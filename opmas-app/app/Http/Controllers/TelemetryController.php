<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\Telemetry;
use App\Models\PollCycle;
use App\Models\RegisterDefinition;
use App\Models\Equipment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    public function generate(Request $request)
    {
        $type = $request->input('type', 'normal');

        // Check if simulation mode is enabled in settings
        $simulationMode = SystemSetting::getValue('simulation_mode', '1');
        if ($simulationMode === '0') {
            return back()->withErrors(['error' => 'Telemetry simulation is currently disabled in System Settings.']);
        }

        // Get current thresholds
        $pLow = (float) SystemSetting::getValue('pressure_low', 4.0);
        $purLow = (float) SystemSetting::getValue('purity_low', 90.0);
        $fLow = (float) SystemSetting::getValue('flow_rate_low', 1.0);
        $tHigh = (float) SystemSetting::getValue('temperature_high', 80.0);
        $tlLow = (float) SystemSetting::getValue('tank_level_low', 10.0);

        if ($type === 'fault') {
            // Generate a random breach type
            $breachType = rand(1, 5);
            $pressure = ($breachType === 1) ? ($pLow - 0.8) : rand(55, 75) / 10;
            $purity = ($breachType === 2) ? ($purLow - 2.5) : rand(920, 960) / 10;
            $flowRate = ($breachType === 3) ? ($fLow - 0.5) : rand(100, 150);
            $temperature = ($breachType === 4) ? ($tHigh + 5.5) : rand(30, 45);
            $tankLevel = ($breachType === 5) ? ($tlLow - 3.5) : rand(50, 90);
            $compressorStatus = ($breachType === 1 || $breachType === 3) ? 2 : 1; // 2 = FAULT
            $bedA = 0;
            $bedB = 0;
        } else {
            // Normal values
            $pressure = rand(55, 75) / 10; // 5.5 - 7.5
            $purity = rand(925, 965) / 10; // 92.5 - 96.5%
            $flowRate = rand(110, 140);
            $temperature = rand(32, 44);
            $tankLevel = rand(60, 85);
            $compressorStatus = 1; // RUNNING

            $latestBedA = Telemetry::query()
                ->join('register_definitions', 'telemetry.register_definition_id', '=', 'register_definitions.id')
                ->where('register_definitions.address', 7)
                ->latest('telemetry.created_at')
                ->select('telemetry.raw_value')
                ->first();
            $bedA = 1;
            $bedB = 0;

            if ($latestBedA) {
                $bedA = (int) !$latestBedA->raw_value;
                $bedB = (int) !$bedA;
            }
        }

        $equipment = Equipment::where('code', 'SIM-127-5020')->first() ?? Equipment::first();
        if ($equipment) {
            $pollCycle = PollCycle::create([
                'equipment_id' => $equipment->id,
                'started_at' => now(),
                'finished_at' => now(),
                'status' => 'COMPLETED',
                'duration' => 120,
            ]);

            $telemetryData = [
                ['address' => 1, 'raw' => (int)round($pressure * 10)],
                ['address' => 2, 'raw' => (int)round($purity * 10)],
                ['address' => 3, 'raw' => (int)round($flowRate)],
                ['address' => 4, 'raw' => (int)round($temperature)],
                ['address' => 5, 'raw' => (int)round($tankLevel)],
                ['address' => 6, 'raw' => (int)$compressorStatus],
                ['address' => 7, 'raw' => (int)$bedA],
                ['address' => 8, 'raw' => (int)$bedB],
            ];

            foreach ($telemetryData as $data) {
                $regId = RegisterDefinition::where('equipment_id', $equipment->id)->where('address', $data['address'])->value('id');
                if ($regId) {
                    Telemetry::create([
                        'poll_cycle_id' => $pollCycle->id,
                        'register_definition_id' => $regId,
                        'raw_value' => $data['raw'],
                        'device_timestamp' => now(),
                        'collector_timestamp' => now(),
                        'quality' => 'GOOD',
                        'poll_duration_ms' => 15,
                    ]);
                }
            }
        }

        // Evaluate Alarm triggers
        $triggeredAlarms = 0;

        if ($pressure < $pLow) {
            Alarm::create([
                'type' => 'Pressure',
                'severity' => 'CRITICAL',
                'message' => "Pressure critically low: {$pressure} bar (Threshold: {$pLow} bar)",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        if ($purity < $purLow) {
            Alarm::create([
                'type' => 'Purity',
                'severity' => 'CRITICAL',
                'message' => "Oxygen purity critically low: {$purity}% (Threshold: {$purLow}%)",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        if ($temperature > $tHigh) {
            Alarm::create([
                'type' => 'Temperature',
                'severity' => 'WARNING',
                'message' => "High operating temperature: {$temperature}°C (Threshold: {$tHigh}°C)",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        if ($flowRate < $fLow) {
            Alarm::create([
                'type' => 'Flow Rate',
                'severity' => 'WARNING',
                'message' => "Flow rate below normal: {$flowRate} L/min (Threshold: {$fLow} L/min)",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        if ($tankLevel < $tlLow) {
            Alarm::create([
                'type' => 'Tank Level',
                'severity' => 'WARNING',
                'message' => "Tank level critically low: {$tankLevel}% (Threshold: {$tlLow}%)",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        if ($compressorStatus === 2) {
            Alarm::create([
                'type' => 'Compressor',
                'severity' => 'CRITICAL',
                'message' => "Compressor reports electronic fault status code 2.",
                'resolved' => false,
            ]);
            $triggeredAlarms++;
        }

        $msg = "Mock telemetry generated successfully.";
        if ($triggeredAlarms > 0) {
            $msg .= " Triggered {$triggeredAlarms} active alarms due to threshold breaches!";
        }

        return back()->with('status', $msg);
    }
}
