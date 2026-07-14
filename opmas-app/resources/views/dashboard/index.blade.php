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

<!-- Plant Diagnostics & Overall Health -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Health Index Gauge -->
    <div class="kijabe-card p-6 bg-white flex flex-col md:flex-row items-center gap-6 lg:col-span-2">
        <div class="relative w-36 h-36 flex-shrink-0 flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90">
                <circle cx="72" cy="72" r="60" stroke="#F4F6F9" stroke-width="12" fill="transparent" />
                <circle cx="72" cy="72" r="60" stroke="#10B981" stroke-width="12" fill="transparent"
                        stroke-dasharray="377" stroke-dashoffset="0" stroke-linecap="round"
                        class="transition-all duration-1000 ease-out" id="health-ring" />
            </svg>
            <div class="absolute flex flex-col items-center justify-center">
                <span class="text-3xl font-extrabold text-[#1B3A6B]" id="health-score">100%</span>
                <span class="text-[9px] font-bold text-[#6B7A90] uppercase tracking-wider">Health Index</span>
            </div>
        </div>
        
        <div class="flex-1 text-center md:text-left">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1B3A6B] mb-2 flex items-center justify-center md:justify-start gap-1.5">
                <i data-lucide="activity" class="w-4 h-4 text-[#2B8AC6]"></i>
                <span>Live Plant Diagnostics</span>
            </h3>
            <p class="text-xs text-emerald-700 font-semibold leading-relaxed mb-3" id="diagnostics-text">
                All components operating normally. Zeolite PSA process running at peak efficiency.
            </p>
            <div id="diagnostics-reasons" class="space-y-1.5 text-[11px] text-[#6B7A90] font-mono">
                <!-- Reasons for score decreases -->
            </div>
        </div>
    </div>

    <!-- Active Process Phase Card -->
    <div class="kijabe-card p-6 bg-white flex flex-col justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#6B7A90] mb-3">PSA Cycle Phase</p>
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-lg bg-[#2B8AC6]/10 text-[#2B8AC6] flex-shrink-0">
                    <i data-lucide="refresh-cw" class="w-5 h-5" id="cycle-icon"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-[#1B3A6B] uppercase tracking-wide" id="cycle-label">Adsorption Phase</p>
                    <p class="text-[10px] text-[#6B7A90] leading-snug mt-0.5" id="cycle-desc">Bed A is generating gas. Bed B venting nitrogen.</p>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-100 pt-4 flex items-center justify-between text-xs text-[#6B7A90]">
            <span>Cycle frequency:</span>
            <span class="font-bold text-[#1A2A3A] font-mono">40s intervals</span>
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

    // Run initial update with server data
    const initialData = @json($reading);
    if (initialData) {
        updateDashboard(initialData);
    }

    // Listen to global status updates from top navbar
    window.addEventListener('system-status-updated', (event) => {
        const data = event.detail.reading;
        if (!data) return;
        updateDashboard(data);
    });
});

function updateDashboard(data) {
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
    if (bedALbl && bedBLbl) {
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
    }

    // Calculate Plant Health Index dynamically
    let healthScore = 100;
    let reasons = [];
    
    if (data.compressor_status === 2) {
        healthScore -= 50;
        reasons.push("Compressor reports CRITICAL FAULT state.");
    } else if (data.compressor_status === 0) {
        healthScore -= 20;
        reasons.push("Compressor is STANDBY / SHUTDOWN.");
    }
    
    if (data.pressure < 4.0) {
        healthScore -= 30;
        reasons.push("Pressure is critically low (< 4.0 bar).");
    } else if (data.pressure < 4.8) {
        healthScore -= 10;
        reasons.push("Pressure is sub-optimal (< 4.8 bar).");
    }
    
    if (data.purity < 90.0) {
        healthScore -= 40;
        reasons.push("O₂ Purity is critically low (< 90.0%).");
    } else if (data.purity < 92.5) {
        healthScore -= 15;
        reasons.push("O₂ Purity is warning low (< 92.5%).");
    }
    
    if (data.temperature >= 80.0) {
        healthScore -= 25;
        reasons.push("Plant temperature is critically high (>= 80.0°C).");
    } else if (data.temperature >= 55.0) {
        healthScore -= 10;
        reasons.push("Plant operating temperature is elevated.");
    }
    
    if (data.tank_level < 15.0) {
        healthScore -= 15;
        reasons.push("Oxygen reserve tank is low (< 15.0%).");
    }
    
    healthScore = Math.max(0, healthScore);
    
    // Update UI elements
    const scoreVal = document.getElementById('health-score');
    if (scoreVal) scoreVal.textContent = healthScore + '%';
    
    // Update SVG circle stroke
    const ring = document.getElementById('health-ring');
    if (ring) {
        const offset = 377 - (377 * healthScore / 100);
        ring.style.strokeDashoffset = offset;
        
        if (healthScore >= 80) {
            ring.setAttribute('stroke', '#10B981'); // Emerald
        } else if (healthScore >= 50) {
            ring.setAttribute('stroke', '#E8A020'); // Amber (Kijabe Gold)
        } else {
            ring.setAttribute('stroke', '#EF4444'); // Red
        }
    }
    
    // Build diagnostics summary
    const diagText = document.getElementById('diagnostics-text');
    const diagReasons = document.getElementById('diagnostics-reasons');
    
    if (diagText && diagReasons) {
        if (healthScore === 100) {
            diagText.textContent = "All components operating normally. Zeolite PSA process running at peak efficiency.";
            diagText.className = "text-xs text-emerald-700 font-semibold leading-relaxed mb-3";
            diagReasons.innerHTML = '<div class="flex items-center gap-1.5 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span><span>System Optimal</span></div>';
        } else {
            diagText.textContent = "Attention required. Telemetry checks identified sub-optimal system parameters.";
            diagText.className = "text-xs text-amber-700 font-semibold leading-relaxed mb-3";
            if (healthScore < 50) {
                diagText.className = "text-xs text-red-700 font-semibold leading-relaxed mb-3";
            }
            
            let reasonHtml = '';
            reasons.forEach(reason => {
                reasonHtml += `
                    <div class="flex items-start gap-1.5 text-gray-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"></span>
                        <span>${reason}</span>
                    </div>
                `;
            });
            diagReasons.innerHTML = reasonHtml;
        }
    }
    
    // Update cycle labels
    const cycleIcon = document.getElementById('cycle-icon');
    const cycleLabel = document.getElementById('cycle-label');
    const cycleDesc = document.getElementById('cycle-desc');
    
    if (cycleIcon && cycleLabel && cycleDesc) {
        if (data.bed_a_status && !data.bed_b_status) {
            cycleLabel.textContent = "PSA Phase A: Adsorption";
            cycleDesc.textContent = "Zeolite Bed A is active and generating O₂. Bed B is in regenerational venting.";
            cycleIcon.className = "w-5 h-5 text-[#2B8AC6] animate-spin";
        } else if (data.bed_b_status && !data.bed_a_status) {
            cycleLabel.textContent = "PSA Phase B: Adsorption";
            cycleDesc.textContent = "Zeolite Bed B is active and generating O₂. Bed A is in regenerational venting.";
            cycleIcon.className = "w-5 h-5 text-[#2B8AC6] animate-spin";
        } else {
            cycleLabel.textContent = "PSA Cycle: Standby";
            cycleDesc.textContent = "Both PSA beds are offline. Oxygen generation is inactive.";
            cycleIcon.className = "w-5 h-5 text-gray-400";
        }
    }

    const lastUpdatedEl = document.getElementById('last-updated');
    if (lastUpdatedEl) lastUpdatedEl.textContent = 'Just now';

    const timestamp = new Date(data.created_at || new Date()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    
    if (chart && (chart.data.labels.length === 0 || chart.data.labels[chart.data.labels.length - 1] !== timestamp)) {
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
}
</script>
@endsection