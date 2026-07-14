@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold" style="color:#1B3A6B;">Reports</h1>
    <p class="text-sm mt-0.5" style="color:#6B7A90;">Daily and 30-day summaries from plant sensor data.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    <div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">Today's Summary</h2>
        <p class="text-sm mb-4" style="color:#1A2A3A;">Total readings today: <strong>{{ $dailyStats->total ?? 0 }}</strong></p>
        <div class="space-y-3 text-sm" style="color:#1A2A3A;">
            <p>Pressure avg: <strong>{{ $dailyStats->average_pressure ? number_format($dailyStats->average_pressure, 2) . ' bar' : 'N/A' }}</strong></p>
            <p>O₂ Purity avg: <strong>{{ $dailyStats->average_purity ? number_format($dailyStats->average_purity, 2) . '%' : 'N/A' }}</strong></p>
            <p>Flow Rate avg: <strong>{{ $dailyStats->average_flow_rate ? number_format($dailyStats->average_flow_rate, 2) . ' L/min' : 'N/A' }}</strong></p>
            <p>Temperature avg: <strong>{{ $dailyStats->average_temperature ? number_format($dailyStats->average_temperature, 2) . ' °C' : 'N/A' }}</strong></p>
            <p>Tank Level avg: <strong>{{ $dailyStats->average_tank_level ? number_format($dailyStats->average_tank_level, 2) . '%' : 'N/A' }}</strong></p>
        </div>
    </div>

    <div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">Last 30 Days</h2>
        <p class="text-sm mb-4" style="color:#1A2A3A;">Total readings: <strong>{{ $monthlyStats->total ?? 0 }}</strong></p>
        <div class="space-y-3 text-sm" style="color:#1A2A3A;">
            <p>Pressure avg: <strong>{{ $monthlyStats->average_pressure ? number_format($monthlyStats->average_pressure, 2) . ' bar' : 'N/A' }}</strong></p>
            <p>O₂ Purity avg: <strong>{{ $monthlyStats->average_purity ? number_format($monthlyStats->average_purity, 2) . '%' : 'N/A' }}</strong></p>
            <p>Flow Rate avg: <strong>{{ $monthlyStats->average_flow_rate ? number_format($monthlyStats->average_flow_rate, 2) . ' L/min' : 'N/A' }}</strong></p>
            <p>Temperature avg: <strong>{{ $monthlyStats->average_temperature ? number_format($monthlyStats->average_temperature, 2) . ' °C' : 'N/A' }}</strong></p>
            <p>Tank Level avg: <strong>{{ $monthlyStats->average_tank_level ? number_format($monthlyStats->average_tank_level, 2) . '%' : 'N/A' }}</strong></p>
        </div>
    </div>
</div>

<div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
    <p class="text-sm" style="color:#6B7A90;">These reports are generated from historical sensor readings. When the collector is polling the PLC, this page will provide real operational insights and support future PDF export enhancements.</p>
</div>
@endsection