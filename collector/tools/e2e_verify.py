from __future__ import annotations

import subprocess
import sys
from datetime import datetime
import os

# Ensure collector package root is on sys.path so local modules can be imported
ROOT = os.path.dirname(os.path.dirname(__file__))
sys.path.insert(0, ROOT)

from config import PLC_HOST, PLC_PORT, REGISTER_MAP, DB_DRIVER
from drivers.modbus_tcp import ModbusTcpDriver
from main import connect_db, find_equipment_id, create_poll_cycle, get_or_create_register_definition, insert_telemetry_row, finish_poll_cycle, read_registers


def run_once():
    conn = connect_db()
    driver = ModbusTcpDriver(host=PLC_HOST, port=PLC_PORT)
    try:
        if not driver.connect():
            print('driver connect failed')
            return 2
        eq = find_equipment_id(conn, PLC_HOST, PLC_PORT)
        if not eq:
            # Create a minimal equipment row for local testing
            cur = conn.cursor()
            ph = '?' if DB_DRIVER == 'sqlite' else '%s'
            if ph == '?':
                cur.execute('INSERT INTO equipment (code, name, ip_address, port, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)', ('PLC_AUTO', 'Auto PLC', PLC_HOST, PLC_PORT, datetime.utcnow(), datetime.utcnow()))
            else:
                cur.execute('INSERT INTO equipment (code, name, ip_address, port, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s)', ('PLC_AUTO', 'Auto PLC', PLC_HOST, PLC_PORT, datetime.utcnow(), datetime.utcnow()))
            conn.commit()
            eq = find_equipment_id(conn, PLC_HOST, PLC_PORT)
            print('Created equipment id', eq)
        poll_id = create_poll_cycle(conn, eq)
        start = datetime.utcnow()
        values = read_registers(driver)
        for name, addr in REGISTER_MAP.items():
            raw = values.get(name)
            reg_id = get_or_create_register_definition(conn, eq, addr)
            tid = insert_telemetry_row(conn, poll_id, reg_id, raw)
            print('inserted telemetry', tid, 'for', name, 'raw=', raw)
        duration_ms = int((datetime.utcnow() - start).total_seconds() * 1000)
        finish_poll_cycle(conn, poll_id, duration_ms)
        # Call Laravel script to interpret latest telemetry
        php = subprocess.run(['php', 'scripts/interpret_latest.php'], cwd='../opmas-app', capture_output=True, text=True)
        print('=== PHP interpreter output ===')
        print(php.stdout)
        if php.returncode != 0:
            print('Interpreter script failed:', php.stderr, file=sys.stderr)
            return 4
        return 0
    finally:
        try:
            driver.disconnect()
        except Exception:
            pass
        try:
            conn.close()
        except Exception:
            pass


if __name__ == '__main__':
    sys.exit(run_once())
