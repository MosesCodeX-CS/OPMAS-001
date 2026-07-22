import asyncio
import random
import logging
from pymodbus.server import StartAsyncTcpServer
from pymodbus.pdu.device import ModbusDeviceIdentification
from pymodbus.datastore import ModbusSequentialDataBlock, ModbusDeviceContext, ModbusServerContext

# configure logger
logging.basicConfig(level=logging.INFO, format='%(asctime)s [%(levelname)s] %(message)s')
log = logging.getLogger(__name__)

class PlantSimulator:
    def __init__(self) -> None:
        self.cycle = 0
        self.pressure = 6.3
        self.purity = 94.1
        self.flow_rate = 122.0
        self.temperature = 36.5
        self.tank_level = 78.0
        self.compressor_status = 1
        self.bed_a_status = 1
        self.bed_b_status = 0
        self.fault_timer = 0
        self.bed_cycle_length = 20

    def clamp(self, value: float, minimum: float, maximum: float) -> float:
        return max(minimum, min(maximum, value))

    def update_readings(self) -> list[int]:
        self.cycle += 1

        if self.cycle % self.bed_cycle_length == 0:
            self.bed_a_status = 1 if self.bed_b_status == 1 else 0
            self.bed_b_status = 1 - self.bed_a_status

        if self.fault_timer > 0:
            self.fault_timer -= 1
            self.compressor_status = 2
            self.pressure = self.clamp(self.pressure - random.uniform(0.1, 0.3), 3.0, 4.5)
            self.purity = self.clamp(self.purity - random.uniform(0.4, 0.9), 85.0, 92.0)
            self.temperature = self.clamp(self.temperature + random.uniform(0.5, 1.2), 70.0, 85.0)
            self.tank_level = self.clamp(self.tank_level + random.uniform(-0.5, 0.0), 55.0, 88.0)
        else:
            if self.compressor_status == 2:
                self.compressor_status = 1
            self.pressure = self.clamp(self.pressure + random.uniform(-0.06, 0.06), 5.8, 6.8)
            self.purity = self.clamp(self.purity + random.uniform(-0.1, 0.1), 93.0, 95.5)
            self.flow_rate = self.clamp(self.flow_rate + random.uniform(-1.5, 1.5), 115.0, 130.0)
            self.temperature = self.clamp(self.temperature + random.uniform(-0.3, 0.3), 34.0, 40.0)
            self.tank_level = self.clamp(self.tank_level + random.uniform(-0.4, 0.4), 65.0, 92.0)

            if random.random() < 0.05:
                self.fault_timer = random.randint(3, 5)
                self.compressor_status = 2
                log.warning("Simulator Action: Simulating transient compressor fault for %s cycles", self.fault_timer)

        pressure_val = int(round(self.pressure * 10))
        purity_val = int(round(self.purity * 10))
        flow_rate_val = int(round(self.flow_rate))
        temperature_val = int(round(self.temperature))
        tank_level_val = int(round(self.tank_level))

        return [
            pressure_val,
            purity_val,
            flow_rate_val,
            temperature_val,
            tank_level_val,
            self.compressor_status,
            self.bed_a_status,
            self.bed_b_status,
        ]

simulator = PlantSimulator()

async def plc_action(function_code, start_address, address, count, current_registers, set_values):
    if function_code != 3:
        return None

    values = simulator.update_readings()
    offset = address - start_address
    for i in range(min(count, len(values) - offset)):
        current_registers[offset + i] = values[offset + i]

    if simulator.cycle % 5 == 0 or simulator.compressor_status == 2:
        log.info(
            "Simulator read cycle=%s values=%s",
            simulator.cycle,
            values,
        )

    return None

async def run_server():
    hr_block = ModbusSequentialDataBlock(1, [63, 941, 122, 37, 80, 1, 1, 0])

    store = ModbusDeviceContext(
        di=ModbusSequentialDataBlock(1, [0] * 10),
        co=ModbusSequentialDataBlock(1, [0] * 10),
        hr=hr_block,
        ir=ModbusSequentialDataBlock(1, [0] * 10),
    )

    store.simdevice.action = plc_action

    context = ModbusServerContext(devices=store, single=True)

    log.info("Starting Modbus TCP Simulator on 127.0.0.1:5020 with dynamic runtime interceptor")
    await StartAsyncTcpServer(
        context=context,
        address=("127.0.0.1", 5020),
        identity=ModbusDeviceIdentification(),
    )

if __name__ == "__main__":
    try:
        asyncio.run(run_server())
    except KeyboardInterrupt:
        log.info("Simulator stopped.")
