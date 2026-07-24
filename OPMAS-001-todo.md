# OPMAS-001 Implementation To-Do List (v6 — Final Architecture)

**Guiding principle:** Collect first. Interpret later. Improve continuously.
The collector never knows what a value *means* — Laravel decides that using `register_definitions`.

---

## Module 1 — Core Infrastructure

### ~~1.1 Database~~ [x] completed
- [x] ~~`hospitals` / `sites` tables (design for multi-hospital from the start, even with only Kijabe today)~~
- [x] ~~Create `drivers` table: `id`, `name` (e.g. "Schneider M221 Driver"), `protocol`, `supports_holding_registers`, `supports_input_registers`, `supports_coils`, `supports_discrete_inputs`, `supports_writes`, `max_registers_per_request`, `max_concurrent_requests` — capabilities live here, not on equipment, since every device using the same driver shares them~~
- [x] ~~Update `equipment` table: `id`, `site_id`, `driver_id` (FK, replaces separate capability fields), `name`, `manufacturer`, `model`, `device_type`, `location` (e.g. "Plant Room", "ICU", "Building A"), `ip_address`, `port`, `unit_id`, `poll_interval`, `enabled`, `last_seen`, `status`~~
- [x] ~~Create `register_groups`: `id`, `equipment_id`, `name` (e.g. "Pressure", "Flow", "Temperature")~~
- [x] ~~Create `poll_profiles`: `id`, `name`, `interval_seconds`, `priority` (Critical / Normal / Low), `enabled` — allows disabling a whole profile (e.g. "Fast") without deleting it~~
- [x] ~~Create `register_definitions`: `id`, `equipment_id`, `register_group_id`, `address`, `register_type` (enum: `HOLDING`, `INPUT`, `COIL`, `DISCRETE`), `data_type`, `poll_profile_id`, `display_order`, `enabled`, `graph_enabled` — the current, stable identity of a register *(note: may eventually evolve into a protocol-agnostic `telemetry_points` concept if OPC UA/MQTT support is added — not renaming now, just leaving the note)*~~
- [x] ~~Create `register_definition_versions`: `id`, `register_definition_id` (FK), `name`, `description`, `scale`, `offset`, `unit`, `decimals`, `effective_from` — a register keeps ONE `register_definitions` row for life; only naming/scale/unit changes get a new version row, avoiding duplicate register records for a five-year-old register that just had its scale corrected~~
- [x] ~~Create `alarm_rules` table (separate entity, not fields on `register_definitions`, since one register can have multiple rules): `id`, `register_definition_id`, `condition`, `threshold(s)`, `severity`, `enabled`~~
- [x] ~~Create `poll_cycles`: `id`, `equipment_id`, `started_at`, `finished_at`, `status`, `duration` — one row per polling pass over a device~~
- [x] ~~Create `telemetry`: `id`, `poll_cycle_id` (FK), `register_definition_id` (FK), `raw_value`, `device_timestamp` (nullable), `collector_timestamp`, `quality` (enum, see below), `poll_duration_ms`~~
- [x] ~~Define `quality` as a proper enum/lookup table, not free text: `GOOD`, `BAD`, `TIMEOUT`, `ILLEGAL_ADDRESS`, `DEVICE_OFFLINE`, `COMMUNICATION_ERROR` — prevents typo variants like `TimeOut` vs `timeout` vs `TIMEOUT`~~
- [x] ~~Define `equipment.status` as a proper enum, not free text: `ONLINE`, `OFFLINE`, `CONNECTING`, `ERROR`, `DISABLED`, `UNKNOWN`~~
- [x] ~~Update `alarms` table (active/historical alarm instances) to reference `alarm_rules`~~
- [x] ~~Create `config_change_history`: `id`, `table_name`, `record_id`, `field`, `old_value`, `new_value`, `changed_by`, `changed_at`, `reason`~~
- [x] ~~Create `event_logs` table (separate from telemetry and alarms): `id`, `event_type`, `category` (enum: `SYSTEM`, `DEVICE`, `SECURITY`, `CONFIGURATION`, `COLLECTOR`), `message`, `related_equipment_id` (nullable), `related_user_id` (nullable), `occurred_at` — for events like collector started, device connected/disconnected, config imported, register map imported, device added, user logged in~~
- [x] ~~Design telemetry retention policy: keep raw telemetry for N months, then archive/aggregate into hourly/daily summaries~~
- [x] ~~Write and run all migrations — use `BIGINT UNSIGNED AUTO_INCREMENT` primary keys throughout (integers, not UUIDs — appropriate for a single hospital / single database / single collector deployment; revisit only if distributed collectors or cross-hospital sync become a real requirement)~~
- [x] ~~Add indexes: `telemetry(register_definition_id, collector_timestamp)`, `telemetry(poll_cycle_id)`, `equipment(ip_address)`, `register_definitions` unique on `(equipment_id, address, register_type)`, `alarm_rules(register_definition_id)`~~
- [x] ~~Seed test hospital/site/equipment + unknown registers for local dev~~

