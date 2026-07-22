from __future__ import annotations

from typing import Any

from .base import Driver


class DeviceDriver(Driver):
    def __init__(self, comm_driver: Driver) -> None:
        self.comm_driver = comm_driver

    def connect(self) -> bool:
        return self.comm_driver.connect()

    def disconnect(self) -> None:
        self.comm_driver.disconnect()

    def test_connection(self) -> bool:
        return self.comm_driver.test_connection()

    def read_point(self, address: int, register_type: str = 'HOLDING') -> Any:
        return self.comm_driver.read_point(address, register_type)

    def read_points(self, points: list[tuple[int, str]]) -> list[dict[str, Any]]:
        return self.comm_driver.read_points(points)

    def get_capabilities(self) -> dict[str, Any]:
        return self.comm_driver.get_capabilities()
