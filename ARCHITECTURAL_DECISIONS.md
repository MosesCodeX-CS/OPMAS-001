# OPMAS-001 — Architectural Decisions

Frozen architecture, Version 1. These are the decisions that should **not** be casually
changed during implementation. If a future change genuinely requires breaking one of
these, treat it as a deliberate architectural revision — write down why, don't just
patch around it in code.

## Core boundary

1. **The collector stores raw values only.** It never scales, converts units, or
   interprets a reading in any way.
2. **The collector never performs scaling or unit conversion**, even if it would be
   "easier" to multiply by scale inline. That logic belongs to Laravel.
3. **Laravel is responsible for all interpretation** — turning `raw_value` +
   `register_definition_versions` into a meaningful reading (name, scaled value, unit).

## Data integrity

4. **Historical telemetry is immutable.** Once a `telemetry` row is written, it is never
   edited to "fix" a value. Reinterpretation happens by changing metadata
   (`register_definition_versions`), not by touching stored facts.
5. **Register definitions are versioned.** A register keeps one stable
   `register_definitions` row for life; naming/scale/unit changes go into
   `register_definition_versions` with an `effective_from`, not overwritten in place.
6. **Every telemetry row belongs to a `poll_cycle`.** Telemetry is always traceable to
   the specific polling pass that produced it.
7. **Config changes are audited.** Any edit to devices, registers, poll profiles, or
   alarm rules is logged to `config_change_history` — who changed what, when, why.

## Communication

8. **Drivers implement a common interface**: `connect()`, `disconnect()`,
   `test_connection()`, `read_point()`, `read_points()`, `get_capabilities()`. The
   collector never branches on protocol (`if protocol == "modbus"` is a smell — fix it
   at the driver layer, not in collector logic).
9. **Capabilities belong to the driver, not the equipment.** Equipment references a
   `driver_id`; it does not duplicate capability flags per device.
10. **Version 1 is strictly read-only.** No write function codes (Write Single/Multiple
    Register, Force Coil, etc.) exist anywhere in the codebase. This is a hospital
    oxygen plant — the system observes, it does not command.

## Operations

11. **No direct database edits in production.** Configuration changes go through the
    Laravel application (so they get audited and versioned), never through raw SQL
    against the live database.
12. **Primary keys are `BIGINT AUTO_INCREMENT`, not UUIDs**, for Version 1. This fits a
    single hospital / single database / single collector deployment. Revisit only if
    distributed collectors or multi-hospital sync become a real, funded requirement —
    not preemptively.

## Naming note (forward-looking, not a current change)

`register_definitions` may eventually evolve into a protocol-agnostic
`telemetry_points` concept if OPC UA, MQTT, or other protocols are added (Modbus
register / OPC UA node / MQTT topic all become one "Telemetry Point" abstraction). Not
renaming now — just leaving this note so a future refactor doesn't reinvent it.

---

*If you're reading this months later, or someone else is contributing to OPMAS: these
principles are what keep the implementation aligned with the design. Deviating from
them should be a conscious decision, documented here, not a quiet shortcut.*

## Note

This architecture is intentionally frozen for Version 1 to avoid unnecessary redesign
during implementation.

That does not mean it can never change.

If implementation or operational experience reveals a better approach, update this
document first, record the reasoning, and then implement the change.

Architecture is a guide, not a prison.
