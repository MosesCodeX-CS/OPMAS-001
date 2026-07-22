from __future__ import annotations

import logging
import sqlite3
import sys
import time
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any

from config import DB_DRIVER, DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PATH, LOG_FILE, PLC_HOST, PLC_PORT, POLL_INTERVAL, REGISTER_MAP
from drivers.device import DeviceDriver
from drivers.modbus_rtu import ModbusRtuDriver
from drivers.modbus_tcp import ModbusTcpDriver
from drivers.opc_ua import OpcUaDriver
from drivers.mqtt import MqttDriver
from drivers.snmp import SnmpDriver
from drivers.session_manager import SessionManager

try:
    import mysql.connector
    from mysql.connector import MySQLConnection
except ImportError:  # pragma: no cover
    mysql = None

# Ensure parent directory of log file exists to prevent startup FileNotFoundError
Path(LOG_FILE).parent.mkdir(parents=True, exist_ok=True)

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE),
        logging.StreamHandler(sys.stdout),
    ],
)
logger = logging.getLogger(__name__)

if DB_DRIVER == 'sqlite':
    sqlite3.register_adapter(datetime, lambda value: value.isoformat())


def connect_db():
    if DB_DRIVER == 'sqlite':
        db_path = Path(DB_PATH)
        if not db_path.exists():
            raise FileNotFoundError(f'Database file not found: {db_path}')

        return sqlite3.connect(str(db_path), detect_types=sqlite3.PARSE_DECLTYPES | sqlite3.PARSE_COLNAMES)

    if DB_DRIVER == 'mysql':
        if mysql is None:
            raise RuntimeError('mysql-connector-python is required for MySQL support.')

        return mysql.connector.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USERNAME,
            password=DB_PASSWORD,
            database=DB_DATABASE,
            autocommit=True,
        )

    raise RuntimeError(f'Unsupported DB_DRIVER: {DB_DRIVER}')


def placeholder() -> str:
    return '?' if DB_DRIVER == 'sqlite' else '%s'


def format_db_datetime(value: datetime) -> str | datetime:
    if DB_DRIVER == 'sqlite' and isinstance(value, datetime):
        return value.isoformat(sep=' ')
    return value


def get_cursor(connection):
    # Use buffered cursors for MySQL to avoid 'Unread result found' errors
    try:
        if DB_DRIVER == 'mysql':
            return connection.cursor(buffered=True)
    except Exception:
        pass
    return connection.cursor()


def read_registers(driver: ModbusTcpDriver) -> dict[str, float | int | None]:
    if not REGISTER_MAP:
        raise RuntimeError('No register mapping configured')

    start = min(REGISTER_MAP.values())
    count = max(REGISTER_MAP.values()) - start + 1

    points = [(start + offset, 'HOLDING') for offset in range(count)]
    values = driver.read_points(points)
    register_values: dict[str, float | int | None] = {name: None for name in REGISTER_MAP}

    for name, address in REGISTER_MAP.items():
        value = next((item['value'] for item in values if item['address'] == address), None)
        register_values[name] = value

    return register_values


def save_sensor_reading(connection, payload: dict[str, float | int | None]) -> int:
    cursor = get_cursor(connection)
    ph = placeholder()
    cursor.execute(
        f'INSERT INTO sensor_readings (pressure, purity, flow_rate, temperature, tank_level, compressor_status, bed_a_status, bed_b_status, created_at, updated_at) VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})',
        (
            payload.get('pressure'),
            payload.get('purity'),
            payload.get('flow_rate'),
            payload.get('temperature'),
            payload.get('tank_level'),
            payload.get('compressor_status'),
            payload.get('bed_a_status'),
            payload.get('bed_b_status'),
            datetime.now(timezone.utc),
            datetime.now(timezone.utc),
        ),
    )
    connection.commit()
    rowid = cursor.lastrowid
    logger.info('Saved sensor reading id=%s %s', rowid, payload)
    return rowid


