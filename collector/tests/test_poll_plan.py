import sqlite3
import sys
import types
import unittest
from pathlib import Path

try:
    import pymodbus  # type: ignore
except ImportError:
    pymodbus = types.ModuleType('pymodbus')
    pymodbus.client = types.ModuleType('pymodbus.client')

    class DummyModbusTcpClient:
        def __init__(self, *args, **kwargs):
            pass

        def connect(self):
            return True

        def close(self):
            pass

        def read_holding_registers(self, address, count, device_id=None):
            result = types.SimpleNamespace(isError=lambda: False, registers=[0] * count)
            return result

    pymodbus.client.ModbusTcpClient = DummyModbusTcpClient
    sys.modules['pymodbus'] = pymodbus
    sys.modules['pymodbus.client'] = pymodbus.client

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

import config
import main


def create_test_schema(connection: sqlite3.Connection) -> None:
    cursor = connection.cursor()
    cursor.executescript(
        """
        CREATE TABLE drivers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            protocol TEXT,
            supports_holding_registers INTEGER,
            supports_input_registers INTEGER,
            supports_coils INTEGER,
            supports_discrete_inputs INTEGER,
            supports_writes INTEGER,
            max_registers_per_request INTEGER,
            max_concurrent_requests INTEGER
        );
        CREATE TABLE equipment (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            driver_id INTEGER,
            name TEXT,
            ip_address TEXT,
            port INTEGER,
            unit_id INTEGER,
            poll_interval INTEGER,
            enabled INTEGER,
            last_seen TEXT,
            status TEXT DEFAULT 'UNKNOWN'
        );
        CREATE TABLE poll_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            interval_seconds INTEGER,
            priority TEXT,
            enabled INTEGER
        );
        CREATE TABLE register_definitions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            equipment_id INTEGER,
            poll_profile_id INTEGER,
            address INTEGER,
            register_type TEXT,
            data_type TEXT,
            enabled INTEGER
        );
        CREATE TABLE poll_cycles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            equipment_id INTEGER,
            started_at TEXT,
            finished_at TEXT,
            status TEXT,
            duration INTEGER,
            created_at TEXT,
            updated_at TEXT
        );
        CREATE TABLE telemetry (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            poll_cycle_id INTEGER,
            register_definition_id INTEGER,
            raw_value REAL,
            device_timestamp TEXT,
            collector_timestamp TEXT,
            quality TEXT,
            poll_duration_ms INTEGER,
            created_at TEXT,
            updated_at TEXT
        );
        """
    )
    connection.commit()


