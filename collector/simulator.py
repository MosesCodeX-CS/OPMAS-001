import logging
import asyncio
import random
from pymodbus.server import StartAsyncTcpServer
from pymodbus.pdu.device import ModbusDeviceIdentification
from pymodbus.datastore import ModbusSequentialDataBlock, ModbusDeviceContext, ModbusServerContext

# configure logger
logging.basicConfig(level=logging.INFO, format='%(asctime)s [%(levelname)s] %(message)s')
log = logging.getLogger(__name__)

# Global cycle counter for PSA bed alternation
cycle_counter = 0

async def plc_action(function_code, start_address, address, count, current_registers, set_values):
    global cycle_counter
    # Only intercept holding registers read operations (function code 3)
    if function_code == 3:
        cycle_counter += 1
        
        # Alternating beds every 5 read cycles
        bed_a_status = 1 if (cycle_counter // 5) % 2 == 0 else 0
        bed_b_status = 1 - bed_a_status
        
        # Fluctuate values in normal ranges to simulate dynamic oxygen plant process
        pressure_val = int(random.uniform(5.5, 7.5) * 10)
        purity_val = int(random.uniform(92.5, 95.5) * 10)
        flow_rate_val = int(random.uniform(115, 135))
        temperature_val = int(random.uniform(32, 44))
        tank_level_val = int(random.uniform(65, 85))
        compressor_status = 1

        # 5% chance of simulating a critical fault reading (e.g. pressure drops below 4.0 bar)
        if random.random() < 0.05:
            pressure_val = int(3.2 * 10)  # Low Pressure
            compressor_status = 2  # FAULT
            log.warning("Simulator Action: Simulating Critical Fault Event (Low Pressure)!")
        
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
        
        # Write the dynamic values into the server's active runtime register memory in-place
        offset = address - start_address
        for i in range(min(count, len(values) - offset)):
            current_registers[offset + i] = values[offset + i]
            
        log.info(f"Simulator Action: Updated active registers on-the-fly: {current_registers[offset : offset + count]}")
    return None

async def run_server():
    # Initial holding registers values (address 1, 8 values):
    hr_block = ModbusSequentialDataBlock(1, [55, 935, 120, 35, 72, 1, 1, 0])
    
    store = ModbusDeviceContext(
        di=ModbusSequentialDataBlock(1, [0]*10),
        co=ModbusSequentialDataBlock(1, [0]*10),
        hr=hr_block,
        ir=ModbusSequentialDataBlock(1, [0]*10)
    )
    
    # Assign custom runtime interceptor action
    store.simdevice.action = plc_action
    
    context = ModbusServerContext(devices=store, single=True)
    
    log.info("Starting Modbus TCP Simulator on 127.0.0.1:5020 with dynamic runtime interceptor")
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