def create_alarm(connection, alarm_type: str, severity: str, message: str) -> int:
    cursor = get_cursor(connection)
    ph = placeholder()
    cursor.execute(
        f'INSERT INTO alarms (type, severity, message, resolved, created_at, updated_at) VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph})',
        (alarm_type, severity, message, False, datetime.now(timezone.utc), datetime.now(timezone.utc)),
    )
    connection.commit()
    alarm_id = cursor.lastrowid
    logger.warning('Created alarm id=%s type=%s severity=%s message=%s', alarm_id, alarm_type, severity, message)
    return alarm_id


def get_db_thresholds(connection) -> dict[str, float]:
    thresholds = {
        'pressure_low': 4.0,
        'purity_low': 90.0,
        'flow_rate_low': 1.0,
        'temperature_high': 80.0,
        'tank_level_low': 10.0,
    }
    try:
        cursor = get_cursor(connection)
        cursor.execute("SELECT `key`, `value` FROM `system_settings` WHERE `key` IN ('pressure_low', 'purity_low', 'flow_rate_low', 'temperature_high', 'tank_level_low')")
        rows = cursor.fetchall()
        for key, value in rows:
            if value is not None:
                thresholds[key] = float(value)
    except Exception as exc:
        logger.exception("Failed to load thresholds from database, using defaults: %s", exc)
    return thresholds

def get_driver_class(protocol: str):
    drivers = {
        'MODBUS_TCP': ModbusTcpDriver,
        'MODBUS_RTU': ModbusRtuDriver,
        'OPC_UA': OpcUaDriver,
        'MQTT': MqttDriver,
        'SNMP': SnmpDriver,
    }
    try:
        return drivers[protocol]
    except KeyError:
        raise RuntimeError(f'Unsupported protocol: {protocol}')


def driver_supports_register_type(register_type: str, capabilities: dict[str, Any]) -> bool:
    register_type = register_type.upper()
    if register_type == 'HOLDING':
        return bool(capabilities.get('supports_holding_registers'))
    if register_type == 'INPUT':
        return bool(capabilities.get('supports_input_registers'))
    if register_type == 'COIL':
        return bool(capabilities.get('supports_coils'))
    if register_type == 'DISCRETE_INPUT':
        return bool(capabilities.get('supports_discrete_inputs'))
    return False


def fetch_enabled_equipment(connection) -> list[dict[str, Any]]:
    cursor = get_cursor(connection)
    ph = placeholder()
    query = (
        'SELECT equipment.id, equipment.name, equipment.ip_address, equipment.port, equipment.unit_id, '
        'COALESCE(equipment.poll_interval, %s) AS poll_interval, '
        'drivers.protocol, drivers.supports_holding_registers, drivers.supports_input_registers, '
        'drivers.supports_coils, drivers.supports_discrete_inputs, drivers.supports_writes, '
        'drivers.max_registers_per_request, drivers.max_concurrent_requests '
        'FROM equipment '
        'JOIN drivers ON equipment.driver_id = drivers.id '
        'WHERE equipment.enabled = %s '
        'ORDER BY equipment.id'
    )
    if DB_DRIVER == 'sqlite':
        query = query.replace('%s', '?')
    cursor.execute(query, (POLL_INTERVAL, 1))
    rows = cursor.fetchall()
    equipment = []
    for row in rows:
        equipment.append({
            'equipment_id': row[0],
            'name': row[1],
            'ip_address': row[2],
            'port': row[3],
            'unit_id': row[4] or 1,
            'poll_interval': int(row[5]) if row[5] is not None else POLL_INTERVAL,
            'protocol': row[6],
            'supports_holding_registers': bool(row[7]),
            'supports_input_registers': bool(row[8]),
            'supports_coils': bool(row[9]),
            'supports_discrete_inputs': bool(row[10]),
            'supports_writes': bool(row[11]),
            'max_registers_per_request': row[12],
            'max_concurrent_requests': row[13],
        })
    return equipment


