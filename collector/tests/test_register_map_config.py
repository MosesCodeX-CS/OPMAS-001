import importlib
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

import config


def test_register_map_file_overrides_defaults(tmp_path, monkeypatch):
    mapping_file = tmp_path / 'register_map.json'
    mapping_file.write_text(json.dumps({
        'pressure': {'address': 100, 'scale': 0.25},
        'purity': {'address': 101, 'scale': 0.5},
    }))

    monkeypatch.setenv('REGISTER_MAP_FILE', str(mapping_file))
    monkeypatch.delenv('REGISTER_PRESSURE', raising=False)
    monkeypatch.delenv('REGISTER_PURITY', raising=False)

    module = importlib.reload(config)

    assert module.REGISTER_MAP['pressure'] == 100
    assert module.REGISTER_MAP['purity'] == 101
    assert module.SCALE['pressure'] == 0.25
    assert module.SCALE['purity'] == 0.5
