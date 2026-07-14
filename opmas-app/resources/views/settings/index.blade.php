@extends('layouts.app')
@section('title', 'System Settings')

@section('content')
<!-- Header -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">System Configuration</h1>
    <p class="text-sm text-[#6B7A90] mt-1">Configure telemetry thresholds, alarm limits, and simulator engines.</p>
</div>

<!-- Settings Panel Form -->
<div class="kijabe-card p-8 max-w-2xl bg-white shadow-sm">
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6 text-sm">
        @csrf
        @method('PUT')
        
        <h3 class="text-sm font-bold uppercase tracking-wider text-[#2B8AC6] border-b border-gray-150 pb-3">Alarm Thresholds</h3>

        <!-- Pressure Low Threshold -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Critical Low Pressure Threshold (bar)</label>
            <input type="number" step="0.1" name="pressure_low" required value="{{ $settings['pressure_low']?->value ?? '4.0' }}" class="w-full rounded-lg px-4 py-2.5 kijabe-input font-mono">
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Alarms are triggered if pressure drops below this limit. Standard value: 4.0 bar.</span>
        </div>

        <!-- Purity Low Threshold -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Critical Low O₂ Purity Threshold (%)</label>
            <input type="number" step="0.1" name="purity_low" required value="{{ $settings['purity_low']?->value ?? '90.0' }}" class="w-full rounded-lg px-4 py-2.5 kijabe-input font-mono">
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Alarms are triggered if oxygen purity falls below this level. Standard value: 90.0%.</span>
        </div>

        <!-- Flow Rate Low Threshold -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Warning Low Flow Rate Threshold (L/min)</label>
            <input type="number" step="0.1" name="flow_rate_low" required value="{{ $settings['flow_rate_low']?->value ?? '1.0' }}" class="w-full rounded-lg px-4 py-2.5 kijabe-input font-mono">
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Warning alerts are generated when gas flow falls below this value. Standard value: 1.0 L/min.</span>
        </div>

        <!-- Temperature High Threshold -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Warning High Temperature Threshold (°C)</label>
            <input type="number" step="0.1" name="temperature_high" required value="{{ $settings['temperature_high']?->value ?? '80.0' }}" class="w-full rounded-xl px-4 py-2.5 kijabe-input font-mono">
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Alerts are logged if compressor or plant operating temp exceeds this value. Standard value: 80.0°C.</span>
        </div>

        <!-- Tank Level Low Threshold -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Warning Low Tank Level Threshold (%)</label>
            <input type="number" step="0.1" name="tank_level_low" required value="{{ $settings['tank_level_low']?->value ?? '10.0' }}" class="w-full rounded-lg px-4 py-2.5 kijabe-input font-mono">
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Warning alerts are triggered if oxygen tank volume percentage falls below this value. Standard value: 10.0%.</span>
        </div>

        <h3 class="text-sm font-bold uppercase tracking-wider text-[#2B8AC6] border-b border-gray-150 pt-6 pb-3">Simulator Engine Parameters</h3>

        <!-- Simulation Toggle -->
        <div>
            <label class="block text-xs font-bold text-[#1A2A3A] mb-1.5 uppercase">Mock Telemetry Generation</label>
            <select name="simulation_mode" class="w-full rounded-lg px-4 py-2.5 kijabe-input bg-white">
                <option value="1" {{ ($settings['simulation_mode']?->value ?? '1') === '1' ? 'selected' : '' }}>Enabled (Allows Manual/Automatic injects)</option>
                <option value="0" {{ ($settings['simulation_mode']?->value ?? '1') === '0' ? 'selected' : '' }}>Disabled (Blocks all mock injects)</option>
            </select>
            <span class="text-[11px] text-[#6B7A90] mt-1 block">Controls whether operators can manually trigger simulation data points on the dashboard.</span>
        </div>

        <!-- Submit Button -->
        <div class="pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-xs font-bold text-white shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save System Thresholds</span>
            </button>
        </div>
    </form>
</div>
@endsection