### ~~1.2 Collector (Python)~~ [x] completed
- [x] ~~Formally define the **Driver interface** as a class every protocol driver must implement:~~
  ```
  class Driver:
      connect()
      disconnect()
      test_connection()
      read_point()
      read_points()
      get_capabilities()
  ```
  ~~This is what lets the collector stay free of `if protocol == "modbus"` branching entirely.~~
- [x] ~~Implement Modbus TCP driver~~
- [ ] Implement additional protocol drivers: Modbus RTU, OPC UA, MQTT, SNMP
- [x] ~~Build **Device Driver** layer above communication drivers (e.g. "Schneider M221 Driver") for vendor-specific quirks~~
- [x] ~~Build **Session Manager** between collector and driver: open connection, reuse existing connection, detect disconnects, close idle sessions, automatic reconnect — avoids reconnecting on every single poll~~
- [x] ~~Look up each device's driver capabilities (`drivers` table, via `equipment.driver_id`) before polling so the collector never issues an unsupported function code~~
- [x] ~~Load enabled devices + driver config from the database~~
- [x] ~~Load enabled register definitions per device, respecting each register's `poll_profile` (interval + priority — critical registers get scheduled first under load)~~
- [x] ~~Implement **batch reads**: group contiguous register addresses (e.g. 0–4) into a single Modbus request instead of one-by-one, respecting the driver's `max_registers_per_request`~~
- [x] ~~Wrap each polling pass in a `poll_cycles` row (started_at/finished_at/status/duration); every `telemetry` row references its `poll_cycle_id`~~
- [x] ~~Store raw value + `device_timestamp` (if available) + `collector_timestamp` + quality into `telemetry`~~
- [x] ~~Enforce read-only: only Read Holding/Input Registers, Read Coils, Read Discrete Inputs — no write function codes anywhere~~
- [x] ~~Confirm collector has zero interpretation logic~~

### 1.3 Logging
- [ ] Structured collector logs: started, connected/disconnected, read failed, timeout, reconnect successful, telemetry stored, poll cycle completed
- [ ] Write path for `event_logs`: collector lifecycle (started/stopped), device connectivity (connected/disconnected), configuration events (config imported, register map imported, device added), security events (user logged in) — categorized as `SYSTEM`/`DEVICE`/`SECURITY`/`CONFIGURATION`/`COLLECTOR`
- [ ] Hook config edits into `config_change_history` automatically
- [ ] When register naming/scale/unit changes, insert a new `register_definition_versions` row with a fresh `effective_from` rather than overwriting the existing one — preserves historical meaning without duplicating the register itself
- [ ] Retention/archival scheduled job to enforce the telemetry retention policy

---

## Module 2 — Monitoring

### 2.1 Live View
- [ ] Live telemetry view: register name, interpreted value (raw × scale + offset), timestamp
- [ ] Group live values by `register_groups`
- [ ] **Register Browser** (formerly "Register Explorer"): address, name, latest raw value, **quality** (GOOD/BAD/TIMEOUT/etc. — lets biomedical staff see at a glance whether a value is trustworthy), last updated, notes
- [ ] **Event Viewer**: browse `event_logs`, filterable by category (SYSTEM/DEVICE/SECURITY/CONFIGURATION/COLLECTOR), date, and related equipment

### 2.2 Charts
- [ ] Historical charts per register
- [ ] CSV export
- [ ] Chart from aggregated data for long ranges, raw data for short ranges once retention/aggregation exists

### 2.3 Device & System Health
- [ ] Device status: online/offline, last seen, IP, protocol, register count, last poll duration
- [ ] Dashboard health panel: collector running, database connected, last poll time, polling rate, failed reads, success %, average response time
- [ ] **Health Watchdog** — go beyond "is it running": detect collector frozen (no progress despite process alive), poll delay/drift, queue backlog, slow database responses, memory usage trending up

### 2.4 Alarm Engine
- [ ] `alarm_rules` as their own entity linked to a register (not fields on `register_definitions`) — supports multiple rules per register, e.g. Pressure < 5 = Warning, Pressure < 3 = Critical
- [ ] Full operator set per rule: `>`, `<`, `>=`, `<=`, `==`, `!=`, `BETWEEN`, `OUTSIDE RANGE`
- [ ] On new telemetry: fetch latest raw value → apply scale → evaluate against active `alarm_rules` → create alarm if triggered
- [ ] Alarm acknowledgementhttps://kijabehospital.or.ke/images/hospital-logo.png flow (`resolved_by`, resolved timestamp)
- [ ] Alarm history view

---

## Module 3 — Configuration
(Previously "Administration" — split so config objects are separate from security/users)

- [ ] Add/Edit Driver page: name, protocol, capabilities, max registers/request, max concurrent requests (define once per driver, reused across all matching equipment)
- [ ] Add/Edit Device page: name, site, location, manufacturer, model, driver (select from `drivers`), IP, port, unit ID, poll interval, enabled
- [ ] Add/Edit Register page: address, type, name (default "Unknown Register N"), scale, offset, unit, decimals, data type, register group, poll profile, display_order (controls dashboard ordering, e.g. so registers show 1, 5, 17, 102 instead of raw address order)
- [ ] Manage `register_groups`
- [ ] Manage `poll_profiles` (interval + priority: Critical/Normal/Low)
- [ ] Manage `alarm_rules` per register (add/edit multiple rules with different severities)
- [ ] Enable/disable toggles per device and per register

