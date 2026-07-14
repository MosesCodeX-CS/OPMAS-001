import logging
import asyncio
import random
from pymodbus.server import StartAsyncTcpServer
from pymodbus.pdu.device import ModbusDeviceIdentification
from pymodbus.datastore import ModbusSequentialDataBlock, ModbusDeviceContext, ModbusServerContext

# configure logger
logging.basicConfig(level=logging.INFO, format='%(asctime)s [%(levelname)s] %(message)s')
log = logging.getLogger(__name__)

async def run_server():
    # Initial holding registers values (address 1, 8 values):
    # 0: pressure (5.5 bar -> scaled by 10 is 55)
    # 1: purity (93.5% -> scaled by 10 is 935)
    # 2: flow_rate (120 L/min -> scaled by 1 is 120)
    # 3: temperature (35 C -> scaled by 1 is 35)
    # 4: tank_level (72% -> scaled by 1 is 72)
    # 5: compressor_status (1 = RUNNING)
    # 6: bed_a_status (1 = Active)
    # 7: bed_b_status (0 = Idle)
    hr_block = ModbusSequentialDataBlock(1, [55, 935, 120, 35, 72, 1, 1, 0])
    
    store = ModbusDeviceContext(
        di=ModbusSequentialDataBlock(1, [0]*10),
        co=ModbusSequentialDataBlock(1, [0]*10),
        hr=hr_block,
        ir=ModbusSequentialDataBlock(1, [0]*10)
    )
    context = ModbusServerContext(devices=store, single=True)
    
    async def update_registers():
        bed_a_status = 1
        bed_b_status = 0
        counter = 0

        while True:
            await asyncio.sleep(4)
            counter += 1
            
            # Alternating beds every 10 cycles
            if counter % 10 == 0:
                bed_a_status = 1 - bed_a_status
                bed_b_status = 1 - bed_b_status
            
            # Fluctuate values in normal ranges
            pressure_val = int(random.uniform(5.5, 7.5) * 10)
            purity_val = int(random.uniform(92.5, 95.5) * 10)
            flow_rate_val = int(random.uniform(115, 135))
            temperature_val = int(random.uniform(32, 44))
            tank_level_val = int(random.uniform(65, 85))
            compressor_status = 1

            # 5% chance of simulating a critical fault reading (e.g. pressure drops)
            if random.random() < 0.05:
                pressure_val = int(3.2 * 10)  # Low Pressure
                compressor_status = 2  # FAULT
                log.warning("Simulator: Simulating Critical Fault Event (Low Pressure)!")
            
            values = [
                pressure_val,
                purity_val,
                flow_rate_val,
                temperature_val,
                tank_level_val,
                compressor_status,
                bed_a_status,
                bed_b_status
            ]
            
            # Write directly to the simdata holding registers list
            store.simdevice.simdata[2][0].values = values
            log.info(f"Simulator updated PLC holding registers: {values}")
            
    asyncio.create_task(update_registers())
    
    log.info("Starting Modbus TCP Simulator on 127.0.0.1:5020")
    await StartAsyncTcpServer(
        context=context,
        address=("127.0.0.1", 5020),
        identity=ModbusDeviceIdentification()
    )

if __name__ == "__main__":
    try:
        asyncio.run(run_server())
    except KeyboardInterrupt:
        log.info("Simulator stopped.")
