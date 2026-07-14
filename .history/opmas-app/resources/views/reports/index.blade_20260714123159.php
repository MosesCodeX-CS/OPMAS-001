@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:#1B3A6B;">Reports</h1>
        <p class="text-sm mt-0.5" style="color:#6B7A90;">Daily, weekly, and monthly summaries from plant sensor data.</p>
    </div>
    <a href="{{ route('reports.export') }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#1B3A6B;">
        Export CSV
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
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
        <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">Last 7 Days</h2>
        <p class="text-sm mb-4" style="color:#1A2A3A;">Total readings: <strong>{{ $weeklyStats->total ?? 0 }}</strong></p>
        <div class="space-y-3 text-sm" style="color:#1A2A3A;">
            <p>Pressure avg: <strong>{{ $weeklyStats->average_pressure ? number_format($weeklyStats->average_pressure, 2) . ' bar' : 'N/A' }}</strong></p>
            <p>O₂ Purity avg: <strong>{{ $weeklyStats->average_purity ? number_format($weeklyStats->average_purity, 2) . '%' : 'N/A' }}</strong></p>
            <p>Flow Rate avg: <strong>{{ $weeklyStats->average_flow_rate ? number_format($weeklyStats->average_flow_rate, 2) . ' L/min' : 'N/A' }}</strong></p>
            <p>Temperature avg: <strong>{{ $weeklyStats->average_temperature ? number_format($weeklyStats->average_temperature, 2) . ' °C' : 'N/A' }}</strong></p>
            <p>Tank Level avg: <strong>{{ $weeklyStats->average_tank_level ? number_format($weeklyStats->average_tank_level, 2) . '%' : 'N/A' }}</strong></p>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    <div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">Today’s Value Range</h2>
        <div class="space-y-3 text-sm" style="color:#1A2A3A;">
            <p>Pressure: <strong>{{ $dailyRange->min_pressure ? number_format($dailyRange->min_pressure, 2).' - '.number_format($dailyRange->max_pressure, 2).' bar' : 'N/A' }}</strong></p>
            <p>O₂ Purity: <strong>{{ $dailyRange->min_purity ? number_format($dailyRange->min_purity, 2).' - '.number_format($dailyRange->max_purity, 2).'%' : 'N/A' }}</strong></p>
            <p>Flow Rate: <strong>{{ $dailyRange->min_flow_rate ? number_format($dailyRange->min_flow_rate, 2).' - '.number_format($dailyRange->max_flow_rate, 2).' L/min' : 'N/A' }}</strong></p>
            <p>Temperature: <strong>{{ $dailyRange->min_temperature ? number_format($dailyRange->min_temperature, 2).' - '.number_format($dailyRange->max_temperature, 2).' °C' : 'N/A' }}</strong></p>
            <p>Tank Level: <strong>{{ $dailyRange->min_tank_level ? number_format($dailyRange->min_tank_level, 2).' - '.number_format($dailyRange->max_tank_level, 2).'%' : 'N/A' }}</strong></p>
        </div>
    </div>

    <div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">30-Day Value Range</h2>
        <div class="space-y-3 text-sm" style="color:#1A2A3A;">
            <p>Pressure: <strong>{{ $monthlyRange->min_pressure ? number_format($monthlyRange->min_pressure, 2).' - '.number_format($monthlyRange->max_pressure, 2).' bar' : 'N/A' }}</strong></p>
            <p>O₂ Purity: <strong>{{ $monthlyRange->min_purity ? number_format($monthlyRange->min_purity, 2).' - '.number_format($monthlyRange->max_purity, 2).'%' : 'N/A' }}</strong></p>
            <p>Flow Rate: <strong>{{ $monthlyRange->min_flow_rate ? number_format($monthlyRange->min_flow_rate, 2).' - '.number_format($monthlyRange->max_flow_rate, 2).' L/min' : 'N/A' }}</strong></p>
            <p>Temperature: <strong>{{ $monthlyRange->min_temperature ? number_format($monthlyRange->min_temperature, 2).' - '.number_format($monthlyRange->max_temperature, 2).' °C' : 'N/A' }}</strong></p>
            <p>Tank Level: <strong>{{ $monthlyRange->min_tank_level ? number_format($monthlyRange->min_tank_level, 2).' - '.number_format($monthlyRange->max_tank_level, 2).'%' : 'N/A' }}</strong></p>
        </div>
    </div>
</div>

<div class="rounded-xl p-6 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
    <h2 class="text-sm font-semibold uppercase tracking-widest mb-4" style="color:#6B7A90;">Alarm Summary</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm" style="color:#1A2A3A;">
        <div class="rounded-lg p-4 bg-[#FEF2F2]">
            <p class="text-xs uppercase tracking-widest" style="color:#B91C1C;">Critical</p>
            <p class="text-2xl font-bold">{{ $alarmSummary['CRITICAL'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg p-4 bg-[#FFFBEB]">
            <p class="text-xs uppercase tracking-widest" style="color:#92400E;">Warning</p>
            <p class="text-2xl font-bold">{{ $alarmSummary['WARNING'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg p-4 bg-[#EFF6FF]">
            <p class="text-xs uppercase tracking-widest" style="color:#1B3A6B;">Info</p>
            <p class="text-2xl font-bold">{{ $alarmSummary['INFO'] ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection