@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold" style="color:#1B3A6B;">Live Plant Status</h1>
    <p class="text-sm mt-0.5" style="color:#6B7A90;">
        Last updated: <span id="last-updated">{{ $reading?->updated_at?->diffForHumans() ?? 'No data yet' }}</span>
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#6B7A90;">Active Alarms</p>
        <p class="text-3xl font-bold" style="color:#B91C1C;">{{ $activeAlarms }}</p>
        <p class="text-sm mt-2" style="color:#6B7A90;">Critical: {{ $critical }}</p>
    </div>
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#6B7A90;">Equipment Status</p>
        <div class="space-y-2 text-sm" style="color:#1A2A3A;">
            <p>Online: <strong>{{ $equipmentSummary['ONLINE'] ?? 0 }}</strong></p>
            <p>Offline: <strong>{{ $equipmentSummary['OFFLINE'] ?? 0 }}</strong></p>
            <p>Maintenance: <strong>{{ $equipmentSummary['MAINTENANCE'] ?? 0 }}</strong></p>
            <p>Fault: <strong>{{ $equipmentSummary['FAULT'] ?? 0 }}</strong></p>
        </div>
    </div>
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#6B7A90;">Upcoming Maintenance</p>
        <div class="space-y-2 text-sm" style="color:#1A2A3A;">
            @forelse($upcomingMaintenance as $item)
                <p><strong>{{ $item->code }}</strong> due {{ $item->next_service?->format('Y-m-d') }}</p>
            @empty
                <p>No maintenance due in the next 14 days.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @include('partials.gauge', ['label' => 'Pressure',    'value' => $reading?->pressure,    'unit' => 'bar',   'id' => 'pressure'])
    @include('partials.gauge', ['label' => 'O₂ Purity',  'value' => $reading?->purity,      'unit' => '%',     'id' => 'purity'])
    @include('partials.gauge', ['label' => 'Flow Rate',   'value' => $reading?->flow_rate,   'unit' => 'L/min', 'id' => 'flow_rate'])
    @include('partials.gauge', ['label' => 'Temperature', 'value' => $reading?->temperature, 'unit' => '°C',    'id' => 'temperature'])
    @include('partials.gauge', ['label' => 'Tank Level',  'value' => $reading?->tank_level,  'unit' => '%',     'id' => 'tank_level'])

    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#6B7A90;">Compressor</p>
        <p class="text-2xl font-mono font-bold"
           style="color:{{ ($reading?->compressor_status ?? 0) === 1 ? '#15803D' : (($reading?->compressor_status ?? 0) === 2 ? '#B91C1C' : '#6B7A90') }};">
            {{ $reading?->compressorStatusLabel() ?? '—' }}
        </p>
        <div class="flex gap-3 mt-3 text-xs" style="color:#6B7A90;">
            <span>Bed A:
                <span style="color:{{ ($reading?->bed_a_status ?? 0) ? '#15803D' : '#9CA3AF' }};">
                    {{ ($reading?->bed_a_status ?? 0) ? 'Active' : 'Idle' }}
                </span>
            </span>
            <span>Bed B:
                <span style="color:{{ ($reading?->bed_b_status ?? 0) ? '#15803D' : '#9CA3AF' }};">
                    {{ ($reading?->bed_b_status ?? 0) ? 'Active' : 'Idle' }}
                </span>
            </span>
        </div>
    </div>
</div>

@if($alarms->count())
<div class="mb-6">
    <h2 class="text-sm font-semibold uppercase tracking-widest mb-3" style="color:#1B3A6B;">Active Alarms</h2>
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