class PollPlanTestCase(unittest.TestCase):
    def setUp(self) -> None:
        self.connection = None

    def tearDown(self) -> None:
        if isinstance(self.connection, sqlite3.Connection):
            self.connection.close()

    def test_load_poll_plan_returns_enabled_equipment_and_groups(self):
        self.connection = sqlite3.connect(':memory:')
        connection = self.connection
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 1, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Test Equipment', '127.0.0.1', 5020, 1, 5, 1),
        )
        equipment_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO poll_profiles (interval_seconds, priority, enabled) VALUES (?, ?, ?)',
            (10, 'NORMAL', 1),
        )
        profile_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, poll_profile_id, address, register_type, enabled) VALUES (?, ?, ?, ?, ?)',
            (equipment_id, profile_id, 100, 'HOLDING', 1),
        )
        connection.commit()

        main.DB_DRIVER = 'sqlite'
        main.POLL_INTERVAL = 5

        plan = main.load_poll_plan(connection)

        self.assertEqual(len(plan), 1)
        equipment = plan[0]
        self.assertEqual(equipment['equipment_id'], equipment_id)
        self.assertEqual(equipment['protocol'], 'MODBUS_TCP')
        self.assertEqual(len(equipment['poll_groups']), 1)

        group = equipment['poll_groups'][0]
        self.assertEqual(group['interval_seconds'], 10)
        self.assertEqual(group['definitions'][0]['address'], 100)
        self.assertEqual(group['definitions'][0]['register_type'], 'HOLDING')
        self.assertIsNotNone(group['definitions'][0]['register_definition_id'])

    def test_load_poll_plan_skips_unsupported_register_types(self):
        self.connection = sqlite3.connect(':memory:')
        connection = self.connection
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 0, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Test Equipment', '127.0.0.1', 5020, 1, 5, 1),
        )
        equipment_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO poll_profiles (interval_seconds, priority, enabled) VALUES (?, ?, ?)',
            (10, 'NORMAL', 1),
        )
        profile_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, poll_profile_id, address, register_type, data_type, enabled) VALUES (?, ?, ?, ?, ?, ?)',
            (equipment_id, profile_id, 100, 'COIL', 'BOOLEAN', 1),
        )
        connection.commit()

        main.DB_DRIVER = 'sqlite'
        main.POLL_INTERVAL = 5

        plan = main.load_poll_plan(connection)

        self.assertEqual(plan, [])

    def test_load_poll_plan_groups_by_priority_and_interval(self):
        self.connection = sqlite3.connect(':memory:')
        connection = self.connection
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 1, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Test Equipment', '127.0.0.1', 5020, 1, 5, 1),
        )
        equipment_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO poll_profiles (interval_seconds, priority, enabled) VALUES (?, ?, ?)',
            (20, 'HIGH', 1),
        )
        high_profile_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO poll_profiles (interval_seconds, priority, enabled) VALUES (?, ?, ?)',
            (10, 'NORMAL', 1),
        )
        normal_profile_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO poll_profiles (interval_seconds, priority, enabled) VALUES (?, ?, ?)',
            (5, 'NORMAL', 1),
        )
        fast_normal_profile_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, poll_profile_id, address, register_type, enabled) VALUES (?, ?, ?, ?, ?)',
            (equipment_id, high_profile_id, 100, 'HOLDING', 1),
        )
        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, poll_profile_id, address, register_type, enabled) VALUES (?, ?, ?, ?, ?)',
            (equipment_id, normal_profile_id, 101, 'HOLDING', 1),
        )
        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, poll_profile_id, address, register_type, enabled) VALUES (?, ?, ?, ?, ?)',
            (equipment_id, fast_normal_profile_id, 102, 'HOLDING', 1),
        )
        connection.commit()

        main.DB_DRIVER = 'sqlite'
        main.POLL_INTERVAL = 5

        plan = main.load_poll_plan(connection)

        self.assertEqual(len(plan), 1)
        groups = plan[0]['poll_groups']
        self.assertEqual([group['priority'] for group in groups], ['HIGH', 'NORMAL', 'NORMAL'])
        self.assertEqual([group['interval_seconds'] for group in groups], [20, 5, 10])

    def test_modbus_tcp_driver_falls_back_on_partial_batch_failure(self):
        class FailingClient:
            def __init__(self, *args, **kwargs):
                pass

            def connect(self):
                return True

            def close(self):
                pass

            def read_holding_registers(self, address, count, device_id=None):
                if count == 1 and address == 8:
                    return types.SimpleNamespace(isError=lambda: False, registers=[123])
                if address == 8 and count > 1:
                    return types.SimpleNamespace(isError=lambda: True, registers=[])
                return types.SimpleNamespace(isError=lambda: False, registers=[0] * count)

        import drivers.modbus_tcp as modbus_module
        original_client = modbus_module.ModbusTcpClient
        modbus_module.ModbusTcpClient = lambda *args, **kwargs: FailingClient()
        try:
            from drivers.modbus_tcp import ModbusTcpDriver
            driver = ModbusTcpDriver('127.0.0.1', 5020)
            driver.connect()
            values = driver.read_points([(1, 'HOLDING'), (8, 'HOLDING')])
            self.assertEqual(len(values), 2)
            self.assertEqual(values[0]['address'], 1)
            self.assertEqual(values[0]['value'], 0)
            self.assertEqual(values[1]['address'], 8)
            self.assertEqual(values[1]['value'], 123)
        finally:
            modbus_module.ModbusTcpClient = original_client

    def test_load_poll_plan_uses_equipment_poll_interval_for_unprofiled_definitions(self):
        self.connection = sqlite3.connect(':memory:')
        connection = self.connection
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 1, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Test Equipment', '127.0.0.1', 5020, 1, 7, 1),
        )
        equipment_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO register_definitions (equipment_id, address, register_type, enabled) VALUES (?, ?, ?, ?)',
            (equipment_id, 100, 'HOLDING', 1),
        )
        connection.commit()

        main.DB_DRIVER = 'sqlite'
        main.POLL_INTERVAL = 5

        plan = main.load_poll_plan(connection)

        self.assertEqual(len(plan), 1)
        group = plan[0]['poll_groups'][0]
        self.assertEqual(group['interval_seconds'], 7)
        self.assertIsNone(group['profile_id'])

    def test_poll_register_group_creates_poll_cycle_and_telemetry_rows(self):
        connection = sqlite3.connect(':memory:')
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 1, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Collector Equipment', '127.0.0.1', 5020, 1, 5, 1),
        )
        equipment_id = cursor.lastrowid

        register_ids = []
        for address in (1, 2, 3):
            cursor.execute(
                'INSERT INTO register_definitions (equipment_id, address, register_type, data_type, enabled) VALUES (?, ?, ?, ?, ?)',
                (equipment_id, address, 'HOLDING', 'INTEGER', 1),
            )
            register_ids.append(cursor.lastrowid)
        connection.commit()

        class DummyDriver:
            def __init__(self, *args, **kwargs):
                pass

            def read_points(self, points):
                return [
                    {'address': address, 'register_type': 'HOLDING', 'value': 100 + address}
                    for address, _ in points
                ]

        class DummySession:
            def __init__(self, driver):
                self.driver = driver

            def ensure_connected(self):
                return True

            def disconnect(self):
                pass

        equipment = {
            'equipment_id': equipment_id,
            'poll_interval': 5,
            'protocol': 'MODBUS_TCP',
            'supports_holding_registers': True,
            'supports_input_registers': False,
            'supports_coils': False,
            'supports_discrete_inputs': False,
            'supports_writes': False,
            'max_registers_per_request': 125,
            'max_concurrent_requests': 1,
        }
        group = {
            'profile_id': None,
            'interval_seconds': 5,
            'priority': 'NORMAL',
            'definitions': [
                {'register_definition_id': register_ids[0], 'address': 1, 'register_type': 'HOLDING'},
                {'register_definition_id': register_ids[1], 'address': 2, 'register_type': 'HOLDING'},
                {'register_definition_id': register_ids[2], 'address': 3, 'register_type': 'HOLDING'},
            ],
        }

        main.DB_DRIVER = 'sqlite'
        main.poll_register_group(connection, DummySession(DummyDriver()), equipment, group)

        cursor.execute('SELECT COUNT(*) FROM poll_cycles')
        self.assertEqual(cursor.fetchone()[0], 1)

        cursor.execute('SELECT COUNT(*) FROM telemetry')
        self.assertEqual(cursor.fetchone()[0], 3)

        cursor.execute('SELECT status FROM poll_cycles')
        self.assertEqual(cursor.fetchone()[0], 'COMPLETED')

    def test_run_legacy_cycle_inserts_telemetry_with_static_map(self):
        connection = sqlite3.connect(':memory:')
        create_test_schema(connection)

        cursor = connection.cursor()
        cursor.execute(
            'INSERT INTO drivers (protocol, supports_holding_registers, supports_input_registers, supports_coils, supports_discrete_inputs, supports_writes, max_registers_per_request, max_concurrent_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            ('MODBUS_TCP', 1, 0, 0, 0, 0, 125, 1),
        )
        driver_id = cursor.lastrowid

        cursor.execute(
            'INSERT INTO equipment (driver_id, name, ip_address, port, unit_id, poll_interval, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)',
            (driver_id, 'Legacy Equipment', '127.0.0.1', 5020, 1, 5, 1),
        )
        equipment_id = cursor.lastrowid

        for address in range(1, 9):
            cursor.execute(
                'INSERT INTO register_definitions (equipment_id, address, register_type, data_type, enabled) VALUES (?, ?, ?, ?, ?)',
                (equipment_id, address, 'HOLDING', 'INTEGER', 1),
            )
        connection.commit()

        class DummyDriver:
            def __init__(self, *args, **kwargs):
                pass

            def read_points(self, points):
                return [
                    {'address': address, 'register_type': 'HOLDING', 'value': 42}
                    for address, _ in points
                ]

        class DummySession:
            def __init__(self, driver):
                self.driver = driver

            def ensure_connected(self):
                return True

            def disconnect(self):
                pass

        main.ModbusTcpDriver = DummyDriver
        main.SessionManager = DummySession
        main.DB_DRIVER = 'sqlite'
        main.PLC_HOST = '127.0.0.1'
        main.PLC_PORT = 5020

        result = main.run_legacy_cycle(connection)

        self.assertTrue(result)

        cursor.execute('SELECT COUNT(*) FROM telemetry')
        telemetry_count = cursor.fetchone()[0]
        self.assertEqual(telemetry_count, 8)

        cursor.execute('SELECT status FROM poll_cycles')
        status = cursor.fetchone()[0]
        self.assertEqual(status, 'COMPLETED')