def fetch_register_definitions(connection, equipment_id: int) -> list[dict[str, Any]]:
    cursor = get_cursor(connection)
    ph = placeholder()
    query = (
        'SELECT rd.id, rd.address, rd.register_type, rd.data_type, rd.poll_profile_id, '
        'pp.interval_seconds AS profile_interval, pp.priority AS profile_priority '
        'FROM register_definitions rd '
        'LEFT JOIN poll_profiles pp ON rd.poll_profile_id = pp.id '
        'WHERE rd.equipment_id = %s AND rd.enabled = %s '
        'AND (pp.enabled IS NULL OR pp.enabled = %s) '
        'ORDER BY rd.address'
    )
    if DB_DRIVER == 'sqlite':
        query = query.replace('%s', '?')
    cursor.execute(query, (equipment_id, 1, 1))
    rows = cursor.fetchall()
    return [
        {
            'register_definition_id': row[0],
            'address': int(row[1]),
            'register_type': row[2],
            'data_type': row[3],
            'poll_profile_id': row[4],
            'profile_interval': int(row[5]) if row[5] is not None else None,
            'profile_priority': row[6] or 'NORMAL',
        }
        for row in rows
    ]


def priority_rank(priority: str) -> int:
    ranks = {
        'CRITICAL': 0,
        'HIGH': 1,
        'NORMAL': 2,
        'LOW': 3,
    }
    return ranks.get(priority.upper(), 2)


def group_definitions_by_poll_profile(definitions: list[dict[str, Any]], equipment: dict[str, Any]) -> list[dict[str, Any]]:
    grouped: dict[int|None, list[dict[str, Any]]] = {}
    for definition in definitions:
        profile_id = definition['poll_profile_id']
        grouped.setdefault(profile_id, []).append(definition)

    groups = []
    for profile_id, items in grouped.items():
        interval = items[0]['profile_interval'] if items[0]['profile_interval'] is not None else equipment['poll_interval']
        priority = items[0].get('profile_priority', 'NORMAL')
        groups.append({
            'profile_id': profile_id,
            'interval_seconds': int(interval),
            'priority': priority,
            'definitions': items,
            'next_poll_at': datetime.now(timezone.utc),
        })

    groups.sort(key=lambda group: (priority_rank(group['priority']), group['interval_seconds']))
    return groups


def update_equipment_last_seen(connection, equipment_id: int, status: str = 'ONLINE') -> None:
    cursor = get_cursor(connection)
    ph = placeholder()
    query = (
        'UPDATE equipment SET last_seen = %s, status = %s WHERE id = %s'
    )
    if DB_DRIVER == 'sqlite':
        query = query.replace('%s', '?')
    cursor.execute(query, (format_db_datetime(datetime.now(timezone.utc)), status, equipment_id))
    connection.commit()


def poll_register_group(connection, session: SessionManager, equipment: dict[str, Any], group: dict[str, Any]) -> None:
    driver = session.driver
    if not session.ensure_connected():
        logger.error('Unable to connect driver for equipment_id=%s', equipment['equipment_id'])
        return

    definitions = group['definitions']
    points = [(item['address'], item['register_type']) for item in definitions]
    poll_cycle_id = create_poll_cycle(connection, equipment['equipment_id'])
    cycle_start = datetime.now(timezone.utc)
    status = 'COMPLETED'
    try:
        values = driver.read_points(points)
        for definition in definitions:
            raw_value = next(
                (item['value'] for item in values if item['address'] == definition['address'] and item['register_type'] == definition['register_type']),
                None,
            )
            insert_telemetry_row(
                connection,
                poll_cycle_id,
                definition['register_definition_id'],
                raw_value,
                device_ts=None,
                quality='GOOD',
                poll_duration_ms=None,
            )
        update_equipment_last_seen(connection, equipment['equipment_id'], status='ONLINE')
    except Exception as exc:
        logger.exception('Poll failed for equipment_id=%s profile_id=%s', equipment['equipment_id'], group['profile_id'])
        status = 'FAILED'
    finally:
        duration_ms = int((datetime.now(timezone.utc) - cycle_start).total_seconds() * 1000)
        finish_poll_cycle(connection, poll_cycle_id, duration_ms, status)


