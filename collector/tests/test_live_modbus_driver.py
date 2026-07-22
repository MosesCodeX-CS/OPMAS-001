import os
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

try:
    import pymodbus  # type: ignore
except ImportError:
    pymodbus = None

from drivers.modbus_tcp import ModbusTcpDriver


def test_live_simulator_read_point():
    if pymodbus is None:
        raise unittest.SkipTest('pymodbus is not installed')

    host = os.getenv('PLC_HOST', '127.0.0.1')
    port = int(os.getenv('PLC_PORT', '5020'))

    driver = ModbusTcpDriver(host=host, port=port)
    try:
        assert driver.connect() is True
        value = driver.read_point(0)
        assert isinstance(value, int)
        assert value >= 0
    finally:
        driver.disconnect()
