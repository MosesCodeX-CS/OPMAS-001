from __future__ import annotations

from .base import Driver


class SessionManager:
    def __init__(self, driver: Driver) -> None:
        self.driver = driver
        self.connected = False

    def ensure_connected(self) -> bool:
        if not self.connected:
            self.connected = self.driver.connect()
        return self.connected

    def disconnect(self) -> None:
        self.driver.disconnect()
        self.connected = False
