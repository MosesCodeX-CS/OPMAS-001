@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- Header Section -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">Live Plant Status</h1>
        <p class="text-sm text-[#6B7A90] mt-1 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
            <span>Last updated: </span>
            <span id="last-updated" class="font-semibold text-[#1A2A3A]">{{ $reading?->updated_at?->diffForHumans() ?? 'No data yet' }}</span>
        </p>
    </div>

    @if(!auth()->user()->isUser())
        <!-- Telemetry Simulator Widget -->
        <div class="kijabe-card p-4 flex items-center gap-4 bg-white">
            <div class="text-left">
                <p class="text-xs font-bold text-[#1B3A6B] uppercase tracking-wider">Telemetry Control</p>
                <p class="text-[11px] text-[#6B7A90]">Inject test telemetry</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('telemetry.generate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="normal">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#2B8AC6] text-xs font-bold text-white hover:bg-[#2071a5] transition shadow-sm">
                        Generate Normal
                    </button>
                </form>
                <form action="{{ route('telemetry.generate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="fault">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-red-600 text-xs font-bold text-white hover:bg-red-700 transition shadow-sm">
                        Simulate Fault
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

<!-- Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Active Alarms -->
    <div class="kijabe-card p-6 relative overflow-hidden group hover:scale-[1.01] transition-transform duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="bell" class="w-32 h-32 text-red-600"></i>
        </div>
        <p class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-2">Active Alarms</p>
        <p class="text-4xl font-extrabold text-red-600 tracking-tight" id="count-active-alarms">{{ $activeAlarms }}</p>
        <p class="text-xs text-[#6B7A90] mt-2 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
            <span>Critical issues: <strong class="text-red-700 font-bold" id="count-critical-alarms">{{ $critical }}</strong></span>
        </p>
    </div>

    <!-- Equipment Status Summary -->
    <div class="kijabe-card p-6 relative overflow-hidden group hover:scale-[1.01] transition-transform duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="cpu" class="w-32 h-32 text-[#2B8AC6]"></i>
        </div>
        <p class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-3">Equipment Status</p>
        <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-xs text-[#1A2A3A]">
            <div class="flex items-center justify-between">
                <span class="text-[#6B7A90]">Online</span>
                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold font-mono">{{ $equipmentSummary['ONLINE'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#6B7A90]">Offline</span>
                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold font-mono">{{ $equipmentSummary['OFFLINE'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#6B7A90]">Maintenance</span>
                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold font-mono">{{ $equipmentSummary['MAINTENANCE'] ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[#6B7A90]">Fault</span>
                <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-bold font-mono">{{ $equipmentSummary['FAULT'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Upcoming Maintenance -->
    <div class="kijabe-card p-6 relative overflow-hidden group hover:scale-[1.01] transition-transform duration-300">
        <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="calendar" class="w-32 h-32 text-[#E8A020]"></i>
        </div>
        <p class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-3">Upcoming Maintenance</p>
        <div class="space-y-2 max-h-24 overflow-y-auto">
            @forelse($upcomingMaintenance as $item)
                <div class="flex items-center justify-between text-xs border-b border-gray-100 pb-1.5 last:border-0 last:pb-0">
                    <span class="font-mono text-[#2B8AC6] font-semibold">{{ $item->code }}</span>
                    <span class="text-[#1A2A3A]">{{ $item->next_service?->format('M d, Y') }}</span>
                </div>
            @empty
                <p class="text-xs text-[#6B7A90] italic">No maintenance due in 14 days.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Telemetry Gauges Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
    <!-- Pressure -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">Pressure</p>
            <div class="flex items-baseline text-[#1A2A3A]">
                <span class="text-3xl font-bold font-mono tracking-tight" id="telemetry-pressure">{{ $reading?->pressure !== null ? number_format($reading->pressure, 2) : '—' }}</span>
                <span class="text-xs text-[#6B7A90] ml-1 font-semibold">bar</span>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-[#2B8AC6] h-1.5 rounded-full transition-all duration-300" id="bar-pressure" style="width: {{ $reading?->pressure ? min(100, ($reading->pressure / 10) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Purity -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">O₂ Purity</p>
            <div class="flex items-baseline text-[#1A2A3A]">
                <span class="text-3xl font-bold font-mono tracking-tight" id="telemetry-purity">{{ $reading?->purity !== null ? number_format($reading->purity, 2) : '—' }}</span>
                <span class="text-xs text-[#6B7A90] ml-1 font-semibold">%</span>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-emerald-600 h-1.5 rounded-full transition-all duration-300" id="bar-purity" style="width: {{ $reading?->purity ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Flow Rate -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">Flow Rate</p>
            <div class="flex items-baseline text-[#1A2A3A]">
                <span class="text-3xl font-bold font-mono tracking-tight" id="telemetry-flow_rate">{{ $reading?->flow_rate !== null ? number_format($reading->flow_rate, 2) : '—' }}</span>
                <span class="text-xs text-[#6B7A90] ml-1 font-semibold">L/min</span>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" id="bar-flow_rate" style="width: {{ $reading?->flow_rate ? min(100, ($reading->flow_rate / 200) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Temperature -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">Temperature</p>
            <div class="flex items-baseline text-[#1A2A3A]">
                <span class="text-3xl font-bold font-mono tracking-tight" id="telemetry-temperature">{{ $reading?->temperature !== null ? number_format($reading->temperature, 2) : '—' }}</span>
                <span class="text-xs text-[#6B7A90] ml-1 font-semibold">°C</span>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-300" id="bar-temperature" style="width: {{ $reading?->temperature ? min(100, ($reading->temperature / 100) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tank Level -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">Tank Level</p>
            <div class="flex items-baseline text-[#1A2A3A]">
                <span class="text-3xl font-bold font-mono tracking-tight" id="telemetry-tank_level">{{ $reading?->tank_level !== null ? number_format($reading->tank_level, 2) : '—' }}</span>
                <span class="text-xs text-[#6B7A90] ml-1 font-semibold">%</span>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-300" id="bar-tank_level" style="width: {{ $reading?->tank_level ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Compressor Status -->
    <div class="kijabe-card p-5 flex flex-col justify-between hover:scale-[1.03] transition-transform duration-300">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#6B7A90] mb-1">Compressor</p>
            <p class="text-2xl font-bold font-mono tracking-tight transition-colors duration-300" id="telemetry-compressor"
               style="color:{{ ($reading?->compressor_status ?? 0) === 1 ? '#10B981' : (($reading?->compressor_status ?? 0) === 2 ? '#EF4444' : '#6B7280') }};">
                {{ $reading?->compressorStatusLabel() ?? '—' }}
            </p>
        </div>
        <div class="mt-4 flex justify-between text-[10px] text-[#6B7A90]">
            <span>Bed A: <strong id="bed-a-lbl" class="{{ ($reading?->bed_a_status ?? 0) ? 'text-emerald-600' : 'text-gray-400' }}">{{ ($reading?->bed_a_status ?? 0) ? 'Active' : 'Idle' }}</strong></span>
            <span>Bed B: <strong id="bed-b-lbl" class="{{ ($reading?->bed_b_status ?? 0) ? 'text-emerald-600' : 'text-gray-400' }}">{{ ($reading?->bed_b_status ?? 0) ? 'Active' : 'Idle' }}</strong></span>
        </div>
    </div>
</div>

<!-- Historical Trends Chart -->
<div class="kijabe-card p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-[#1B3A6B]">Live Real-time Trend</h2>
            <p class="text-xs text-[#6B7A90]">Historical performance chart</p>
        </div>
        <div class="text-xs text-[#6B7A90] flex items-center gap-4 font-semibold">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-[#2B8AC6]"></span> Pressure</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> O₂ Purity</span>
        </div>
    </div>
    <div class="h-80 w-full relative">
        <canvas id="trendChart"></canvas>
    </div>
</div>

<!-- Active Alarms Feed -->
@if($alarms->count())
<div class="mb-8" id="active-alarms-container">
    <h2 class="text-sm font-bold uppercase tracking-widest text-[#6B7A90] mb-3 flex items-center gap-2">
        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
        <span>Active Alerts Requiring Attention</span>
    </h2>
    <div class="space-y-3">
        @foreach($alarms as $alarm)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl kijabe-card border-l-4
                 {{ $alarm->severity === 'CRITICAL' ? 'border-l-red-600' : ($alarm->severity === 'WARNING' ? 'border-l-amber-500' : 'border-l-[#2B8AC6]') }}">
                <div class="flex items-start gap-3">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded text-white tracking-wider uppercase
                          {{ $alarm->severity === 'CRITICAL' ? 'bg-red-600' : ($alarm->severity === 'WARNING' ? 'bg-amber-600' : 'bg-[#2B8AC6]') }}">
                        {{ $alarm->severity }}
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-[#1A2A3A]">{{ $alarm->message }}</p>
                        <p class="text-xs text-[#6B7A90] mt-1">{{ $alarm->created_at->diffForHumans() }} · Type: {{ $alarm->type }}</p>
                    </div>
                </div>
                @if(!$alarm->resolved && !auth()->user()->isUser())
                    <form action="{{ route('alarms.resolve', $alarm) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-[#1A2A3A] text-xs font-semibold shadow-sm transition">
                            Mark Resolved
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart;
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    const labels = [];
    const pressureData = [];
    const purityData = [];

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pressure (bar)',
                    data: pressureData,
                    borderColor: '#2B8AC6',
                    backgroundColor: 'rgba(43, 138, 198, 0.05)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'O₂ Purity (%)',
                    data: purityData,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.05)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
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
                        color: '#6B7A90'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(0,0,0,0.03)'
                    },
                    ticks: {
                        color: '#6B7A90'
                    },
                    min: 0,
                    max: 12
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        color: '#6B7A90'
                    },
                    min: 80,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    updateTelemetry();
    setInterval(updateTelemetry, 5000);
});

function updateTelemetry() {
    fetch('/api/latest-reading')
        .then(r => r.json())
        .then(data => {
            if (!data) return;

            document.getElementById('telemetry-pressure').textContent = parseFloat(data.pressure).toFixed(2);
            document.getElementById('telemetry-purity').textContent = parseFloat(data.purity).toFixed(2);
            document.getElementById('telemetry-flow_rate').textContent = parseFloat(data.flow_rate).toFixed(2);
            document.getElementById('telemetry-temperature').textContent = parseFloat(data.temperature).toFixed(2);
            document.getElementById('telemetry-tank_level').textContent = parseFloat(data.tank_level).toFixed(2);

            document.getElementById('bar-pressure').style.width = Math.min(100, (data.pressure / 10) * 100) + '%';
            document.getElementById('bar-purity').style.width = data.purity + '%';
            document.getElementById('bar-flow_rate').style.width = Math.min(100, (data.flow_rate / 200) * 100) + '%';
            document.getElementById('bar-temperature').style.width = Math.min(100, (data.temperature / 100) * 100) + '%';
            document.getElementById('bar-tank_level').style.width = data.tank_level + '%';

            const compEl = document.getElementById('telemetry-compressor');
            let compLabel = 'OFF';
            let compColor = '#6B7280';
            if (data.compressor_status === 1) {
                compLabel = 'RUNNING';
                compColor = '#10B981';
            } else if (data.compressor_status === 2) {
                compLabel = 'FAULT';
                compColor = '#EF4444';
            }
            compEl.textContent = compLabel;
            compEl.style.color = compColor;

            const bedALbl = document.getElementById('bed-a-lbl');
            const bedBLbl = document.getElementById('bed-b-lbl');
            bedALbl.textContent = data.bed_a_status ? 'Active' : 'Idle';
            bedBLbl.textContent = data.bed_b_status ? 'Active' : 'Idle';
            
            if (data.bed_a_status) {
                bedALbl.className = 'text-emerald-600 font-bold';
            } else {
                bedALbl.className = 'text-gray-400 font-normal';
            }
            if (data.bed_b_status) {
                bedBLbl.className = 'text-emerald-600 font-bold';
            } else {
                bedBLbl.className = 'text-gray-400 font-normal';
            }

            document.getElementById('last-updated').textContent = 'Just now';

            const timestamp = new Date(data.created_at || new Date()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            if (chart.data.labels.length === 0 || chart.data.labels[chart.data.labels.length - 1] !== timestamp) {
                chart.data.labels.push(timestamp);
                chart.data.datasets[0].data.push(data.pressure);
                chart.data.datasets[1].data.push(data.purity);

                if (chart.data.labels.length > 15) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                    chart.data.datasets[1].data.shift();
                }
                chart.update();
            }
        });
}
</script>
@endsection