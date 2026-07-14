from __future__ import annotations

import logging
import sqlite3
import sys
import time
from datetime import datetime
from pathlib import Path

from pymodbus.client import ModbusTcpClient

from config import ALARM_THRESHOLDS, DB_DRIVER, DB_PATH, LOG_FILE, PLC_HOST, PLC_PORT, POLL_INTERVAL, REGISTER_MAP, SCALE

logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
)
logger = logging.getLogger(__name__)


def connect_db() -> sqlite3.Connection:
    if DB_DRIVER != 'sqlite':
        raise RuntimeError('Only sqlite is supported in this collector implementation.')

    db_path = Path(DB_PATH)
    if not db_path.exists():
        raise FileNotFoundError(f'Database file not found: {db_path}')

    return sqlite3.connect(str(db_path), detect_types=sqlite3.PARSE_DECLTYPES | sqlite3.PARSE_COLNAMES)


def read_registers(client: ModbusTcpClient) -> dict[str, float | int | None]:
    start = min(REGISTER_MAP.values())
    count = max(REGISTER_MAP.values()) - start + 1

    result = client.read_holding_registers(address=start, count=count, unit=1)
    if result.isError() or result.registers is None:
        raise RuntimeError('Modbus read error: %s' % result)

    registers = {name: result.registers[idx - start] for name, idx in REGISTER_MAP.items()}
    return {
        'pressure': registers['pressure'] * SCALE['pressure'] if registers['pressure'] is not None else None,
        'purity': registers['purity'] * SCALE['purity'] if registers['purity'] is not None else None,
        'flow_rate': registers['flow_rate'] * SCALE['flow_rate'] if registers['flow_rate'] is not None else None,
        'temperature': registers['temperature'] * SCALE['temperature'] if registers['temperature'] is not None else None,
        'tank_level': registers['tank_level'] * SCALE['tank_level'] if registers['tank_level'] is not None else None,
        'compressor_status': registers['compressor_status'],
        'bed_a_status': registers['bed_a_status'],
        'bed_b_status': registers['bed_b_status'],
    }


def save_sensor_reading(connection: sqlite3.Connection, payload: dict[str, float | int | None]) -> int:
    cursor = connection.cursor()
    cursor.execute(
        'INSERT INTO sensor_readings (pressure, purity, flow_rate, temperature, tank_level, compressor_status, bed_a_status, bed_b_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        (
            payload['pressure'],
            payload['purity'],
            payload['flow_rate'],
            payload['temperature'],
            payload['tank_level'],
            payload['compressor_status'],
            payload['bed_a_status'],
            payload['bed_b_status'],
            datetime.utcnow(),
            datetime.utcnow(),
        ),
    )
    connection.commit()
    rowid = cursor.lastrowid
    logger.info('Saved sensor reading id=%s %s', rowid, payload)
    return rowid


def create_alarm(connection: sqlite3.Connection, alarm_type: str, severity: str, message: str) -> int:
    cursor = connection.cursor()
    cursor.execute(
        'INSERT INTO alarms (type, severity, message, resolved, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
        (alarm_type, severity, message, False, datetime.utcnow(), datetime.utcnow()),
    )
    connection.commit()
    alarm_id = cursor.lastrowid
    logger.warning('Created alarm id=%s type=%s severity=%s message=%s', alarm_id, alarm_type, severity, message)
    return alarm_id


def check_alarms(connection: sqlite3.Connection, reading: dict[str, float | int | None]) -> None:
    if reading['pressure'] is not None and reading['pressure'] < ALARM_THRESHOLDS['pressure_low']:
        create_alarm(connection, 'Pressure', 'CRITICAL', f'Pressure below threshold: {reading["pressure"]} bar')

    if reading['purity'] is not None and reading['purity'] < ALARM_THRESHOLDS['purity_low']:
        create_alarm(connection, 'Purity', 'CRITICAL', f'Oxygen purity below threshold: {reading["purity"]}%')

    if reading['temperature'] is not None and reading['temperature'] > ALARM_THRESHOLDS['temperature_high']:
        create_alarm(connection, 'Temperature', 'WARNING', f'Temperature above limit: {reading["temperature"]} °C')

    if reading['flow_rate'] is not None and reading['flow_rate'] < ALARM_THRESHOLDS['flow_rate_low']:
        create_alarm(connection, 'Flow Rate', 'WARNING', f'Flow rate below threshold: {reading["flow_rate"]}')


def main() -> int:
    logger.info('Collector starting. PLC=%s:%s DB=%s', PLC_HOST, PLC_PORT, DB_PATH)
    client = ModbusTcpClient(host=PLC_HOST, port=PLC_PORT)

    try:
        if not client.connect():
            logger.error('Could not connect to PLC at %s:%s', PLC_HOST, PLC_PORT)
            return 1

        connection = connect_db()

        while True:
            try:
                reading = read_registers(client)
                save_sensor_reading(connection, reading)
                check_alarms(connection, reading)
            except Exception as exc:
                logger.exception('Poll cycle failed: %s', exc)
            time.sleep(POLL_INTERVAL)

    finally:
        client.close()
        logger.info('Collector stopped.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
