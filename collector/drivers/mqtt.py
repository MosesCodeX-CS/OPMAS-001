from __future__ import annotations

from typing import Any

from .base import Driver


class MqttDriver(Driver):
    def __init__(self, broker_url: str, topic: str) -> None:
        self.broker_url = broker_url
        self.topic = topic

    def connect(self) -> bool:
        raise NotImplementedError('MQTT support is not implemented yet')

    def disconnect(self) -> None:
        pass

    def test_connection(self) -> bool:
        raise NotImplementedError('MQTT support is not implemented yet')

    def read_point(self, address: int, register_type: str = 'HOLDING') -> Any:
        raise NotImplementedError('MQTT support is not implemented yet')

    def read_points(self, points: list[tuple[int, str]]) -> list[dict[str, Any]]:
        raise NotImplementedError('MQTT support is not implemented yet')

    def get_capabilities(self) -> dict[str, Any]:
        return {
            'protocol': 'MQTT',
            'supports_holding_registers': False,
            'supports_input_registers': False,
            'supports_coils': False,
            'supports_discrete_inputs': False,
            'supports_writes': False,
            'max_registers_per_request': 0,
            'max_concurrent_requests': 1,
        }
