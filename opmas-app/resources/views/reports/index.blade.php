@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<!-- Header & Date Filter Form -->
<div class="mb-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
    <div>
        <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">Plant Performance Analytics</h1>
        <p class="text-sm text-[#6B7A90] mt-1">Export, filter, and inspect historical sensor readouts and alarms.</p>
    </div>

    <!-- Date Range Picker + Export Form -->
    <div class="flex flex-wrap items-center gap-3">
        <form action="{{ route('reports') }}" method="GET" class="flex flex-wrap items-center gap-3 bg-white border border-[#DDE3EE] p-2 rounded-xl">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-[#6B7A90] uppercase tracking-wider pl-2">Range:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="rounded-lg px-3 py-1.5 text-xs kijabe-input w-36">
                <span class="text-gray-400 text-xs">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="rounded-lg px-3 py-1.5 text-xs kijabe-input w-36">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2B8AC6] hover:bg-[#2071a5] text-xs font-bold text-white transition flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
        </form>

        <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-4 py-3 rounded-xl bg-white border border-[#DDE3EE] hover:bg-gray-50 text-[#1A2A3A] text-xs font-semibold flex items-center gap-1.5 transition shadow-sm">
            <i data-lucide="download" class="w-4 h-4"></i>
            <span>Export CSV</span>
        </a>
    </div>
</div>

<!-- Historical Analytics Graph -->
<div class="kijabe-card p-6 mb-8 bg-white">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-[#1B3A6B]">Sensor Value History</h2>
            <p class="text-xs text-[#6B7A90]">Timeline view of plant telemetry logs for the selected range</p>
        </div>
        <div class="flex flex-wrap gap-4 text-xs text-[#6B7A90] font-semibold">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#2B8AC6]"></span> Pressure</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> O₂ Purity</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Temp</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Tank Level</span>
        </div>
    </div>
    <div class="h-96 w-full relative">
        <canvas id="historicalChart"></canvas>
    </div>
</div>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Today's Stats -->
    <div class="kijabe-card p-6 bg-white">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-4">Today's Summary</h3>
        <p class="text-xs text-[#6B7A90] mb-4">Total sensor readings logged today: <strong class="text-[#1A2A3A] font-semibold font-mono">{{ $dailyStats->total ?? 0 }}</strong></p>
        
        <div class="space-y-3.5 text-xs text-[#1A2A3A]">
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Pressure:</span>
                <span class="font-mono font-bold">{{ $dailyStats->average_pressure ? number_format($dailyStats->average_pressure, 2) . ' bar' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg O₂ Purity:</span>
                <span class="font-mono font-bold">{{ $dailyStats->average_purity ? number_format($dailyStats->average_purity, 2) . '%' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Flow Rate:</span>
                <span class="font-mono font-bold">{{ $dailyStats->average_flow_rate ? number_format($dailyStats->average_flow_rate, 2) . ' L/min' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Temperature:</span>
                <span class="font-mono font-bold">{{ $dailyStats->average_temperature ? number_format($dailyStats->average_temperature, 2) . ' °C' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Tank Level:</span>
                <span class="font-mono font-bold">{{ $dailyStats->average_tank_level ? number_format($dailyStats->average_tank_level, 2) . '%' : '—' }}</span>
            </div>
        </div>
    </div>

    <!-- Weekly Stats -->
    <div class="kijabe-card p-6 bg-white">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-4">7-Day Averages</h3>
        <p class="text-xs text-[#6B7A90] mb-4">Sensor readings logged last 7 days: <strong class="text-[#1A2A3A] font-semibold font-mono">{{ $weeklyStats->total ?? 0 }}</strong></p>
        
        <div class="space-y-3.5 text-xs text-[#1A2A3A]">
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Pressure:</span>
                <span class="font-mono font-bold">{{ $weeklyStats->average_pressure ? number_format($weeklyStats->average_pressure, 2) . ' bar' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg O₂ Purity:</span>
                <span class="font-mono font-bold">{{ $weeklyStats->average_purity ? number_format($weeklyStats->average_purity, 2) . '%' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Flow Rate:</span>
                <span class="font-mono font-bold">{{ $weeklyStats->average_flow_rate ? number_format($weeklyStats->average_flow_rate, 2) . ' L/min' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Temperature:</span>
                <span class="font-mono font-bold">{{ $weeklyStats->average_temperature ? number_format($weeklyStats->average_temperature, 2) . ' °C' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Tank Level:</span>
                <span class="font-mono font-bold">{{ $weeklyStats->average_tank_level ? number_format($weeklyStats->average_tank_level, 2) . '%' : '—' }}</span>
            </div>
        </div>
    </div>

    <!-- Monthly Stats -->
    <div class="kijabe-card p-6 bg-white">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-4">30-Day Averages</h3>
        <p class="text-xs text-[#6B7A90] mb-4">Sensor readings logged last 30 days: <strong class="text-[#1A2A3A] font-semibold font-mono">{{ $monthlyStats->total ?? 0 }}</strong></p>
        
        <div class="space-y-3.5 text-xs text-[#1A2A3A]">
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Pressure:</span>
                <span class="font-mono font-bold">{{ $monthlyStats->average_pressure ? number_format($monthlyStats->average_pressure, 2) . ' bar' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg O₂ Purity:</span>
                <span class="font-mono font-bold">{{ $monthlyStats->average_purity ? number_format($monthlyStats->average_purity, 2) . '%' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Flow Rate:</span>
                <span class="font-mono font-bold">{{ $monthlyStats->average_flow_rate ? number_format($monthlyStats->average_flow_rate, 2) . ' L/min' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Temperature:</span>
                <span class="font-mono font-bold">{{ $monthlyStats->average_temperature ? number_format($monthlyStats->average_temperature, 2) . ' °C' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Avg Tank Level:</span>
                <span class="font-mono font-bold">{{ $monthlyStats->average_tank_level ? number_format($monthlyStats->average_tank_level, 2) . '%' : '—' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Value Ranges -->
    <div class="kijabe-card p-6 bg-white">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-4">Today’s Operating Ranges</h3>
        <div class="space-y-3.5 text-xs text-[#1A2A3A]">
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Pressure:</span>
                <span class="font-mono font-bold text-[#1A2A3A]">{{ $dailyRange->min_pressure ? number_format($dailyRange->min_pressure, 2).' - '.number_format($dailyRange->max_pressure, 2).' bar' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">O₂ Purity:</span>
                <span class="font-mono font-bold text-[#1A2A3A]">{{ $dailyRange->min_purity ? number_format($dailyRange->min_purity, 2).' - '.number_format($dailyRange->max_purity, 2).'%' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Flow Rate:</span>
                <span class="font-mono font-bold text-[#1A2A3A]">{{ $dailyRange->min_flow_rate ? number_format($dailyRange->min_flow_rate, 2).' - '.number_format($dailyRange->max_flow_rate, 2).' L/min' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Temperature:</span>
                <span class="font-mono font-bold text-[#1A2A3A]">{{ $dailyRange->min_temperature ? number_format($dailyRange->min_temperature, 2).' - '.number_format($dailyRange->max_temperature, 2).' °C' : '—' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                <span class="text-[#6B7A90]">Tank Level:</span>
                <span class="font-mono font-bold text-[#1A2A3A]">{{ $dailyRange->min_tank_level ? number_format($dailyRange->min_tank_level, 2).' - '.number_format($dailyRange->max_tank_level, 2).'%' : '—' }}</span>
            </div>
        </div>
    </div>

    <!-- Alarm Breakdown -->
    <div class="kijabe-card p-6 bg-white">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-4">Historical Alarm Log Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-semibold">
            <div class="rounded-xl p-4 bg-red-50 border border-red-100 flex flex-col justify-between">
                <p class="text-[10px] uppercase tracking-widest text-red-600">Critical</p>
                <p class="text-3xl font-bold text-red-600 mt-2 font-mono">{{ $alarmSummary['CRITICAL'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl p-4 bg-amber-50 border border-amber-100 flex flex-col justify-between">
                <p class="text-[10px] uppercase tracking-widest text-amber-700">Warning</p>
                <p class="text-3xl font-bold text-amber-600 mt-2 font-mono">{{ $alarmSummary['WARNING'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl p-4 bg-blue-50 border border-blue-100 flex flex-col justify-between">
                <p class="text-[10px] uppercase tracking-widest text-[#2B8AC6]">Info</p>
                <p class="text-3xl font-bold text-[#2B8AC6] mt-2 font-mono">{{ $alarmSummary['INFO'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('historicalChart').getContext('2d');
    const readings = @json($chartReadings);
    
    const labels = readings.map(r => {
        const d = new Date(r.created_at);
        return d.toLocaleDateString([], {month: 'short', day: '2-digit'}) + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    });
    
    const pressure = readings.map(r => r.pressure);
    const purity = readings.map(r => r.purity);
    const temperature = readings.map(r => r.temperature);
    const tankLevel = readings.map(r => r.tank_level);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pressure',
                    data: pressure,
                    borderColor: '#2B8AC6',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y'
                },
                {
                    label: 'O₂ Purity',
                    data: purity,
                    borderColor: '#059669',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y'
                },
                {
                    label: 'Temp',
                    data: temperature,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y'
                },
                {
                    label: 'Tank Level',
                    data: tankLevel,
                    borderColor: '#a855f7',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.2,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.03)'
                    },
                    ticks: {
                        color: '#6B7A90',
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(0,0,0,0.03)'
                    },
                    ticks: {
                        color: '#6B7A90'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection