import importlib.util
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def load_module(name, relative_path):
    spec = importlib.util.spec_from_file_location(name, ROOT / relative_path)
    module = importlib.util.module_from_spec(spec)
    assert spec.loader is not None
    spec.loader.exec_module(module)
    return module


def test_modbus_tcp_driver_exposes_required_interface():
    driver_module = load_module('collector_drivers', 'drivers/modbus_tcp.py')
    driver = driver_module.ModbusTcpDriver(host='127.0.0.1', port=502)

    assert hasattr(driver, 'connect')
    assert hasattr(driver, 'disconnect')
    assert hasattr(driver, 'test_connection')
    assert hasattr(driver, 'read_point')
    assert hasattr(driver, 'read_points')
    assert hasattr(driver, 'get_capabilities')


def test_device_driver_wrapper_proxies_comm_driver_methods():
    driver_module = load_module('collector_drivers', 'drivers/device.py')

    class DummyCommDriver:
        def __init__(self):
            self.connected = False

        def connect(self):
            self.connected = True
            return True

        def disconnect(self):
            self.connected = False

        def test_connection(self):
            return self.connected

        def read_point(self, address, register_type='HOLDING'):
            return address

        def read_points(self, points):
            return [{'address': addr, 'register_type': reg_type, 'value': addr} for addr, reg_type in points]

        def get_capabilities(self):
            return {'supports_holding_registers': True}

    comm_driver = DummyCommDriver()
    wrapper = driver_module.DeviceDriver(comm_driver)

    assert wrapper.connect() is True
    assert wrapper.test_connection() is True
    assert wrapper.read_point(1, 'HOLDING') == 1
    assert wrapper.read_points([(1, 'HOLDING')])[0]['value'] == 1
    assert wrapper.get_capabilities()['supports_holding_registers'] is True
    wrapper.disconnect()
    assert comm_driver.connected is False


def test_main_get_driver_class_resolves_supported_protocols():
    main_module = load_module('collector_main', 'main.py')
    supported_protocols = ['MODBUS_TCP', 'MODBUS_RTU', 'OPC_UA', 'MQTT', 'SNMP']
    for protocol in supported_protocols:
        cls = main_module.get_driver_class(protocol)
        assert cls.__name__.endswith('Driver')
