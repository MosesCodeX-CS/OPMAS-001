@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-white">Live Plant Status</h1>
    <p class="text-sm text-gray-500 mt-0.5">
        Last updated: <span id="last-updated">{{ $reading?->updated_at?->diffForHumans() ?? 'No data yet' }}</span>
    </p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @include('partials.gauge', ['label' => 'Pressure',    'value' => $reading?->pressure,    'unit' => 'bar',   'id' => 'pressure'])
    @include('partials.gauge', ['label' => 'O₂ Purity',  'value' => $reading?->purity,      'unit' => '%',     'id' => 'purity'])
    @include('partials.gauge', ['label' => 'Flow Rate',   'value' => $reading?->flow_rate,   'unit' => 'L/min', 'id' => 'flow_rate'])
    @include('partials.gauge', ['label' => 'Temperature', 'value' => $reading?->temperature, 'unit' => '°C',    'id' => 'temperature'])
    @include('partials.gauge', ['label' => 'Tank Level',  'value' => $reading?->tank_level,  'unit' => '%',     'id' => 'tank_level'])

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Compressor</p>
        <p class="text-2xl font-mono font-bold
            {{ ($reading?->compressor_status ?? 0) === 1 ? 'text-green-400' :
               (($reading?->compressor_status ?? 0) === 2 ? 'text-red-400' : 'text-gray-500') }}">
            {{ $reading?->compressorStatusLabel() ?? '—' }}
        </p>
        <div class="flex gap-3 mt-3 text-xs text-gray-500">
            <span>Bed A:
                <span class="{{ ($reading?->bed_a_status ?? 0) ? 'text-green-400' : 'text-gray-600' }}">
                    {{ ($reading?->bed_a_status ?? 0) ? 'Active' : 'Idle' }}
                </span>
            </span>
            <span>Bed B:
                <span class="{{ ($reading?->bed_b_status ?? 0) ? 'text-green-400' : 'text-gray-600' }}">
                    {{ ($reading?->bed_b_status ?? 0) ? 'Active' : 'Idle' }}
                </span>
            </span>
        </div>
    </div>
</div>

@if($alarms->count())
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-3">Active Alarms</h2>
    <div class="space-y-2">
        @foreach($alarms as $alarm)
            @include('partials.alarm-badge', ['alarm' => $alarm])
        @endforeach
    </div>
</div>
@endif

<script>
setInterval(() => {
    fetch('/api/latest-reading')
        .then(r => r.json())
        .then(data => {
            if (!data) return;
            ['pressure','purity','flow_rate','temperature','tank_level'].forEach(key => {
                const el = document.getElementById(key);
                if (el && data[key] !== null) el.textContent = parseFloat(data[key]).toFixed(2);
            });
            document.getElementById('last-updated').textContent = 'Just now';
        });
}, 5000);
</script>
@endsection