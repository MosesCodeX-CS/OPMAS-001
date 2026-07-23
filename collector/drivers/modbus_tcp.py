from __future__ import annotations

from typing import Any

try:
    from pymodbus.client import ModbusTcpClient
except ImportError:  # pragma: no cover
    ModbusTcpClient = None

from .base import Driver


class ModbusTcpDriver(Driver):
    def __init__(self, host: str, port: int = 502, unit_id: int = 1) -> None:
        self.host = host
        self.port = port
        self.unit_id = unit_id
        self.client: ModbusTcpClient | None = None

    def connect(self) -> bool:
        if ModbusTcpClient is None:
            raise RuntimeError('pymodbus is required for ModbusTcpDriver')
        self.client = ModbusTcpClient(host=self.host, port=self.port)
        return bool(self.client.connect())

    def disconnect(self) -> None:
        if self.client is not None:
            self.client.close()
            self.client = None

    def test_connection(self) -> bool:
        if self.client is None:
            self.connect()
        if self.client is None:
            return False
        return bool(self.client.connect())

    def read_point(self, address: int, register_type: str = "HOLDING") -> Any:
        if self.client is None:
            self.connect()
        if self.client is None:
            raise RuntimeError("Client is not connected")

        if register_type == "HOLDING":
            result = self.client.read_holding_registers(address=address - 1, count=1, device_id=self.unit_id)
        elif register_type == "INPUT":
            result = self.client.read_input_registers(address=address - 1, count=1, device_id=self.unit_id)
        else:
            raise ValueError(f"Unsupported register type: {register_type}")

        if result.isError() or result.registers is None:
            raise RuntimeError(f"Failed to read {register_type.lower()} register {address}")
        return result.registers[0]

    def read_points(self, points: list[tuple[int, str]]) -> list[dict[str, Any]]:
        if self.client is None:
            self.connect()
        if self.client is None:
            raise RuntimeError("Client is not connected")

        grouped: dict[str, list[int]] = {}
        for address, register_type in points:
            if register_type not in ("HOLDING", "INPUT"):
                raise ValueError(f"Unsupported register type: {register_type}")
            grouped.setdefault(register_type, []).append(address)

        max_registers = self.get_capabilities().get('max_registers_per_request', 125)
        results: list[dict[str, Any]] = []
        for register_type, addresses in grouped.items():
            if not addresses:
                continue
            sorted_addresses = sorted(set(addresses))

            ranges: list[tuple[int, int]] = []
            range_start = sorted_addresses[0]
            range_end = sorted_addresses[0]
            for address in sorted_addresses[1:]:
                if address == range_end + 1:
                    range_end = address
                else:
                    ranges.append((range_start, range_end))
                    range_start = address
                    range_end = address
            ranges.append((range_start, range_end))

            for range_start, range_end in ranges:
                chunk_start = range_start
                while chunk_start <= range_end:
                    chunk_end = min(range_end, chunk_start + max_registers - 1)
                    count = chunk_end - chunk_start + 1
                    if register_type == "HOLDING":
                        result = self.client.read_holding_registers(address=chunk_start - 1, count=count, device_id=self.unit_id)
                    else:
                        result = self.client.read_input_registers(address=chunk_start - 1, count=count, device_id=self.unit_id)

                    if result.isError() or result.registers is None:
                        for address in range(chunk_start, chunk_end + 1):
                            try:
                                if register_type == "HOLDING":
                                    single_result = self.client.read_holding_registers(address=address - 1, count=1, device_id=self.unit_id)
                                else:
                                    single_result = self.client.read_input_registers(address=address - 1, count=1, device_id=self.unit_id)
                            except Exception:
                                results.append({
                                    "address": address,
                                    "register_type": register_type,
                                    "value": None,
                                })
                                continue
                            if single_result.isError() or single_result.registers is None:
                                results.append({
                                    "address": address,
                                    "register_type": register_type,
                                    "value": None,
                                })
                            else:
                                results.append({
                                    "address": address,
                                    "register_type": register_type,
                                    "value": single_result.registers[0] if single_result.registers else None,
                                })
                        chunk_start = chunk_end + 1
                        continue

                    for address in range(chunk_start, chunk_end + 1):
                        offset = address - chunk_start
                        value = result.registers[offset] if offset < len(result.registers) else None
                        results.append({
                            "address": address,
                            "register_type": register_type,
                            "value": value,
                        })
                    chunk_start = chunk_end + 1

        results.sort(key=lambda item: item["address"])
        return results

    def get_capabilities(self) -> dict[str, Any]:
        return {
            "protocol": "MODBUS_TCP",
            "supports_holding_registers": True,
            "supports_input_registers": True,
            "supports_coils": False,
            "supports_discrete_inputs": False,
            "supports_writes": False,
            "max_registers_per_request": 125,
            "max_concurrent_requests": 1,
        }
