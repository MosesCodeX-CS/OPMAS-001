from __future__ import annotations

from typing import Any

from .base import Driver


class ModbusRtuDriver(Driver):
    def __init__(self, serial_port: str, baudrate: int = 19200, unit_id: int = 1) -> None:
        self.serial_port = serial_port
        self.baudrate = baudrate
        self.unit_id = unit_id

    def connect(self) -> bool:
        raise NotImplementedError('Modbus RTU support is not implemented yet')

    def disconnect(self) -> None:
        pass

    def test_connection(self) -> bool:
        raise NotImplementedError('Modbus RTU support is not implemented yet')

    def read_point(self, address: int, register_type: str = 'HOLDING') -> Any:
        raise NotImplementedError('Modbus RTU support is not implemented yet')

    def read_points(self, points: list[tuple[int, str]]) -> list[dict[str, Any]]:
        raise NotImplementedError('Modbus RTU support is not implemented yet')

    def get_capabilities(self) -> dict[str, Any]:
        return {
            'protocol': 'MODBUS_RTU',
            'supports_holding_registers': True,
            'supports_input_registers': True,
            'supports_coils': False,
            'supports_discrete_inputs': False,
            'supports_writes': False,
            'max_registers_per_request': 125,
            'max_concurrent_requests': 1,
        }
