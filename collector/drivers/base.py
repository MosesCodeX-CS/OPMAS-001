from __future__ import annotations

from abc import ABC, abstractmethod
from typing import Any


class Driver(ABC):
    @abstractmethod
    def connect(self) -> bool:
        raise NotImplementedError

    @abstractmethod
    def disconnect(self) -> None:
        raise NotImplementedError

    @abstractmethod
    def test_connection(self) -> bool:
        raise NotImplementedError

    @abstractmethod
    def read_point(self, address: int, register_type: str = "HOLDING") -> Any:
        raise NotImplementedError

    @abstractmethod
    def read_points(self, points: list[tuple[int, str]]) -> list[dict[str, Any]]:
        raise NotImplementedError

    @abstractmethod
    def get_capabilities(self) -> dict[str, Any]:
        raise NotImplementedError