def load_poll_plan(connection) -> list[dict[str, Any]]:
    equipment_list = fetch_enabled_equipment(connection)
    plan: list[dict[str, Any]] = []
    for equipment in equipment_list:
        definitions = fetch_register_definitions(connection, equipment['equipment_id'])
        supported_definitions = []
        for definition in definitions:
            if not driver_supports_register_type(definition['register_type'], equipment):
                logger.warning(
                    'Skipping unsupported register type %s for equipment_id=%s',
                    definition['register_type'],
                    equipment['equipment_id'],
                )
                continue
            supported_definitions.append(definition)

        if not supported_definitions:
            logger.warning('No supported register definitions found for equipment_id=%s', equipment['equipment_id'])
            continue

        equipment['poll_groups'] = group_definitions_by_poll_profile(supported_definitions, equipment)
        plan.append(equipment)
    return plan


def find_equipment_id(connection, ip: str, port: int) -> int | None:
    cursor = get_cursor(connection)
    try:
        cursor.execute('SELECT id FROM equipment WHERE ip_address = %s AND port = %s' if DB_DRIVER != 'sqlite' else 'SELECT id FROM equipment WHERE ip_address = ? AND port = ?', (ip, port))
        row = cursor.fetchone()
        return row[0] if row else None
    except Exception:
        return None


def create_poll_cycle(connection, equipment_id: int) -> int:
    cursor = get_cursor(connection)
    ph = placeholder()
    cursor.execute(
        f'INSERT INTO poll_cycles (equipment_id, started_at, status, created_at, updated_at) VALUES ({ph}, {ph}, {ph}, {ph}, {ph})',
        (
            equipment_id,
            format_db_datetime(datetime.now(timezone.utc)),
            'RUNNING',
            format_db_datetime(datetime.now(timezone.utc)),
            format_db_datetime(datetime.now(timezone.utc)),
        ),
    )
    connection.commit()
    return cursor.lastrowid


def finish_poll_cycle(connection, poll_cycle_id: int, duration_ms: int, status: str = 'COMPLETED') -> None:
    cursor = get_cursor(connection)
    ph = placeholder()
    cursor.execute(
        f'UPDATE poll_cycles SET finished_at = {ph}, duration = {ph}, status = {ph}, updated_at = {ph} WHERE id = {ph}',
        (
            format_db_datetime(datetime.now(timezone.utc)),
            duration_ms,
            status,
            format_db_datetime(datetime.now(timezone.utc)),
            poll_cycle_id,
        ),
    )
    connection.commit()


def get_or_create_register_definition(connection, equipment_id: int, address: int, register_type: str = 'HOLDING') -> int:
    cursor = get_cursor(connection)
    ph = placeholder()
    # Try to find existing
    cursor.execute(
        ('SELECT id FROM register_definitions WHERE equipment_id = %s AND address = %s AND register_type = %s' if DB_DRIVER != 'sqlite' else 'SELECT id FROM register_definitions WHERE equipment_id = ? AND address = ? AND register_type = ?'),
        (equipment_id, address, register_type),
    )
    row = cursor.fetchone()
    if row:
        return row[0]

    # Create a placeholder register definition
    cursor.execute(
        f'INSERT INTO register_definitions (equipment_id, address, register_type, enabled, graph_enabled, created_at, updated_at) VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})',
        (equipment_id, address, register_type, False, False, datetime.now(timezone.utc), datetime.now(timezone.utc)),
    )
    register_id = cursor.lastrowid

    # Create an initial version
    # 'offset' is a reserved word in some SQL dialects; quote identifiers when using MySQL
    cols = 'register_definition_id, name, scale, offset, unit, decimals, effective_from, created_at, updated_at'
    if DB_DRIVER != 'sqlite':
        cols = '`register_definition_id`, `name`, `scale`, `offset`, `unit`, `decimals`, `effective_from`, `created_at`, `updated_at`'
    cursor.execute(
        f'INSERT INTO register_definition_versions ({cols}) VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})',
        (
            register_id,
            f'Unknown Register {address}',
            1,
            0,
            None,
            2,
            format_db_datetime(datetime.now(timezone.utc)),
            format_db_datetime(datetime.now(timezone.utc)),
            format_db_datetime(datetime.now(timezone.utc)),
        ),
    )
    connection.commit()
    return register_id