---

## Module 4 — Security & Users

- [ ] User roles (wire up permissions/gates in Laravel using existing `add_role_to_users_table` migration)
- [ ] Permissions per role (who can edit registers, acknowledge alarms, manage users, etc.)
- [ ] Audit log viewer surfacing `config_change_history` (who changed what, when, why)

---

## Module 5 — Commissioning Tools
(Needed most during Milestone 2 on-site work, but build alongside the platform)

- [ ] **Test Connection**: admin clicks a button, Laravel asks the collector to test a device, shows connected/failed + response time
- [ ] **Read Register**: admin enters an address, clicks Read, sees the live raw value immediately
- [ ] **Discovery Workspace** (formerly "Register Discovery"), split into two tools:
  - [ ] Passive Discovery — reads a specified list/range of addresses (e.g. 0–100) and records results
  - [ ] Active Discovery — auto-scans ranges (0–100, 100–200, ...) and logs Valid / Invalid / Illegal Address per address
- [ ] **Import Register Map**: upload CSV/Excel (`address, name, type, scale, unit`) → bulk create/update register definitions

---

## Module 6 — Future Enhancements (not built now, but reserve the space)

- [ ] **API** — future REST endpoints for Devices, Telemetry, Alarms, Reports (enables mobile apps/integrations later)
- [ ] **Backup & Restore** — backup database, restore configuration, export register definitions, export devices
- [ ] **Protocol-agnostic naming** — when OPC UA support is added, note that it exposes "nodes" not "registers"; the driver interface already uses `read_point()`/Telemetry Point terminology so this should slot in cleanly

---

## Milestone 2 — Commissioning (on-site at Kijabe Hospital)
- [ ] Verify physical/network access to the PLC (Ethernet port, IP reachable)
- [ ] Confirm exact PLC model (e.g. Schneider TM221 / M221 / M241 / M251)
- [ ] Confirm protocol details: Modbus TCP, port 502, unit ID
- [ ] Use Test Connection + Read Register tools against the live PLC
- [ ] Observe live values in the Register Browser
- [ ] Request official register map from manufacturer / Schneider integrator / the controls engineer who wrote the PLC logic
- [ ] If no map available, run Active Discovery scan across likely address ranges
- [ ] Cross-reference unknown register values against physical display readings to infer meaning
- [ ] Document findings as you go

---

## Milestone 3 — Production
- [ ] Rename identified registers from "Unknown Register N" to real names, assign register groups
- [ ] Set correct data types per register
- [ ] Configure scale/offset per register (insert a new `register_definition_versions` row rather than overwriting blindly)
- [ ] Add engineering units (%, bar, °C, Nm³/h, etc.)
- [ ] Assign poll profiles (fast for critical values like Oxygen Purity, slow for e.g. Operating Hours)
- [ ] Configure alarm thresholds per register using the full operator set
- [ ] Enable graphing on relevant registers
- [ ] Confirm retention/archival policy is running correctly in production
- [ ] Train biomedical/hospital staff on the dashboard
- [ ] Go live

---

## Standing rules to keep enforcing throughout
- Collector = facts only, never interpretation
- Laravel = interpretation only, via `register_definitions`
- No PLC write operations, ever, in Version 1
- Never lose historical `telemetry.raw_value` — all reinterpretation happens by adding a new `register_definition_versions` row, never by touching stored telemetry
- All config edits get logged to `config_change_history` — no silent changes
- Every telemetry row belongs to a `poll_cycle` — always traceable to a specific polling pass

---

## Architecture is done — start building

The architecture phase is complete. The next mistake to avoid is looping on architecture indefinitely instead of writing code. This design is now **frozen** — see `ARCHITECTURAL_DECISIONS.md` for the invariants that shouldn't change casually.

Build in this order (minimizes rework — later sprints depend on earlier ones):

- [x] **Sprint 1 — Database**: models, migrations, indexes (Module 1.1)
- [x] **Sprint 2 — Drivers**: driver interface, session manager, Modbus TCP driver (Module 1.2)
- [x] **Sprint 3 — Collector**: poll cycles, telemetry storage, batch reads (Module 1.2)
- [ ] **Sprint 4 — Configuration**: devices, registers, poll profiles, drivers admin (Module 3)
- [ ] **Sprint 5 — Monitoring**: live dashboard, Register Browser, device health (Module 2.1–2.3)
- [ ] **Sprint 6 — Alarms & Auditability**: alarm engine, event logs, config change history (Module 2.4, 1.3, 4)
- [ ] **Sprint 7 — Commissioning Tools**: Discovery Workspace, Test Connection, Read Register (Module 5)
- [ ] **Sprint 8 — Production**: deployment, on-site commissioning, register map import (Milestones 2 & 3)
