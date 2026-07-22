from __future__ import annotations

import json
import os
from pathlib import Path
from dotenv import load_dotenv

HERE = Path(__file__).resolve().parent
load_dotenv(HERE / '.env')


def env(name: str, default: str | None = None) -> str | None:
    value = os.getenv(name)
    return value if value is not None else default


PLC_HOST = env('PLC_HOST', '127.0.0.1')
PLC_PORT = int(env('PLC_PORT', '502'))
POLL_INTERVAL = int(env('POLL_INTERVAL', '5'))

DB_DRIVER = env('DB_DRIVER', 'mysql')
DB_HOST = env('DB_HOST', '127.0.0.1')
DB_PORT = int(env('DB_PORT', '3306'))
DB_USERNAME = env('DB_USERNAME', 'root')
DB_PASSWORD = env('DB_PASSWORD', '')
DB_DATABASE = env('DB_DATABASE', 'opmas')
DB_PATH = env('DB_PATH', str(HERE.parent / 'opmas-app' / 'database' / 'database.sqlite'))

def load_register_map() -> tuple[dict[str, int], dict[str, float]]:
    mapping_file = env('REGISTER_MAP_FILE')
    if mapping_file:
        path = Path(mapping_file).expanduser()
        if path.exists():
            with path.open('r', encoding='utf-8') as handle:
                payload = json.load(handle)
            register_map = {}
            scale_map = {}
            for key, value in payload.items():
                if isinstance(value, dict):
                    register_map[key] = int(value.get('address', 0))
                    scale_map[key] = float(value.get('scale', 1.0))
                else:
                    register_map[key] = int(value)
            return register_map, scale_map

    register_map = {
        'pressure': int(env('REGISTER_PRESSURE', '1')),
        'purity': int(env('REGISTER_PURITY', '2')),
        'flow_rate': int(env('REGISTER_FLOW_RATE', '3')),
        'temperature': int(env('REGISTER_TEMPERATURE', '4')),
        'tank_level': int(env('REGISTER_TANK_LEVEL', '5')),
        'compressor_status': int(env('REGISTER_COMPRESSOR_STATUS', '6')),
        'bed_a_status': int(env('REGISTER_BED_A_STATUS', '7')),
        'bed_b_status': int(env('REGISTER_BED_B_STATUS', '8')),
    }

    scale_map = {
        'pressure': float(env('PRESSURE_SCALE', '0.1')),
        'purity': float(env('PURITY_SCALE', '0.1')),
        'flow_rate': float(env('FLOW_RATE_SCALE', '1.0')),
        'temperature': float(env('TEMPERATURE_SCALE', '1.0')),
        'tank_level': float(env('TANK_LEVEL_SCALE', '1.0')),
    }

    return register_map, scale_map


REGISTER_MAP, SCALE = load_register_map()

ALARM_THRESHOLDS = {
    'pressure_low': float(env('ALARM_PRESSURE_LOW', '4.0')),
    'purity_low': float(env('ALARM_PURITY_LOW', '90.0')),
    'flow_rate_low': float(env('ALARM_FLOW_RATE_LOW', '1.0')),
    'temperature_high': float(env('ALARM_TEMPERATURE_HIGH', '80.0')),
}

DEFAULT_ALARM_SEVERITY = env('DEFAULT_ALARM_SEVERITY', 'WARNING')
LOG_FILE = env('LOG_FILE', str(HERE / 'collector.log'))