def insert_telemetry_row(connection, poll_cycle_id: int, register_definition_id: int, raw_value, device_ts=None, quality='GOOD', poll_duration_ms=None) -> int:
    cursor = get_cursor(connection)
    ph = placeholder()
    cursor.execute(
        f'INSERT INTO telemetry (poll_cycle_id, register_definition_id, raw_value, device_timestamp, collector_timestamp, quality, poll_duration_ms, created_at, updated_at) VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})',
        (
            poll_cycle_id,
            register_definition_id,
            raw_value,
            device_ts,
            format_db_datetime(datetime.now(timezone.utc)),
            quality,
            poll_duration_ms,
            format_db_datetime(datetime.now(timezone.utc)),
            format_db_datetime(datetime.now(timezone.utc)),
        ),
    )
    connection.commit()
    return cursor.lastrowid


def main() -> int:
    db_description = f'{DB_DRIVER}'
    if DB_DRIVER == 'mysql':
        db_description = f'mysql://{DB_USERNAME}@{DB_HOST}:{DB_PORT}/{DB_DATABASE}'
    logger.info('Collector starting. DB=%s', db_description)

    schedule: dict[int, dict[str, datetime]] = {}

    try:
        while True:
            connection = connect_db()
            try:
                plan = load_poll_plan(connection)
                if not plan:
                    logger.warning('No enabled equipment or register definitions found. Falling back to static register mapping.')
                    if not run_legacy_cycle(connection):
                        time.sleep(POLL_INTERVAL)
                        continue
                else:
                    now = datetime.now(timezone.utc)
                    for equipment in plan:
                        equipment_id = equipment['equipment_id']
                        equipment_schedule = schedule.setdefault(equipment_id, {})
                        comm_driver = get_driver_class(equipment['protocol'])(host=equipment['ip_address'], port=equipment['port'], unit_id=equipment['unit_id'])
                        driver = DeviceDriver(comm_driver)
                        session = SessionManager(driver)
                        try:
                            for group in equipment['poll_groups']:
                                group_key = f"{equipment_id}:{group['profile_id'] or 'default'}"
                                next_poll_at = equipment_schedule.get(group_key, now)
                                if next_poll_at > now:
                                    continue
                                poll_register_group(connection, session, equipment, group)
                                equipment_schedule[group_key] = now + timedelta(seconds=group['interval_seconds'])
                        finally:
                            session.disconnect()
            except Exception as exc:
                logger.exception('Collector loop failed: %s', exc)
            finally:
                try:
                    connection.close()
                except Exception:
                    pass
            time.sleep(1)

    finally:
        logger.info('Collector stopped.')
    return 0


def run_legacy_cycle(connection) -> bool:
    driver = ModbusTcpDriver(host=PLC_HOST, port=PLC_PORT)
    session = SessionManager(driver)
    if not session.ensure_connected():
        logger.error('Could not connect to PLC at %s:%s', PLC_HOST, PLC_PORT)
        return False

    try:
        equipment_id = find_equipment_id(connection, PLC_HOST, PLC_PORT)
        if equipment_id is None:
            logger.error('No equipment record found for %s:%s — skipping legacy poll', PLC_HOST, PLC_PORT)
            return False

        poll_cycle_id = create_poll_cycle(connection, equipment_id)
        cycle_start = datetime.now(timezone.utc)

        reading = read_registers(driver)

        for name, address in REGISTER_MAP.items():
            raw = reading.get(name)
            reg_id = get_or_create_register_definition(connection, equipment_id, address, 'HOLDING')
            insert_telemetry_row(connection, poll_cycle_id, reg_id, raw, device_ts=None, quality='GOOD', poll_duration_ms=None)

        cycle_end = datetime.now(timezone.utc)
        duration_ms = int((cycle_end - cycle_start).total_seconds() * 1000)
        finish_poll_cycle(connection, poll_cycle_id, duration_ms)
        logger.info(
            'Legacy poll cycle %s completed in %sms (%s registers, equipment_id=%s)',
            poll_cycle_id,
            duration_ms,
            len(REGISTER_MAP),
            equipment_id,
        )
        return True
    except Exception as exc:
        logger.exception('Legacy poll cycle failed: %s', exc)
        return False
    finally:
        session.disconnect()


if __name__ == '__main__':
    sys.exit(main())
