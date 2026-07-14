<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\SensorReading;
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
            $bedA = rand(0, 1);
            $bedB = $bedA === 0 ? 1 : 0;
        }

        $reading = SensorReading::create([
            'pressure'          => $pressure,
            'purity'            => $purity,
            'flow_rate'         => $flowRate,
            'temperature'       => $temperature,
            'tank_level'        => $tankLevel,
            'compressor_status' => $compressorStatus,
            'bed_a_status'      => $bedA,
            'bed_b_status'      => $bedB,
        ]);

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
