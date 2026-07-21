@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<!-- CSS custom styles for SCADA mimic -->
<style>
@keyframes flow-right {
    from { background-position: 0 0; }
    to { background-position: 24px 0; }
}
#hmi-mimic-panel:fullscreen {
    width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    overflow: hidden !important;
    display: flex;
    flex-direction: column;
}
#hmi-mimic-panel:fullscreen #hmi-mimic-inner {
    width: 100% !important;
    min-width: calc(100vw - 2rem) !important;
    transform: scale(1.18);
    transform-origin: top center;
    padding-bottom: 1.5rem;
}
#hmi-mimic-inner {
    transform-origin: center center;
    transition: transform 180ms ease-out, height 180ms ease-out;
}
#mimic-resize-handle {
    height: 12px;
    cursor: row-resize;
    background: linear-gradient(90deg, rgba(148, 163, 184, 0.15), rgba(43, 138, 198, 0.35), rgba(148, 163, 184, 0.15));
    border-top: 1px solid rgba(148, 163, 184, 0.45);
    border-bottom: 1px solid rgba(148, 163, 184, 0.45);
    position: relative;
    user-select: none;
    touch-action: none;
}
#mimic-resize-handle::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 88px;
    height: 3px;
    border-radius: 9999px;
    background: #94A3B8;
    opacity: 0.85;
}
#mimic-resize-handle:hover::before,
#mimic-resize-handle.dragging::before {
    background: #2B8AC6;
}
/* === SCADA Piping & Connections (SVG) === */
.mimic-svg-container {
    position: absolute;
    inset: 0;
    width: 1440px;
    height: 420px;
    pointer-events: none;
    z-index: 0;
}
.pipe-casing {
    fill: none;
    stroke: #E2E8F0;
    stroke-width: 14px;
    stroke-linecap: round;
    stroke-linejoin: round;
    opacity: 0.95;
}
path.pipe-3d {
    fill: none;
    stroke: #94A3B8;
    stroke-width: 8px;
    stroke-linecap: round;
    stroke-linejoin: round;
    transition: stroke 350ms ease, filter 350ms ease;
}
path.pipe-3d.active {
    stroke: var(--flow-color, #10B981);
    stroke-dasharray: 8, 12;
    animation: svg-pipe-flow var(--flow-speed, 0.8s) linear infinite;
    filter: drop-shadow(0px 0px 4px var(--flow-color, #10B981));
}
@keyframes svg-pipe-flow {
    to {
        stroke-dashoffset: -20;
    }
}

/* === Bed Particle Animations === */
.o2-particle {
    position: absolute;
    width: 3.5px;
    height: 3.5px;
    background: #10B981;
    border-radius: 50%;
    filter: drop-shadow(0px 0px 1.5px #10B981);
    bottom: 5px;
    animation: o2-rise-and-fade 1.5s ease-in-out infinite;
}
@keyframes o2-rise-and-fade {
    0% {
        transform: translateY(0) scale(0.6);
        opacity: 0;
    }
    30% {
        opacity: 0.9;
    }
    70% {
        opacity: 0.9;
    }
    100% {
        transform: translateY(-65px) scale(1.1);
        opacity: 0;
    }
}
.n2-particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #3B82F6;
    border-radius: 50%;
    filter: drop-shadow(0px 0px 1px #3B82F6);
    bottom: 5px;
    animation: n2-rise-and-trap 1.8s ease-out forwards;
}
@keyframes n2-rise-and-trap {
    0% {
        transform: translateY(0) scale(0.6);
        opacity: 0;
    }
    20% {
        opacity: 0.9;
    }
    80% {
        transform: translateY(var(--trap-y, -35px));
        opacity: 0.9;
    }
    100% {
        transform: translateY(var(--trap-y, -35px));
        opacity: 0;
    }
}
.vent-particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #F97316;
    border-radius: 50%;
    filter: drop-shadow(0px 0px 1px #F97316);
    animation: vent-fall-and-fade 1.2s ease-in-out forwards;
}
@keyframes vent-fall-and-fade {
    0% {
        transform: translateY(var(--start-y, -25px)) scale(1.1);
        opacity: 0.9;
    }
    100% {
        transform: translateY(5px) scale(0.6);
        opacity: 0;
    }
}

/* === Unified Mimic Card Animations === */
.air-swirl-particle {
    position: absolute;
    width: 3.5px;
    height: 3.5px;
    background: rgba(147, 197, 253, 0.8);
    border-radius: 50%;
    animation: air-swirl 2s linear infinite;
}
@keyframes air-swirl {
    0% {
        transform: rotate(0deg) translateX(18px) rotate(0deg) scale(0.6);
        opacity: 0;
    }
    20% {
        opacity: 0.8;
    }
    80% {
        opacity: 0.8;
    }
    100% {
        transform: rotate(360deg) translateX(18px) rotate(-360deg) scale(1);
        opacity: 0;
    }
}
.water-drop-particle {
    position: absolute;
    width: 2.5px;
    height: 5px;
    background: #3B82F6;
    border-radius: 30% 30% 50% 50%;
    opacity: 0.8;
   animation: water-fall 1.4s linear infinite;
@keyframes water-fall {
    0% {
        transform: translateY(-25px);
        opacity: 0;
    }
    15% {
        opacity: 0.8;
    }
    85% {
        opacity: 0.8;
    }
    100% {
        transform: translateY(35px);
        opacity: 0;
    }
}
.air-bounce-particle {
    position: absolute;
    width: 3.5px;
    height: 3.5px;
    background: #60A5FA;
    border-radius: 50%;
    opacity: 0.6;
    animation: air-bounce 3s ease-in-out infinite;
}
@keyframes air-bounce {
    0% {
        transform: translate(0, 0) scale(0.7);
        opacity: 0;
    }
    10% { opacity: 0.7; }
    25% { transform: translate(15px, -20px); }
    50% { transform: translate(-10px, -45px); }
    75% { transform: translate(10px, -30px); }
    90% { opacity: 0.7; }
    100% {
        transform: translate(0, 0) scale(0.7);
        opacity: 0;
    }
}
.o2-bounce-particle {
    position: absolute;
    width: 3.5px;
    height: 3.5px;
    background: #34D399;
    border-radius: 50%;
    filter: drop-shadow(0 0 1px #34D399);
    opacity: 0.7;
    animation: o2-bounce 3s ease-in-out infinite;
}
@keyframes o2-bounce {
    0% {
        transform: translate(0, 0) scale(0.7);
        opacity: 0;
    }
    10% { opacity: 0.8; }
    25% { transform: translate(-15px, -25px); }
    50% { transform: translate(12px, -50px); }
    75% { transform: translate(-12px, -35px); }
    90% { opacity: 0.8; }
    100% {
        transform: translate(0, 0) scale(0.7);
        opacity: 0;
    }
}
.booster-fast-particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #10B981;
    border-radius: 50%;
    filter: drop-shadow(0 0 1.5px #10B981);
    left: 5px;
    animation: booster-fast 1s linear infinite;
}
@keyframes booster-fast {
    0% {
        transform: translateX(0);
        opacity: 0;
    }
    20% {
        opacity: 0.8;
    }
    80% {
        opacity: 0.8;
    }
    100% {
        transform: translateX(110px);
        opacity: 0;
    }
}
.pipeline-flow-particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #059669;
    border-radius: 50%;
    filter: drop-shadow(0 0 1.5px #059669);
    left: 5px;
    animation: pipeline-flow 1.5s linear infinite;
}
@keyframes pipeline-flow {
    0% {
        transform: translateX(0);
        opacity: 0;
    }
    20% {
        opacity: 0.8;
    }
    80% {
        opacity: 0.8;
    }
    100% {
        transform: translateX(110px);
        opacity: 0;
    }
}

/* === 3D Vessel Shading (tanks) === */
.vessel-3d {
    position: relative;
    background: radial-gradient(120% 100% at 30% 20%, #FFFFFF 0%, #F1F5F9 35%, #DDE3EE 70%, #C4CDDA 100%);
    box-shadow:
        inset -6px 0 10px rgba(0,0,0,0.08),
        inset 4px 4px 8px rgba(255,255,255,0.9),
        0 4px 10px rgba(0,0,0,0.06);
}
.vessel-3d.pressurized {
    animation: vessel-breathe 2.6s ease-in-out infinite;
}
@keyframes vessel-breathe {
    0%, 100% { box-shadow: inset -6px 0 10px rgba(0,0,0,0.08), inset 4px 4px 8px rgba(255,255,255,0.9), 0 0 0 0 rgba(16,185,129,0.20); }
    50%      { box-shadow: inset -6px 0 10px rgba(0,0,0,0.08), inset 4px 4px 8px rgba(255,255,255,0.9), 0 0 0 7px rgba(16,185,129,0); }
}

.valve-3d {
    background: radial-gradient(circle at 35% 30%, #FFFFFF, #94A3B8 70%, #64748B 100%);
    box-shadow: inset -1px -1px 2px rgba(0,0,0,0.3), inset 1px 1px 1px rgba(255,255,255,0.8);
    transition: background 400ms ease, box-shadow 400ms ease;
}
.valve-3d.active {
    background: radial-gradient(circle at 35% 30%, #6EE7B7, #10B981 70%, #059669 100%);
    box-shadow: inset -1px -1px 2px rgba(0,0,0,0.2), 0 0 6px rgba(16,185,129,0.7);
}
.valve-3d.fault {
    background: radial-gradient(circle at 35% 30%, #FCA5A5, #EF4444 70%, #B91C1C 100%);
    box-shadow: inset -1px -1px 2px rgba(0,0,0,0.2), 0 0 8px rgba(239,68,68,0.8);
}

.glow-adsorbing {
    box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.2), 0 8px 10px -6px rgba(16, 185, 129, 0.2);
    border-color: #10B981 !important;
}
.glow-regenerating {
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.2), 0 8px 10px -6px rgba(59, 130, 246, 0.2);
    border-color: #3B82F6 !important;
}
.glow-fault {
    box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3), 0 8px 10px -6px rgba(239, 68, 68, 0.3);
    border-color: #EF4444 !important;
    animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
.led-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Nitrogen Venting Particles */
@keyframes vent-float {
    0% { transform: translateY(0) scale(0.5); opacity: 0; }
    30% { opacity: 0.9; }
    100% { transform: translateY(-24px) scale(1.6); opacity: 0; }
}
.vent-puff-1 {
    animation: vent-float 1.4s infinite ease-out;
}
.vent-puff-2 {
    animation: vent-float 1.4s infinite ease-out 0.4s;
}
.vent-puff-3 {
    animation: vent-float 1.4s infinite ease-out 0.8s;
}

/* Data Transmission Flow Packets */
@keyframes flow-packet {
    0% { left: 0%; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { left: 100%; opacity: 0; }
}
.flow-packet-dot {
    animation: flow-packet 2s infinite linear;
}
#mimic-resize-handle {
    height: 12px;
    cursor: row-resize;
    background: linear-gradient(90deg, rgba(148, 163, 184, 0.15), rgba(43, 138, 198, 0.35), rgba(148, 163, 184, 0.15));
    border-top: 1px solid rgba(148, 163, 184, 0.45);
    border-bottom: 1px solid rgba(148, 163, 184, 0.45);
    position: relative;
    user-select: none;
    touch-action: none;
}
#mimic-resize-handle::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 88px;
    height: 3px;
    border-radius: 9999px;
    background: #94A3B8;
    opacity: 0.85;
}
#mimic-resize-handle:hover::before,
#mimic-resize-handle.dragging::before {
    background: #2B8AC6;
}
</style>

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
    <div class="flex items-center gap-2 flex-wrap">
        <button id="toggle-mimic-btn" onclick="toggleMimicPanel()" class="flex items-center gap-2 px-4 py-2 bg-[#2B8AC6] hover:bg-[#1C6CA0] text-white text-xs font-bold rounded-lg shadow-sm transition duration-200">
            <i data-lucide="layout-template" class="w-4 h-4"></i>
            <span>Open HMI Mimic</span>
        </button>
    </div>
</div>

<!-- Collapsible SCADA Mimic Panel -->
<div id="hmi-mimic-panel" class="mb-8 bg-white border border-[#DDE3EE] rounded-xl shadow-sm overflow-auto resize-x transition-all duration-500 ease-in-out max-h-0 w-full min-w-[100%]">
    <div class="p-5 border-b border-[#DDE3EE] flex justify-between items-center bg-[#F8FAFC] gap-3">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h2 class="text-xs font-bold uppercase tracking-widest text-[#1B3A6B]">Interactive SCADA Process Mimic (Digital Twin)</h2>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] text-[#6B7A90] font-semibold italic">Hover/click components on the physical PSA skid to inspect telemetry</span>
            <div class="flex items-center gap-1.5">
                <button id="mimic-zoom-out-btn" onclick="zoomMimic(-0.1)" class="flex items-center justify-center w-8 h-8 bg-slate-700 hover:bg-slate-600 text-white text-lg font-bold rounded-lg shadow-sm transition duration-200" title="Zoom out">−</button>
                <button id="mimic-zoom-in-btn" onclick="zoomMimic(0.1)" class="flex items-center justify-center w-8 h-8 bg-slate-700 hover:bg-slate-600 text-white text-lg font-bold rounded-lg shadow-sm transition duration-200" title="Zoom in">+</button>
                <button id="mimic-fullscreen-btn" onclick="toggleMimicFullscreen()" class="flex items-center gap-2 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg shadow-sm transition duration-200">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                    <span>Full Screen</span>
                </button>
            </div>
        </div>
    </div>
    <div id="mimic-canvas-shell" class="p-8 overflow-x-auto bg-[#FAFBFD]">
        <div id="hmi-mimic-inner" style="height: 420px; width: 1580px;" class="min-w-[1580px] relative py-6 mx-auto select-none">
            <div id="mimic-stage-popover" class="pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 z-40 max-w-[90%] rounded-2xl border border-[#2B8AC6]/25 bg-white/90 backdrop-blur-md shadow-lg px-4 py-2 text-center">
                <div id="mimic-stage-primary" class="text-[10px] font-extrabold uppercase tracking-[0.26em] text-[#1B3A6B]">Air entering</div>
                <div id="mimic-stage-detail" class="text-[9px] text-[#6B7A90] font-semibold mt-1 max-w-[760px] leading-relaxed">Ambient air is being drawn into the intake line and routed toward the compressor.</div>
            </div>
            
            <!-- SVG Pipeline Casing & Fluid Flow paths -->
            <svg class="mimic-svg-container" style="width: 1580px; height: 420px;">
                <path d="M 164 212 L 214 212" class="pipe-casing" />
                <path d="M 358 212 L 408 212" class="pipe-casing" />
                <path d="M 552 212 L 562 212" class="pipe-casing" />
                <path d="M 562 212 L 562 132" class="pipe-casing" />
                <path d="M 562 212 L 562 292" class="pipe-casing" />
                <path d="M 562 132 L 622 132" class="pipe-casing" />
                <path d="M 562 292 L 622 292" class="pipe-casing" />
                <path d="M 766 132 L 826 132" class="pipe-casing" />
                <path d="M 766 292 L 826 292" class="pipe-casing" />
                <path d="M 826 132 L 826 212" class="pipe-casing" />
                <path d="M 826 292 L 826 212" class="pipe-casing" />
                <path d="M 826 212 L 836 212" class="pipe-casing" />
                <path d="M 980 212 L 1030 212" class="pipe-casing" />
                <path d="M 1174 212 L 1224 212" class="pipe-casing" />
                <path d="M 1352 212 L 1402 212" class="pipe-casing" />
                <path d="M 694 196 L 694 228" class="pipe-casing" />
 
                   <!-- Active pipelines (colored pulsing dashed lines when active) -->
                <path id="pipe-intake-compressor" d="M 164 212 L 214 212" class="pipe-3d" />
                <path id="pipe-compressor-dryer" d="M 358 212 L 408 212" class="pipe-3d" />
                <path id="pipe-manifold-psa-in" d="M 552 212 L 562 212" class="pipe-3d" />
                <path id="pipe-manifold-psa-vert-a" d="M 562 212 L 562 132" class="pipe-3d" />
                <path id="pipe-manifold-psa-vert-b" d="M 562 212 L 562 292" class="pipe-3d" />
                <path id="pipe-manifold-psa-a" d="M 562 132 L 622 132" class="pipe-3d" />
                <path id="pipe-manifold-psa-b" d="M 562 292 L 622 292" class="pipe-3d" />
                <path id="pipe-psa-o2-a" d="M 766 132 L 826 132" class="pipe-3d" />
                <path id="pipe-psa-o2-b" d="M 766 292 L 826 292" class="pipe-3d" />
                <path id="pipe-psa-o2-vert-a" d="M 826 132 L 826 212" class="pipe-3d" />
                <path id="pipe-psa-o2-vert-b" d="M 826 292 L 826 212" class="pipe-3d" />
                <path id="pipe-psa-o2-out" d="M 826 212 L 836 212" class="pipe-3d" />
                <path id="pipe-o2-booster" d="M 980 212 L 1030 212" class="pipe-3d" />
                <path id="pipe-booster-manifold" d="M 1174 212 L 1224 212" class="pipe-3d" />
                <path id="pipe-o2-manifold-hospital" d="M 1352 212 L 1402 212" class="pipe-3d" />
                <path id="pipe-psa-eq" d="M 694 196 L 694 228" class="pipe-3d" />
            </svg>

            <!-- Column 1: Air Compressor -->
            <div onclick="openComponentDrawer('compressor')" style="left: 20px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-compressor-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex items-center justify-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <div class="relative w-full h-[85%] flex items-center justify-center">
                        <img src="/images/mimic/compressor.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="compressor-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span id="mimic-compressor-badge" class="absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase tracking-wider bg-slate-500 text-white shadow-sm">Standby</span>
                    <div class="absolute bottom-2 left-2 w-6 h-6 rounded-full bg-slate-100/90 border border-slate-200 shadow-sm flex items-center justify-center z-10" title="Compressor Cooling Fan">
                        <i data-lucide="fan" id="compressor-fan-icon" class="w-4 h-4 text-slate-400"></i>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Compressor C-101</span>
                <span id="mimic-compressor-temp" class="text-[10px] font-semibold text-[#6B7A90] font-mono">0.0°C</span>
            </div>
 
            <!-- Column 2: Refrigerant Air Dryer -->
            <div onclick="openComponentDrawer('dryer')" style="left: 214px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-dryer-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <div class="w-full flex justify-between items-start">
                        <span id="mimic-dryer-badge" class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm">Standby</span>
                    </div>
                    <div class="relative w-full h-[65%] flex items-center justify-center">
                        <img src="/images/mimic/dryer.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="dryer-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span id="mimic-dryer-temp" class="text-[10px] font-bold text-[#2B8AC6] font-mono">3.0°C</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Air Dryer D-102</span>
                <span class="text-[10px] font-semibold text-[#6B7A90] font-mono">Dewpoint 3°C</span>
            </div>
 
            <!-- Column 3: Air Receiver Tank -->
            <div onclick="openComponentDrawer('air_tank')" style="left: 408px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-air_tank-card" class="vessel-3d relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <span class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-200 text-slate-700 shadow-sm self-start">Buffer</span>
                    <div class="relative w-full h-[65%] flex items-center justify-center">
                        <img src="/images/mimic/air_tank.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="air-tank-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span id="mimic-air_tank-press" class="text-[10px] font-bold text-[#2B8AC6] font-mono">0.00 bar</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Air Receiver V-103</span>
                <span class="text-[10px] font-semibold text-[#6B7A90] font-mono">Wet Storage</span>
            </div>
 
            <!-- Column 4: PSA Beds A & B (Stacked Vertically) -->
            <div style="left: 622px; top: 60px;" class="absolute flex flex-col gap-4 items-center justify-center select-none w-36 z-10">
                <!-- PSA Bed A -->
                <div onclick="openComponentDrawer('psa_towers')" id="mimic-psa-a-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center hover:border-[#2B8AC6] hover:bg-gray-50 transition duration-200 shadow-sm cursor-pointer">
                    <div class="w-full flex justify-between items-start">
                        <span id="mimic-psa-a-badge" class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm">Standby</span>
                    </div>
                    
                    <div class="relative w-full h-[60%] flex items-center justify-center">
                        <img src="/images/mimic/psa_column.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="psa-a-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    
                    <span class="text-[9px] font-bold text-[#1A2A3A]">PSA Bed A</span>
                    
                    <!-- Exhaust vent and rising puffs -->
                    <div id="exhaust-vent-a" class="absolute top-[-10px] left-[40%] w-6 h-6 flex flex-col items-center pointer-events-none z-20">
                        <div class="w-1.5 h-3 bg-slate-400 border border-slate-500 rounded-t shadow-sm"></div>
                        <div class="relative w-full h-0">
                            <span id="vent-puff-a1" class="absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0"></span>
                            <span id="vent-puff-a2" class="absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0"></span>
                            <span id="vent-puff-a3" class="absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0"></span>
                        </div>
                    </div>
                </div>
 
                <!-- PSA Bed B -->
                <div onclick="openComponentDrawer('psa_towers')" id="mimic-psa-b-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center hover:border-[#2B8AC6] hover:bg-gray-50 transition duration-200 shadow-sm cursor-pointer">
                    <div class="w-full flex justify-between items-start">
                        <span id="mimic-psa-b-badge" class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm">Standby</span>
                    </div>
                    
                    <div class="relative w-full h-[60%] flex items-center justify-center">
                        <img src="/images/mimic/psa_column.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="psa-b-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    
                    <span class="text-[9px] font-bold text-[#1A2A3A]">PSA Bed B</span>
                    
                    <!-- Exhaust vent and rising puffs -->
                    <div id="exhaust-vent-b" class="absolute top-[-10px] left-[40%] w-6 h-6 flex flex-col items-center pointer-events-none z-20">
                        <div class="w-1.5 h-3 bg-slate-400 border border-slate-500 rounded-t shadow-sm"></div>
                        <div class="relative w-full h-0">
                            <span id="vent-puff-b1" class="absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0"></span>
                            <span id="vent-puff-b2" class="absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0"></span>
                            <span id="vent-puff-b3" class="absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Column 5: Oxygen Receiver Tank -->
            <div onclick="openComponentDrawer('o2_tank')" style="left: 836px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-o2_tank-card" class="vessel-3d relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <span id="mimic-o2_tank-purity" class="relative z-30 self-start px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-emerald-500 text-white shadow-sm">0.0% O₂</span>
                    <div class="relative w-full h-[65%] flex items-center justify-center">
                        <img src="/images/mimic/o2_tank.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="o2-tank-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span id="mimic-o2_tank-press" class="text-[10px] font-bold text-[#10B981] font-mono">0.00 bar</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Oxygen Receiver V-104</span>
                <span class="text-[10px] font-semibold text-[#6B7A90] font-mono">Product Storage</span>
            </div>
 
            <!-- Column 6: Oxygen Booster pump -->
            <div onclick="openComponentDrawer('booster')" style="left: 1030px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-booster-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <span id="mimic-booster-badge" class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm self-start">Standby</span>
                    <div class="relative w-full h-[65%] flex items-center justify-center">
                        <img src="/images/mimic/booster.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="booster-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span id="mimic-booster-state" class="text-[10px] font-bold text-[#10B981] font-mono">Standby</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">O₂ Booster C-105</span>
                <span class="text-[10px] font-semibold text-[#6B7A90] font-mono">High Pressure</span>
            </div>
 
            <!-- Column 7: Valve Manifold -->
            <div onclick="openComponentDrawer('psa_towers')" style="left: 1224px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-32 z-10">
                <div id="mimic-manifold-block-card" class="relative w-32 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-3 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <span class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-200 text-slate-700 shadow-sm self-start">PLC Valves</span>
                    <div class="grid grid-cols-2 gap-3 my-1">
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white shadow led-pulse" title="Ward A Delivery Solenoid"></span>
                            <span class="text-[6.5px] font-bold text-[#6B7A90] mt-0.5">WARD A</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-300 border-2 border-white shadow" title="Ward B Delivery Solenoid"></span>
                            <span class="text-[6.5px] font-bold text-[#6B7A90] mt-0.5">WARD B</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-300 border-2 border-white shadow" title="Cylinder Backfill Solenoid"></span>
                            <span class="text-[6.5px] font-bold text-[#6B7A90] mt-0.5">CYL FILL</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-300 border-2 border-white shadow" title="Manifold Purge Vent Solenoid"></span>
                            <span class="text-[6.5px] font-bold text-[#6B7A90] mt-0.5">PURGE</span>
                        </div>
                    </div>
                    <span class="text-[8px] font-extrabold text-[#1B3A6B] uppercase tracking-wider">Manifold YV-106</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Valve Manifold</span>
                <span id="mimic-psa-state" class="text-[10px] font-semibold text-[#6B7A90] font-mono">Bed A / Bed B</span>
            </div>
 
            <!-- Column 8: Hospital Pipeline -->
            <div onclick="openComponentDrawer('manifold')" style="left: 1402px; top: 140px;" class="absolute flex flex-col items-center gap-2 cursor-pointer group select-none w-36 z-10">
                <div id="mimic-manifold-card" class="relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center group-hover:border-[#2B8AC6] group-hover:bg-gray-50 transition duration-200 shadow-sm">
                    <span id="mimic-manifold-badge" class="relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-emerald-500 text-white shadow-sm self-start">Holding</span>
                    <div class="relative w-full h-[65%] flex items-center justify-center">
                        <img src="/images/mimic/hospital_pipeline.png" class="max-w-full max-h-full object-contain rounded-xl z-10">
                        <div id="hospital-particles" class="absolute inset-0 z-20 pointer-events-none overflow-hidden rounded-xl"></div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-600 font-mono">Active Supply</span>
                </div>
                <span class="text-[10px] font-bold text-[#1A2A3A] tracking-wide uppercase">Hospital Pipeline</span>
            </div>
 
            <!-- Valves absolutely placed over the SVG pipes -->
            <div id="valve-intake" class="valve-3d w-6 h-3.5 absolute left-[177px] top-[205px] rounded border border-white shadow z-20 cursor-pointer" title="Intake Solenoid Valve YV-101"></div>
            <div id="valve-compressor" class="valve-3d w-6 h-3.5 absolute left-[371px] top-[205px] rounded border border-white shadow z-20 cursor-pointer" title="Dryer Isolation Valve YV-102"></div>
            <div id="valve-psa-a" class="valve-3d w-6 h-3.5 absolute left-[580px] top-[125px] rounded border border-white shadow z-20 cursor-pointer" title="Bed A Feed Valve YV-104A"></div>
            <div id="valve-psa-b" class="valve-3d w-6 h-3.5 absolute left-[580px] top-[285px] rounded border border-white shadow z-20 cursor-pointer" title="Bed B Feed Valve YV-104B"></div>
            <div id="valve-vent-a" class="valve-3d w-6 h-3.5 absolute left-[784px] top-[125px] rounded border border-white shadow z-20 cursor-pointer" title="Bed A Vent Valve"></div>
            <div id="valve-vent-b" class="valve-3d w-6 h-3.5 absolute left-[784px] top-[285px] rounded border border-white shadow z-20 cursor-pointer" title="Bed B Vent Valve"></div>
            <div id="valve-o2-tank" class="valve-3d w-6 h-3.5 absolute left-[993px] top-[205px] rounded border border-white shadow z-20 cursor-pointer" title="Outlet Solenoid Valve YV-105"></div>
            <div id="valve-dryer" class="valve-3d w-6 h-3.5 absolute left-[1187px] top-[205px] rounded border border-white shadow z-20 cursor-pointer" title="Crossover Solenoid Valve YV-106A"></div>
            <div id="valve-booster" class="valve-3d w-6 h-3.5 absolute left-[1365px] top-[205px] rounded border border-white shadow z-20 cursor-pointer" title="Hospital Feed Solenoid Valve YV-107"></div>
            <div id="valve-eq" class="valve-3d w-3.5 h-4 absolute left-[687px] top-[204px] rounded border border-white shadow z-20 cursor-pointer" title="Equalization Valve YV-105C"></div>
        </div>
    </div>
    <div id="mimic-resize-handle" title="Drag to enlarge the HMI canvas" aria-label="Resize HMI mimic canvas" role="separator"></div>

    <!-- Step-by-Step PSA Process Explanation -->
    <div class="p-6 border-t border-[#DDE3EE] bg-[#FAFBFD]">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#1B3A6B] mb-4 flex items-center gap-1.5">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>Interactive Process Steps</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Step 1 -->
            <div onclick="openComponentDrawer('compressor')" class="bg-white p-3 border border-[#DDE3EE] rounded-xl shadow-sm flex gap-3 items-start cursor-pointer hover:border-[#2B8AC6] hover:bg-slate-50/50 transition duration-150 group">
                <span class="w-6 h-6 rounded-full bg-[#2B8AC6]/10 text-[#2B8AC6] flex items-center justify-center text-xs font-bold flex-shrink-0 group-hover:bg-[#2B8AC6] group-hover:text-white transition duration-150">1</span>
                <div>
                    <h4 class="text-xs font-bold text-[#1B3A6B] uppercase">Compression</h4>
                    <p class="text-[10px] text-[#6B7A90] mt-1">Compressor C-101 draws in air and compresses it to 7.0 bar to feed downstream equipment.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div onclick="openComponentDrawer('dryer')" class="bg-white p-3 border border-[#DDE3EE] rounded-xl shadow-sm flex gap-3 items-start cursor-pointer hover:border-[#2B8AC6] hover:bg-slate-50/50 transition duration-150 group">
                <span class="w-6 h-6 rounded-full bg-[#2B8AC6]/10 text-[#2B8AC6] flex items-center justify-center text-xs font-bold flex-shrink-0 group-hover:bg-[#2B8AC6] group-hover:text-white transition duration-150">2</span>
                <div>
                    <h4 class="text-xs font-bold text-[#1B3A6B] uppercase">Condensation & Dewpoint</h4>
                    <p class="text-[10px] text-[#6B7A90] mt-1">Refrigerant Dryer D-102 cools air to 3.0°C to condense and separate moisture.</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div onclick="openComponentDrawer('psa_towers')" class="bg-white p-3 border border-[#DDE3EE] rounded-xl shadow-sm flex gap-3 items-start cursor-pointer hover:border-[#2B8AC6] hover:bg-slate-50/50 transition duration-150 group">
                <span class="w-6 h-6 rounded-full bg-[#2B8AC6]/10 text-[#2B8AC6] flex items-center justify-center text-xs font-bold flex-shrink-0 group-hover:bg-[#2B8AC6] group-hover:text-white transition duration-150">3</span>
                <div>
                    <h4 class="text-xs font-bold text-[#1B3A6B] uppercase">Zeolite Adsorption</h4>
                    <p class="text-[10px] text-[#6B7A90] mt-1">PSA Columns adsorb nitrogen under pressure, leaving pure oxygen (93%+) to flow to V-104.</p>
                </div>
            </div>
            <!-- Step 4 -->
            <div onclick="openComponentDrawer('manifold')" class="bg-white p-3 border border-[#DDE3EE] rounded-xl shadow-sm flex gap-3 items-start cursor-pointer hover:border-[#2B8AC6] hover:bg-slate-50/50 transition duration-150 group">
                <span class="w-6 h-6 rounded-full bg-[#2B8AC6]/10 text-[#2B8AC6] flex items-center justify-center text-xs font-bold flex-shrink-0 group-hover:bg-[#2B8AC6] group-hover:text-white transition duration-150">4</span>
                <div>
                    <h4 class="text-xs font-bold text-[#1B3A6B] uppercase">High Pressure Cylinder Fill</h4>
                    <p class="text-[10px] text-[#6B7A90] mt-1">Booster C-105 elevates O₂ pressure up to 150 bar to fill medical gas cylinders.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- OPMAS Data Transmission Architecture Layer -->
    <div class="p-6 border-t border-[#DDE3EE] bg-[#F8FAFC]">
        <h3 class="text-xs font-bold uppercase tracking-widest text-[#1B3A6B] mb-4 flex items-center gap-1.5">
            <i data-lucide="network" class="w-4 h-4"></i>
            <span>Telemetry Pipeline & Data Transmission Architecture</span>
        </h3>
        <div class="flex flex-col md:flex-row items-center justify-between gap-2 py-4 px-2 bg-white border border-[#DDE3EE] rounded-2xl shadow-sm overflow-x-auto min-w-[1200px]">
            <!-- Block 1 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="gauge" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">Sensor Readings</span>
                <span class="text-[8px] text-[#6B7A90]">Pressure / Temp / Purity</span>
            </div>
            
            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot"></div>
            </div>

            <!-- Block 2 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="cpu" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">TM3A14 Module</span>
                <span class="text-[8px] text-[#6B7A90]">Analog ➔ Digital conversion</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 0.25s"></div>
            </div>

            <!-- Block 3 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="binary" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">Schneider M221 PLC</span>
                <span class="text-[8px] text-[#6B7A90]">Modbus Telemetry Host</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 0.5s"></div>
            </div>

            <!-- Block 4 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="globe" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">Modbus TCP Link</span>
                <span class="text-[8px] text-[#6B7A90]">Port 5020 Transmission</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 0.75s"></div>
            </div>

            <!-- Block 5 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="terminal" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">Python Service</span>
                <span class="text-[8px] text-[#6B7A90]">Modbus Client Poller</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 1.0s"></div>
            </div>

            <!-- Block 6 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="database" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">MySQL Database</span>
                <span class="text-[8px] text-[#6B7A90]">Persistent Storage</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 1.25s"></div>
            </div>

            <!-- Block 7 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="server" class="w-6 h-6 text-[#2B8AC6] mb-1"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">Laravel Backend API</span>
                <span class="text-[8px] text-[#6B7A90]">JSON Controller Poller</span>
            </div>

            <!-- Connection Line -->
            <div class="flex-grow h-1.5 bg-slate-100 rounded relative overflow-hidden min-w-[30px]">
                <div class="absolute inset-y-0 w-3 bg-[#2B8AC6] rounded-full flow-packet-dot" style="animation-delay: 1.5s"></div>
            </div>

            <!-- Block 8 -->
            <div class="flex flex-col items-center p-2 rounded-xl w-32 text-center">
                <i data-lucide="layout-dashboard" class="w-6 h-6 text-emerald-500 mb-1 animate-pulse"></i>
                <span class="text-[9px] font-bold text-[#1A2A3A]">OPMAS HMI Dashboard</span>
                <span class="text-[8px] text-emerald-600 font-bold">Live Visual Sync</span>
            </div>
        </div>
    </div>
</div>

<!-- Component Info Drawer -->
<div id="component-drawer" class="fixed right-0 top-0 h-full w-96 bg-white border-l border-[#DDE3EE] shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
    <div class="p-6 border-b border-[#DDE3EE] flex items-center justify-between bg-[#F8FAFC]">
        <h3 id="drawer-title" class="text-sm font-bold text-[#1B3A6B] uppercase tracking-wider">Component Info</h3>
        <button onclick="closeComponentDrawer()" class="p-1 rounded-lg hover:bg-gray-100 text-[#6B7A90] hover:text-[#1A2A3A] transition">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    <div class="p-6 flex-grow overflow-y-auto space-y-6 text-[#1A2A3A]">
        <!-- Live Status Section -->
        <div>
            <span class="text-[9px] font-bold uppercase tracking-widest text-[#6B7A90] block mb-1">Live Status</span>
            <div class="flex items-center gap-2">
                <span id="drawer-status-led" class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span id="drawer-status-text" class="text-sm font-semibold text-[#1A2A3A]">Running</span>
            </div>
        </div>

        <!-- Spec Description Section -->
        <div>
            <span class="text-[9px] font-bold uppercase tracking-widest text-[#6B7A90] block mb-1">Process Function</span>
            <p id="drawer-description" class="text-xs leading-relaxed text-[#6B7A90]">
                Compresses ambient air to 7-8 bar to supply feed gas for the Zeolite PSA columns.
            </p>
        </div>

        <!-- Technical Telemetry Table -->
        <div>
            <span class="text-[9px] font-bold uppercase tracking-widest text-[#6B7A90] block mb-1">Process Values</span>
            <div class="bg-[#F8FAFC] border border-[#DDE3EE] rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-[#6B7A90] font-bold border-b border-[#DDE3EE]">
                            <th class="p-3">Parameter</th>
                            <th class="p-3 text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody id="drawer-telemetry-body" class="divide-y divide-[#DDE3EE] font-mono text-[#1A2A3A]">
                        <!-- Filled dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Operational Limits -->
        <div>
            <span class="text-[9px] font-bold uppercase tracking-widest text-[#6B7A90] block mb-1">Operational Limits</span>
            <ul id="drawer-limits-list" class="space-y-1.5 text-xs list-disc pl-4 text-[#6B7A90]">
                <!-- Filled dynamically -->
            </ul>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
let chart;
// Store latest telemetry globally for the detail drawer
window.latestTelemetryData = null;
window.latestTelemetryOffline = false;

// Predefine Component Specifications for HMI Drawer
const componentSpecs = {
    intake: {
        title: "Air Intake & Filter",
        description: "Standard atmospheric suction intake. Features a multi-stage particulate filter (down to 5 microns) to extract dust, soot, and impurities before compression, protecting downstream machinery.",
        limits: [
            "Normal suction pressure: 1.013 bar (Atmospheric)",
            "Filter maintenance interval: Every 2,000 running hours",
            "Max particle intake threshold: PM 2.5"
        ]
    },
    compressor: {
        title: "Air Compressor C-101",
        description: "Primary Rotary Screw Air Compressor. Compresses atmospheric air up to 7-8 bar to overcome pressure drops across filters and zeolite beds, ensuring high velocity gas separation.",
        limits: [
            "Normal Operating Pressure: 5.5 - 7.5 bar",
            "Shutdown Temperature limit: 95.0°C",
            "Warning Temperature limit: 80.0°C"
        ]
    },
    dryer: {
        title: "Refrigerant Air Dryer D-102",
        description: "Refrigerated condenser dryer. Lowers the air dewpoint to 3.0°C, condensing water vapor out of the stream. Dry air is mandatory to prevent moisture from destroying the zeolite molecular sieves.",
        limits: [
            "Operating Condenser Temp: 2.0°C - 5.0°C",
            "Maximum inlet air temperature: 45°C",
            "Water Separator auto-drain frequency: 30s cycle"
        ]
    },
    air_tank: {
        title: "Air Receiver Tank V-103",
        description: "1000-Liter vertical pressure buffer vessel. Absorbs pulsation spikes from the compressor and provides an instantaneous high-volume surge capacity during zeolite bed pressurization cycles.",
        limits: [
            "Maximum Vessel Design Pressure: 10.0 bar",
            "Automatic safety relief valve setpoint: 8.5 bar",
            "Manual bottom blowdown frequency: Daily"
        ]
    },
    psa_towers: {
        title: "PSA Zeolite Sieve Columns",
        description: "Dual adsorption chambers packed with Zeolite Molecular Sieve (ZMS). Chamber A adsorbs nitrogen under pressure while releasing oxygen. Concurrently, Chamber B vents to release trapped nitrogen, regenerating the bed.",
        limits: [
            "Adsorption cycle duration: 45 - 60 seconds",
            "Venting/Purging cycle duration: 40 - 55 seconds",
            "Zeolite lifespan estimate: 8 - 10 years"
        ]
    },
    o2_tank: {
        title: "O₂ Receiver Tank V-104",
        description: "Oxygen product buffer vessel. Normalizes flow fluctuations during adsorption transitions. Maintains a stable pressure head of pure oxygen (93% - 95%) ready for hospital distribution or cylinder boosting.",
        limits: [
            "Desired product purity: 93.0% - 95.5%",
            "Minimum acceptable purity threshold: 90.0%",
            "Pressure drop alert trigger: < 4.0 bar"
        ]
    },
    booster: {
        title: "O₂ Booster Compressor C-105",
        description: "High-pressure oil-free piston gas booster. Elevates oxygen gas pressure from 4-5 bar up to a maximum of 150 bar to facilitate cylinder filling operations.",
        limits: [
            "Maximum discharge pressure: 150 bar (cylinder fill)",
            "Safety interlock low-suction alarm: < 2.0 bar",
            "Cooling fan auto-start temp: > 45°C"
        ]
    },
    manifold: {
        title: "Cylinder Filling Manifold M-106",
        description: "High pressure cylinder filling rack. Connects up to 4 high-pressure medical oxygen cylinders (typically J-type, 150 bar) in parallel, equipped with safety pigtails and non-return check valves.",
        limits: [
            "Full cylinder pressure rating: 150 bar @ 20°C",
            "Flexible pigtail test pressure: 250 bar",
            "Purge valve vent setpoint: 160 bar"
        ]
    }
};

let mimicResizeState = null;
let mimicZoomLevel = 1;
const mimicBaseHeight = 420;
const mimicBaseWidth = 1320;
const mimicMaxZoom = 4;

function clamp(value, min, max = Number.POSITIVE_INFINITY) {
    return Math.min(Math.max(value, min), max);
}

function setMimicCanvasZoom(scale) {
    const inner = document.getElementById('hmi-mimic-inner');
    if (!inner) return;

    const newScale = clamp(Number(scale) || 1, 1, mimicMaxZoom);
    mimicZoomLevel = newScale;
    inner.style.height = (mimicBaseHeight * newScale) + 'px';
    inner.style.transform = 'scale(' + newScale + ')';
    inner.style.minWidth = (mimicBaseWidth * newScale) + 'px';
}

function zoomMimic(step) {
    setMimicCanvasZoom(mimicZoomLevel + step);
}

function startMimicResizeDrag(event) {
    const inner = document.getElementById('hmi-mimic-inner');
    const handle = event.currentTarget;
    if (!inner || !handle) return;

    event.preventDefault();
    mimicResizeState = {
        startY: event.clientY,
        startScale: mimicZoomLevel
    };
    handle.classList.add('dragging');
}

function stopMimicResizeDrag() {
    const handle = document.getElementById('mimic-resize-handle');
    if (handle) {
        handle.classList.remove('dragging');
    }
    mimicResizeState = null;
}

function toggleMimicPanel() {
    const panel = document.getElementById('hmi-mimic-panel');
    const btn = document.getElementById('toggle-mimic-btn');
    const btnText = btn.querySelector('span');
    
    if (panel.classList.contains('max-h-0')) {
        panel.classList.remove('max-h-0');
        panel.classList.add('max-h-[1000px]');
        btnText.textContent = "Close HMI Mimic";
        btn.className = "flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg shadow-sm transition duration-200";
    } else {
        panel.classList.remove('max-h-[1000px]');
        panel.classList.add('max-h-0');
        btnText.textContent = "Open HMI Mimic";
        btn.className = "flex items-center gap-2 px-4 py-2 bg-[#2B8AC6] hover:bg-[#1C6CA0] text-white text-xs font-bold rounded-lg shadow-sm transition duration-200";
    }
}

function toggleMimicFullscreen() {
    const panel = document.getElementById('hmi-mimic-panel');
    if (!panel) return;

    if (!document.fullscreenElement) {
        panel.requestFullscreen?.();
    } else {
        document.exitFullscreen?.();
    }
}

function syncMimicFullscreenState() {
    const panel = document.getElementById('hmi-mimic-panel');
    const inner = document.getElementById('hmi-mimic-inner');
    const btn = document.getElementById('mimic-fullscreen-btn');
    const btnText = btn?.querySelector('span');

    if (!panel || !inner) return;

    const isFullscreen = document.fullscreenElement === panel;

    if (isFullscreen) {
        panel.style.width = '100vw';
        panel.style.minWidth = '100vw';
        panel.style.maxWidth = '100vw';
        inner.style.width = '100%';
        inner.style.minWidth = 'calc(100vw - 2rem)';
        inner.style.transform = 'scale(1.18)';
        mimicZoomLevel = 1.18;
    } else {
        panel.style.width = '100%';
        panel.style.minWidth = '100%';
        panel.style.maxWidth = 'none';
        inner.style.width = '100%';
        inner.style.minWidth = (1320 * mimicZoomLevel) + 'px';
        inner.style.transform = 'scale(' + mimicZoomLevel + ')';
    }

    if (btnText) {
        btnText.textContent = isFullscreen ? 'Exit Full Screen' : 'Full Screen';
    }
}

document.addEventListener('fullscreenchange', syncMimicFullscreenState);
document.addEventListener('mousemove', (event) => {
    if (!mimicResizeState) return;
    const delta = event.clientY - mimicResizeState.startY;
    const nextScale = clamp(mimicResizeState.startScale + (delta / 240), 1, mimicMaxZoom);
    setMimicCanvasZoom(nextScale);
});
document.addEventListener('mouseup', stopMimicResizeDrag);
document.addEventListener('mouseleave', stopMimicResizeDrag);

document.addEventListener('DOMContentLoaded', () => {
    const handle = document.getElementById('mimic-resize-handle');
    if (handle) {
        handle.addEventListener('mousedown', startMimicResizeDrag);
    }
});

function openComponentDrawer(key) {
    const spec = componentSpecs[key];
    if (!spec) return;

    document.getElementById('drawer-title').textContent = spec.title;
    document.getElementById('drawer-description').textContent = spec.description;

    let limitsHtml = '';
    spec.limits.forEach(limit => {
        limitsHtml += `<li>${limit}</li>`;
    });
    document.getElementById('drawer-limits-list').innerHTML = limitsHtml;

    let telemetryHtml = '';
    const data = window.latestTelemetryData;

    if (!data || window.latestTelemetryOffline) {
        telemetryHtml = `
            <tr>
                <td class="p-3 text-slate-400">Status</td>
                <td class="p-3 text-right text-red-500 font-bold">OFFLINE</td>
            </tr>
        `;
        document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-red-600 animate-ping";
        document.getElementById('drawer-status-text').textContent = "Offline / No Data";
    } else {
        if (key === 'intake') {
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Inlet Air Temp</td><td class="p-3 text-right text-white">24.5°C</td></tr>
                <tr><td class="p-3 text-slate-400">Relative Humidity</td><td class="p-3 text-right text-white">55.0%</td></tr>
                <tr><td class="p-3 text-slate-400">Intake Suction</td><td class="p-3 text-right text-white">1.013 bar</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = "Active";
        } else if (key === 'compressor') {
            const statusLabel = data.compressor_status === 1 ? 'RUNNING' : (data.compressor_status === 2 ? 'FAULT' : 'STANDBY');
            const statusColor = data.compressor_status === 1 ? 'text-emerald-500' : (data.compressor_status === 2 ? 'text-red-500' : 'text-slate-400');
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Motor State</td><td class="p-3 text-right ${statusColor} font-bold">${statusLabel}</td></tr>
                <tr><td class="p-3 text-slate-400">Outlet Temperature</td><td class="p-3 text-right text-white">${parseFloat(data.temperature).toFixed(1)}°C</td></tr>
                <tr><td class="p-3 text-slate-400">Vibration Level</td><td class="p-3 text-right text-white">Normal (2.1 mm/s)</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full " + (data.compressor_status === 1 ? 'bg-emerald-500 led-pulse' : (data.compressor_status === 2 ? 'bg-red-600 animate-ping' : 'bg-slate-500'));
            document.getElementById('drawer-status-text').textContent = statusLabel;
        } else if (key === 'dryer') {
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Refrigerant Temp</td><td class="p-3 text-right text-white">3.4°C</td></tr>
                <tr><td class="p-3 text-slate-400">Condenser State</td><td class="p-3 text-right text-emerald-500 font-bold">ON</td></tr>
                <tr><td class="p-3 text-slate-400">Auto-Drain Valve</td><td class="p-3 text-right text-slate-400">Idle (Closed)</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = "Active";
        } else if (key === 'air_tank') {
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Buffer Pressure</td><td class="p-3 text-right text-white font-mono">${parseFloat(data.pressure + 1.2).toFixed(2)} bar</td></tr>
                <tr><td class="p-3 text-slate-400">Vessel Volume</td><td class="p-3 text-right text-white">1000 Liters</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = "Charged";
        } else if (key === 'psa_towers') {
            const stateA = data.bed_a_status ? 'ADSORBING (Green)' : 'VENTING/REGEN';
            const stateB = data.bed_b_status ? 'ADSORBING (Green)' : 'VENTING/REGEN';
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Bed A Status</td><td class="p-3 text-right text-white">${stateA}</td></tr>
                <tr><td class="p-3 text-slate-400">Bed B Status</td><td class="p-3 text-right text-white">${stateB}</td></tr>
                <tr><td class="p-3 text-slate-400">Equilibration Cycle</td><td class="p-3 text-right text-slate-400">Active (45s cycle)</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = "Cycling";
        } else if (key === 'o2_tank') {
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Oxygen Purity</td><td class="p-3 text-right text-emerald-500 font-bold font-mono">${parseFloat(data.purity).toFixed(2)}%</td></tr>
                <tr><td class="p-3 text-slate-400">Oxygen Pressure</td><td class="p-3 text-right text-white font-mono">${parseFloat(data.pressure).toFixed(2)} bar</td></tr>
                <tr><td class="p-3 text-slate-400">Product Fill Level</td><td class="p-3 text-right text-white font-mono">${parseFloat(data.tank_level).toFixed(1)}%</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = "Monitoring";
        } else if (key === 'booster') {
            const boosterActive = data.compressor_status === 1 && data.pressure > 3.0;
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Booster Motor</td><td class="p-3 text-right ${boosterActive ? 'text-emerald-500 font-bold' : 'text-slate-400'}">${boosterActive ? 'RUNNING' : 'STANDBY'}</td></tr>
                <tr><td class="p-3 text-slate-400">Suction Pressure</td><td class="p-3 text-right text-white">${parseFloat(data.pressure).toFixed(2)} bar</td></tr>
                <tr><td class="p-3 text-slate-400">Discharge Pressure</td><td class="p-3 text-right text-white">${boosterActive ? '135.4 bar' : '0.0 bar'}</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full " + (boosterActive ? 'bg-emerald-500 led-pulse' : 'bg-slate-500');
            document.getElementById('drawer-status-text').textContent = boosterActive ? 'Running' : 'Standby';
        } else if (key === 'manifold') {
            const boosterActive = data.compressor_status === 1 && data.pressure > 3.0;
            telemetryHtml = `
                <tr><td class="p-3 text-slate-400">Manifold Connection</td><td class="p-3 text-right text-white">4 Cylinders (Ready)</td></tr>
                <tr><td class="p-3 text-slate-400">Filling Progress</td><td class="p-3 text-right text-white">${boosterActive ? '88% (Filling)' : 'Holding'}</td></tr>
                <tr><td class="p-3 text-slate-400">Estimated Completion</td><td class="p-3 text-right text-slate-400">${boosterActive ? '12 minutes' : 'N/A'}</td></tr>
            `;
            document.getElementById('drawer-status-led').className = "w-2.5 h-2.5 rounded-full bg-emerald-500 led-pulse";
            document.getElementById('drawer-status-text').textContent = boosterActive ? 'Filling' : 'Ready';
        }
    }

    document.getElementById('drawer-telemetry-body').innerHTML = telemetryHtml;

    const drawer = document.getElementById('component-drawer');
    drawer.classList.remove('translate-x-full');
}

function closeComponentDrawer() {
    const drawer = document.getElementById('component-drawer');
    drawer.classList.add('translate-x-full');
}

document.addEventListener('DOMContentLoaded', () => {
    const drawerCloseListener = (e) => {
        const drawer = document.getElementById('component-drawer');
        const isNode = e.target.closest('[onclick^="openComponentDrawer"]');
        const isDrawer = e.target.closest('#component-drawer');
        if (!isDrawer && !isNode && drawer && !drawer.classList.contains('translate-x-full')) {
            closeComponentDrawer();
        }
    };
    document.addEventListener('click', drawerCloseListener);

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
        const initialAge = {{ $reading ? abs(now()->diffInSeconds($reading->created_at)) : 'null' }};
        updateDashboard(initialData, initialAge);
    }

    // Listen to global status updates from top navbar
    window.addEventListener('system-status-updated', (event) => {
        const data = event.detail.reading;
        const age = event.detail.reading_age;
        if (!data) return;
        updateDashboard(data, age);
    });

    startProceduralAnimation();
});

function setValveState(valve, state) {
    if (!valve) return;
    valve.classList.remove('active', 'fault', 'bg-emerald-500', 'bg-slate-300', 'bg-red-500', 'led-pulse');
    if (state === 'active') {
        valve.classList.add('active', 'led-pulse');
    } else if (state === 'fault') {
        valve.classList.add('fault', 'led-pulse');
    } else {
        valve.classList.add('bg-slate-300');
    }
}

function setPipe3D(el, { active, color, flowRate }) {
    if (!el) return;

    el.classList.toggle('active', !!active);
    el.style.setProperty('--flow-color', color || '#10B981');
    if (active) {
        const rate = Math.max(10, Math.min(200, flowRate || 60));
        const seconds = 1.4 - (rate / 200) * 1.0;
        el.style.setProperty('--flow-speed', seconds.toFixed(2) + 's');
    }
}

function updateCardParticles(containerId, type, state) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (state === 'idle' || state === 'inactive') {
        if (container.__particleInterval) {
            clearInterval(container.__particleInterval);
            container.__particleInterval = null;
        }
        container.innerHTML = '';
        container.__particleState = 'idle';
        return;
    }

    if (container.__particleState === state) return;
    container.__particleState = state;

    if (!container.__particleInterval) {
        container.__particleInterval = setInterval(() => {
            const currentState = container.__particleState;
            if (currentState === 'idle') {
                clearInterval(container.__particleInterval);
                container.__particleInterval = null;
                return;
            }

            const maxParticles = type === 'tank-o2' || type === 'tank-air' ? 18 : 12;
            if (container.children.length > maxParticles) return;

            const p = document.createElement('div');
            
            if (type === 'compressor') {
                p.className = 'air-swirl-particle';
                const leftVal = 30 + Math.random() * 40;
                const topVal = 40 + Math.random() * 30;
                p.style.left = `${leftVal}%`;
                p.style.top = `${topVal}%`;
                container.appendChild(p);
                setTimeout(() => p.remove(), 2000);
            }
            else if (type === 'dryer') {
                p.className = 'water-drop-particle';
                const leftVal = 20 + Math.random() * 60;
                p.style.left = `${leftVal}%`;
                p.style.top = '40%';
                container.appendChild(p);
                setTimeout(() => p.remove(), 1400);
            }
            else if (type === 'tank-air') {
                p.className = 'air-bounce-particle';
                const leftVal = 30 + Math.random() * 40;
                const topVal = 65 + Math.random() * 20;
                p.style.left = `${leftVal}%`;
                p.style.top = `${topVal}%`;
                container.appendChild(p);
                setTimeout(() => p.remove(), 3000);
            }
            else if (type === 'bed') {
                const leftOffset = 25 + Math.random() * 50;
                p.style.left = `${leftOffset}%`;
                if (currentState === 'adsorb') {
                    if (Math.random() > 0.4) {
                        p.className = 'o2-particle';
                        container.appendChild(p);
                        setTimeout(() => p.remove(), 1500);
                    } else {
                        p.className = 'n2-particle';
                        const trapY = -20 - Math.random() * 45;
                        p.style.setProperty('--trap-y', `${trapY}px`);
                        container.appendChild(p);
                        setTimeout(() => p.remove(), 2500);
                    }
                } else if (currentState === 'vent') {
                    p.className = 'vent-particle';
                    const startY = -15 - Math.random() * 50;
                    p.style.setProperty('--start-y', `${startY}px`);
                    container.appendChild(p);
                    setTimeout(() => p.remove(), 1200);
                }
            }
            else if (type === 'tank-o2') {
                p.className = 'o2-bounce-particle';
                const leftVal = 30 + Math.random() * 40;
                const topVal = 65 + Math.random() * 20;
                p.style.left = `${leftVal}%`;
                p.style.top = `${topVal}%`;
                container.appendChild(p);
                setTimeout(() => p.remove(), 3000);
            }
            else if (type === 'booster') {
                p.className = 'booster-fast-particle';
                const topVal = 40 + Math.random() * 30;
                p.style.top = `${topVal}%`;
                container.appendChild(p);
                setTimeout(() => p.remove(), 1000);
            }
            else if (type === 'hospital') {
                p.className = 'pipeline-flow-particle';
                const topVal = 45 + Math.random() * 15;
                p.style.top = `${topVal}%`;
                container.appendChild(p);
                setTimeout(() => p.remove(), 1500);
            }
        }, 220);
    }
}

function setValve3D(el, state) {
    if (!el) return;
    el.classList.remove('active', 'fault');
    if (state === 'active' || state === 'fault') {
        el.classList.add(state);
    }
}

function startProceduralAnimation() {
    if (window.__mimicStageTimer) return;

    window.__mimicCycleStartedAt = window.__mimicCycleStartedAt || Date.now();
    window.__mimicStageTimer = setInterval(() => {
        if (window.latestTelemetryData) {
            updateDashboard(window.latestTelemetryData, null);
        }
    }, 1600);
}

function getProceduralStage(data) {
    const compressorRunning = data.compressor_status === 1;

    if (!compressorRunning) {
        return {
            id: 'standby',
            label: 'PSA Cycle: Standby',
            desc: 'Compressor and PSA skid are idle. No process flow is active.'
        };
    }

    // PLC State Machine simulation (30s cycle)
    const bedCycleDuration = 30000;
    const elapsedSinceStart = Date.now() - (window.__mimicCycleStartedAt || Date.now());
    const bedCyclePos = elapsedSinceStart % bedCycleDuration;

    if (bedCyclePos < 3000) {
        return {
            id: 'a-pressurize',
            label: 'PLC State: Bed A Pressurization',
            desc: 'PLC opens Bed A Air Inlet Valve (YV-104A) to feed compressed air. Simultaneously, Bed B Product Valve (YV-105B) is open to output oxygen from Bed B.'
        };
    } else if (bedCyclePos < 13000) {
        return {
            id: 'a-adsorb',
            label: 'PLC State: Bed A Adsorption (Production)',
            desc: 'Bed A adsorbs nitrogen under high pressure. Simultaneously, Bed B Product Valve (YV-105B) is open to discharge oxygen to the Oxygen Receiver Tank (V-104).'
        };
    } else if (bedCyclePos < 15000) {
        return {
            id: 'equalize-1',
            label: 'PLC State: Pressure Equalization',
            desc: 'PLC opens Equalization Valve (YV-105C) briefly to balance pressure directly between Bed A and Bed B, reclaiming energy before swapping bed roles.'
        };
    } else if (bedCyclePos < 18000) {
        return {
            id: 'b-pressurize',
            label: 'PLC State: Bed B Pressurization',
            desc: 'PLC opens Bed B Air Inlet Valve (YV-104B) to feed compressed air. Simultaneously, Bed A Product Valve (YV-105A) is open to output oxygen from Bed A.'
        };
    } else if (bedCyclePos < 28000) {
        return {
            id: 'b-adsorb',
            label: 'PLC State: Bed B Adsorption (Production)',
            desc: 'Bed B adsorbs nitrogen under high pressure. Simultaneously, Bed A Product Valve (YV-105A) is open to discharge oxygen to the Oxygen Receiver Tank (V-104).'
        };
    } else {
        return {
            id: 'equalize-2',
            label: 'PLC State: Pressure Equalization',
            desc: 'PLC opens Equalization Valve (YV-105C) briefly to balance pressure directly between Bed B and Bed A, reclaiming energy before swapping bed roles.'
        };
    }
}

function updateDashboard(data, age = null) {
    window.latestTelemetryData = data;
    window.latestTelemetryOffline = false;

    // Retrieve HMI elements
    const mimicCompCard = document.getElementById('mimic-compressor-card');
    const mimicCompBadge = document.getElementById('mimic-compressor-badge');
    const mimicCompTemp = document.getElementById('mimic-compressor-temp');
    const compFanIcon = document.getElementById('compressor-fan-icon');
    
    const mimicDryerCard = document.getElementById('mimic-dryer-card');
    const mimicDryerBadge = document.getElementById('mimic-dryer-badge');
    const mimicDryerTemp = document.getElementById('mimic-dryer-temp');
    
    const mimicAirTankCard = document.getElementById('mimic-air_tank-card');
    const mimicAirTankPress = document.getElementById('mimic-air_tank-press');
    
    const mimicManifoldBlockCard = document.getElementById('mimic-manifold-block-card');
    const mimicPsaState = document.getElementById('mimic-psa-state');
    
    const mimicPsaACard = document.getElementById('mimic-psa-a-card');
    const mimicPsaABadge = document.getElementById('mimic-psa-a-badge');
    const puffA1 = document.getElementById('vent-puff-a1');
    const puffA2 = document.getElementById('vent-puff-a2');
    const puffA3 = document.getElementById('vent-puff-a3');
    
    const mimicPsaBCard = document.getElementById('mimic-psa-b-card');
    const mimicPsaBBadge = document.getElementById('mimic-psa-b-badge');
    const puffB1 = document.getElementById('vent-puff-b1');
    const puffB2 = document.getElementById('vent-puff-b2');
    const puffB3 = document.getElementById('vent-puff-b3');
    
    const mimicO2TankCard = document.getElementById('mimic-o2_tank-card');
    const mimicO2TankPurity = document.getElementById('mimic-o2_tank-purity');
    const mimicO2TankPress = document.getElementById('mimic-o2_tank-press');
    
    const mimicBoosterCard = document.getElementById('mimic-booster-card');
    const mimicBoosterBadge = document.getElementById('mimic-booster-badge');
    const mimicBoosterState = document.getElementById('mimic-booster-state');
    
    const mimicManifoldCard = document.getElementById('mimic-manifold-card');
    const mimicManifoldBadge = document.getElementById('mimic-manifold-badge');

    const pipes = [
        document.getElementById('pipe-intake-compressor'),
        document.getElementById('pipe-compressor-dryer'),
        document.getElementById('pipe-manifold-psa-in'),
        document.getElementById('pipe-manifold-psa-a'),
        document.getElementById('pipe-manifold-psa-b'),
        document.getElementById('pipe-manifold-psa-vert'),
        document.getElementById('pipe-psa-o2-out'),
        document.getElementById('pipe-psa-o2-a'),
        document.getElementById('pipe-psa-o2-b'),
        document.getElementById('pipe-psa-o2-vert'),
        document.getElementById('pipe-o2-booster'),
        document.getElementById('pipe-booster-manifold'),
        document.getElementById('pipe-o2-manifold-hospital')
    ];

    const pipeIntake = pipes[0];
    const pipeCompDryer = pipes[1];
    const pipeManifoldPsaIn = pipes[2];
    const pipeManifoldPsaA = pipes[3];
    const pipeManifoldPsaB = pipes[4];
    const pipeManifoldPsaVertA = document.getElementById('pipe-manifold-psa-vert-a');
    const pipeManifoldPsaVertB = document.getElementById('pipe-manifold-psa-vert-b');
    const pipePsaO2Out = pipes[6];
    const pipePsaO2A = pipes[7];
    const pipePsaO2B = pipes[8];
    const pipePsaO2VertA = document.getElementById('pipe-psa-o2-vert-a');
    const pipePsaO2VertB = document.getElementById('pipe-psa-o2-vert-b');
    const pipeO2Booster = pipes[10];
    const pipeBoosterManifold = pipes[11];
    const pipeO2ManifoldHospital = pipes[12];
    const pipePsaEq = document.getElementById('pipe-psa-eq');

    const valveIntake = document.getElementById('valve-intake');
    const valveCompressor = document.getElementById('valve-compressor');
    const valveAirTank = document.getElementById('valve-compressor'); // Air receiver uses the same feed solenoid as the dryer isolation stage
    const valveDryer = document.getElementById('valve-dryer');
    const valvePsaA = document.getElementById('valve-psa-a'); // Manifold Feed A
    const valvePsaB = document.getElementById('valve-psa-b'); // Manifold Feed B
    const valveVentA = document.getElementById('valve-vent-a'); // Manifold Vent A
    const valveVentB = document.getElementById('valve-vent-b'); // Manifold Vent B
    const valveO2Tank = document.getElementById('valve-o2-tank');
    const valveBooster = document.getElementById('valve-booster');
    const valveEq = document.getElementById('valve-eq');

    const scoreVal = document.getElementById('health-score');
    const ring = document.getElementById('health-ring');
    const diagText = document.getElementById('diagnostics-text');
    const diagReasons = document.getElementById('diagnostics-reasons');
    const cycleIcon = document.getElementById('cycle-icon');
    const cycleLabel = document.getElementById('cycle-label');
    const cycleDesc = document.getElementById('cycle-desc');
    const stagePrimary = document.getElementById('mimic-stage-primary');
    const stageDetail = document.getElementById('mimic-stage-detail');
    const lastUpdatedEl = document.getElementById('last-updated');

    if (age !== null && age > 15) {
        window.latestTelemetryOffline = true;

        setPipe3D(pipeIntake, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeCompDryer, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeManifoldPsaIn, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeManifoldPsaVertA, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeManifoldPsaVertB, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeManifoldPsaA, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeManifoldPsaB, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaO2Out, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaO2VertA, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaO2VertB, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaO2A, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaO2B, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeO2Booster, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeBoosterManifold, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipeO2ManifoldHospital, { active: false, color: '#94A3B8', flowRate: 0 });
        setPipe3D(pipePsaEq, { active: false, color: '#94A3B8', flowRate: 0 });
 
        // Set all valves to static grey
        setValveState(valveIntake, 'inactive');
        setValveState(valveCompressor, 'inactive');
        setValveState(valveDryer, 'inactive');
        setValveState(valvePsaA, 'inactive');
        setValveState(valvePsaB, 'inactive');
        setValveState(valveVentA, 'inactive');
        setValveState(valveVentB, 'inactive');
        setValveState(valveO2Tank, 'inactive');
        setValveState(valveBooster, 'inactive');
        setValveState(valveEq, 'inactive');
        
        // Reset component statuses
        if (mimicCompBadge) {
            mimicCompBadge.textContent = "Offline";
            mimicCompBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-red-600 text-white shadow-sm";
        }
        if (mimicCompCard) mimicCompCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex items-center justify-center transition duration-200 shadow-sm";
        if (mimicCompTemp) mimicCompTemp.textContent = "— °C";
        if (compFanIcon) compFanIcon.className = "w-4 h-4 text-slate-400";

        if (mimicDryerBadge) {
            mimicDryerBadge.textContent = "Offline";
            mimicDryerBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicDryerTemp) mimicDryerTemp.textContent = "— °C";
        if (mimicDryerCard) {
            mimicDryerCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }

        if (mimicAirTankPress) mimicAirTankPress.textContent = "— bar";
        if (mimicAirTankCard) {
            mimicAirTankCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }

        if (mimicManifoldBlockCard) {
            mimicManifoldBlockCard.className = "relative w-32 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-3 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }
        if (mimicPsaState) mimicPsaState.textContent = "Bed A / Bed B";

        if (mimicPsaABadge) {
            mimicPsaABadge.textContent = "Offline";
            mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicPsaACard) {
            mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer";
        }
        if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
        if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
        if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

        if (mimicPsaBBadge) {
            mimicPsaBBadge.textContent = "Offline";
            mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicPsaBCard) {
            mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer";
        }
        if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
        if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
        if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

        if (mimicO2TankPurity) {
            mimicO2TankPurity.textContent = "— % O₂";
            mimicO2TankPurity.className = "relative z-30 self-start px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-slate-500 text-white shadow-sm";
        }
        if (mimicO2TankPress) mimicO2TankPress.textContent = "— bar";
        if (mimicO2TankCard) {
            mimicO2TankCard.classList.remove('border-emerald-500', 'glow-adsorbing');
            mimicO2TankCard.classList.add('border-transparent');
        }

        if (mimicBoosterBadge) {
            mimicBoosterBadge.textContent = "Offline";
            mimicBoosterBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-200 text-slate-500 shadow-sm";
        }
        if (mimicBoosterState) mimicBoosterState.textContent = "Offline";
        if (mimicBoosterCard) mimicBoosterCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex items-center justify-center transition duration-200 shadow-sm";

        if (mimicManifoldBadge) {
            mimicManifoldBadge.textContent = "Offline";
            mimicManifoldBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-200 text-slate-500 shadow-sm";
        }

        if (scoreVal) scoreVal.textContent = "OFFLINE";
        if (ring) {
            ring.style.strokeDashoffset = 377;
            ring.setAttribute('stroke', '#EF4444');
        }
        if (diagText && diagReasons) {
            diagText.textContent = "CRITICAL: The telemetry collector service is offline. System data is currently frozen.";
            diagText.className = "text-xs text-red-700 font-semibold leading-relaxed mb-3";
            diagReasons.innerHTML = `
                <div class="flex items-start gap-1.5 text-red-600 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 mt-1.5 flex-shrink-0"></span>
                    <span>Modbus Collector service stopped or physical PLC disconnected. Check background logs.</span>
                </div>
            `;
        }
        if (lastUpdatedEl) lastUpdatedEl.textContent = "Offline";
        return;
    }

    // Update HMI values
    if (mimicCompTemp) mimicCompTemp.textContent = parseFloat(data.temperature).toFixed(1) + '°C';

    const compressorRunning = data.compressor_status === 1;
    const proceduralStage = getProceduralStage(data);
    const stageId = proceduralStage.id;

    if (stagePrimary) {
        stagePrimary.textContent = proceduralStage.label;
    }
    if (stageDetail) {
        stageDetail.textContent = proceduralStage.desc;
    }
    const stageProductActive = stageId === 'a-adsorb' || stageId === 'b-adsorb';
    const stageDeliveryActive = stageId === 'a-adsorb' || stageId === 'b-adsorb';
    const psaPhase = stageId;
    const boosterActive = compressorRunning && stageProductActive && data.pressure > 3.0;
    
    // Compressor card & badge & solenoid valves
    if (compressorRunning) {
        if (mimicCompBadge) {
            mimicCompBadge.textContent = "Running";
            mimicCompBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-emerald-500 text-white shadow-sm";
        }
        if (mimicCompCard) mimicCompCard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex items-center justify-center transition duration-200 glow-adsorbing shadow-sm";
        if (compFanIcon) compFanIcon.className = "w-4 h-4 text-emerald-500 animate-spin";
        
        // Open all primary feed train valves since compressor is active
        setValveState(valveIntake, 'active');
        setValveState(valveCompressor, 'active');
        setValveState(valveDryer, 'active');
        setValveState(valveAirTank, 'active');
    } else if (data.compressor_status === 2) {
        if (mimicCompBadge) {
            mimicCompBadge.textContent = "Fault";
            mimicCompBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-red-600 text-white shadow-sm";
        }
        if (mimicCompCard) mimicCompCard.className = "relative w-36 h-36 bg-white border-2 border-red-600 rounded-2xl p-2 flex items-center justify-center transition duration-200 glow-fault shadow-sm";
        if (compFanIcon) compFanIcon.className = "w-4 h-4 text-red-500";
        
        // Intake is green suction, compressor valve is red fault warning
        setValveState(valveIntake, 'active');
        setValveState(valveCompressor, 'fault');
        setValveState(valveDryer, 'inactive');
        setValveState(valveAirTank, 'inactive');
    } else {
        if (mimicCompBadge) {
            mimicCompBadge.textContent = "Standby";
            mimicCompBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicCompCard) mimicCompCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex items-center justify-center transition duration-200 shadow-sm";
        if (compFanIcon) compFanIcon.className = "w-4 h-4 text-slate-400";
        
        // Valves closed/standby
        setValveState(valveIntake, 'active');
        setValveState(valveCompressor, 'inactive');
        setValveState(valveDryer, 'inactive');
        setValveState(valveAirTank, 'inactive');
    }

    // Dryer
    if (mimicDryerTemp) mimicDryerTemp.textContent = compressorRunning ? '3.2°C' : '— °C';
    if (mimicDryerBadge) {
        mimicDryerBadge.textContent = compressorRunning ? "Active" : "Standby";
        mimicDryerBadge.className = "relative z-30 self-center px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm " + (compressorRunning ? "bg-emerald-500 text-white" : "bg-slate-500 text-white");
    }
    if (mimicDryerCard) {
        if (compressorRunning) {
            mimicDryerCard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 glow-adsorbing shadow-sm";
        } else {
            mimicDryerCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }
    }

    // Air Tank
    if (mimicAirTankPress) mimicAirTankPress.textContent = parseFloat(data.pressure + 1.2).toFixed(2) + ' bar';
    if (mimicAirTankCard) {
        if (compressorRunning) {
            mimicAirTankCard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 glow-adsorbing shadow-sm";
        } else {
            mimicAirTankCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }
    }

    // PSA Column & solenoids & venting particles
    // PSA Column & solenoids & venting particles
    const psaActive = compressorRunning;
    
    if (mimicManifoldBlockCard) {
        if (psaActive) {
            mimicManifoldBlockCard.className = "relative w-32 h-36 bg-white border-2 border-[#2B8AC6] rounded-2xl p-3 flex flex-col justify-between items-center transition duration-200 glow-adsorbing shadow-sm";
        } else {
            mimicManifoldBlockCard.className = "relative w-32 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-3 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }
    }

    let bedPhase = 'standby';
    if (psaActive) {
        const hasA = data.bed_a_status === 1;
        const hasB = data.bed_b_status === 1;
        
        if (hasA && !hasB) {
            bedPhase = 'a-adsorb';
        } else if (!hasA && hasB) {
            bedPhase = 'b-adsorb';
        } else {
            // Symmetrical 6-state PLC fallback simulation (30s cycle)
            const bedCycleDuration = 30000;
            const elapsedSinceStart = Date.now() - (window.__mimicCycleStartedAt || Date.now());
            const bedCyclePos = elapsedSinceStart % bedCycleDuration;
            if (bedCyclePos < 3000) {
                bedPhase = 'a-pressurize';
            } else if (bedCyclePos < 13000) {
                bedPhase = 'a-adsorb';
            } else if (bedCyclePos < 15000) {
                bedPhase = 'equalize-1';
            } else if (bedCyclePos < 18000) {
                bedPhase = 'b-pressurize';
            } else if (bedCyclePos < 28000) {
                bedPhase = 'b-adsorb';
            } else {
                bedPhase = 'equalize-2';
            }
        }
    }

    if (psaActive) {
        if (bedPhase === 'a-pressurize') {
            if (mimicPsaState) mimicPsaState.textContent = 'COL-A: Pressurizing';
            if (mimicPsaABadge) {
                mimicPsaABadge.textContent = "Pressurize";
                mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-blue-500 text-white";
            }
            if (mimicPsaACard) {
                mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-blue-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }
            if (mimicPsaBBadge) {
                mimicPsaBBadge.textContent = "Venting";
                mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-orange-500 text-white";
            }
            if (mimicPsaBCard) {
                mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-orange-400 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-regenerating";
            }

            // Vent puffs B are active, A inactive
            if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] vent-puff-1";
            if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] vent-puff-2";
            if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] vent-puff-3";

            if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            setValveState(valvePsaA, 'active');
            setValveState(valvePsaB, 'inactive');
            setValveState(valveVentA, 'inactive');
            setValveState(valveVentB, 'active');
            setValveState(valveEq, 'inactive');
        } else if (bedPhase === 'a-adsorb') {
            if (mimicPsaState) mimicPsaState.textContent = 'COL-A: Adsorbing';
            if (mimicPsaABadge) {
                mimicPsaABadge.textContent = "Adsorb";
                mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-emerald-500 text-white";
            }
            if (mimicPsaACard) {
                mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }
            if (mimicPsaBBadge) {
                mimicPsaBBadge.textContent = "Purging";
                mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-orange-500 text-white";
            }
            if (mimicPsaBCard) {
                mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-orange-400 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-regenerating";
            }

            // Vent puffs B are active, A inactive
            if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] vent-puff-1";
            if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] vent-puff-2";
            if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] vent-puff-3";

            if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            setValveState(valvePsaA, 'active');
            setValveState(valvePsaB, 'inactive');
            setValveState(valveVentA, 'inactive');
            setValveState(valveVentB, 'active');
            setValveState(valveEq, 'inactive');
        } else if (bedPhase === 'b-pressurize') {
            if (mimicPsaState) mimicPsaState.textContent = 'COL-B: Pressurizing';
            if (mimicPsaABadge) {
                mimicPsaABadge.textContent = "Venting";
                mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-orange-500 text-white";
            }
            if (mimicPsaACard) {
                mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-orange-400 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-regenerating";
            }
            if (mimicPsaBBadge) {
                mimicPsaBBadge.textContent = "Pressurize";
                mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-blue-500 text-white";
            }
            if (mimicPsaBCard) {
                mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-blue-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }

            // Vent puffs A are active, B inactive
            if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] vent-puff-1";
            if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] vent-puff-2";
            if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] vent-puff-3";

            if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            setValveState(valvePsaA, 'inactive');
            setValveState(valvePsaB, 'active');
            setValveState(valveVentA, 'active');
            setValveState(valveVentB, 'inactive');
            setValveState(valveEq, 'inactive');
        } else if (bedPhase === 'b-adsorb') {
            if (mimicPsaState) mimicPsaState.textContent = 'COL-B: Adsorbing';
            if (mimicPsaABadge) {
                mimicPsaABadge.textContent = "Purging";
                mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-orange-500 text-white";
            }
            if (mimicPsaACard) {
                mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-orange-400 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-regenerating";
            }
            if (mimicPsaBBadge) {
                mimicPsaBBadge.textContent = "Adsorb";
                mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-emerald-500 text-white";
            }
            if (mimicPsaBCard) {
                mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }

            // Vent puffs A are active, B inactive
            if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] vent-puff-1";
            if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] vent-puff-2";
            if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] vent-puff-3";

            if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            setValveState(valvePsaA, 'inactive');
            setValveState(valvePsaB, 'active');
            setValveState(valveVentA, 'active');
            setValveState(valveVentB, 'inactive');
            setValveState(valveEq, 'inactive');
        } else {
            // Equalizing (equalize-1 and equalize-2)
            if (mimicPsaState) mimicPsaState.textContent = 'Pressure Equalizing';
            if (mimicPsaABadge) {
                mimicPsaABadge.textContent = "Equalize";
                mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-teal-500 text-white";
            }
            if (mimicPsaACard) {
                mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-teal-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }
            if (mimicPsaBBadge) {
                mimicPsaBBadge.textContent = "Equalize";
                mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm bg-teal-500 text-white";
            }
            if (mimicPsaBCard) {
                mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-teal-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer glow-adsorbing";
            }

            // Both puffs inactive
            if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
            if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
            if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

            setValveState(valvePsaA, 'inactive');
            setValveState(valvePsaB, 'inactive');
            setValveState(valveVentA, 'inactive');
            setValveState(valveVentB, 'inactive');
            setValveState(valveEq, 'active');
        }
    } else {
        // Standby / offline / fault
        if (mimicPsaState) mimicPsaState.textContent = "Standby";
        if (mimicPsaABadge) {
            mimicPsaABadge.textContent = "Standby";
            mimicPsaABadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicPsaACard) {
            mimicPsaACard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer";
        }
        if (mimicPsaBBadge) {
            mimicPsaBBadge.textContent = "Standby";
            mimicPsaBBadge.className = "relative z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-500 text-white shadow-sm";
        }
        if (mimicPsaBCard) {
            mimicPsaBCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm cursor-pointer";
        }

        if (puffA1) puffA1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
        if (puffA2) puffA2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
        if (puffA3) puffA3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

        if (puffB1) puffB1.className = "absolute top-[-8px] left-[3px] w-2.5 h-2.5 bg-orange-400 rounded-full blur-[0.5px] opacity-0";
        if (puffB2) puffB2.className = "absolute top-[-14px] left-[5px] w-3 h-3 bg-orange-300 rounded-full blur-[0.5px] opacity-0";
        if (puffB3) puffB3.className = "absolute top-[-20px] left-[1px] w-3.5 h-3.5 bg-orange-200 rounded-full blur-[0.5px] opacity-0";

        setValveState(valvePsaA, 'inactive');
        setValveState(valvePsaB, 'inactive');
        setValveState(valveVentA, 'inactive');
        setValveState(valveVentB, 'inactive');
        setValveState(valveEq, 'inactive');
    }

    // O2 Tank
    if (mimicO2TankPurity) {
        mimicO2TankPurity.textContent = parseFloat(data.purity).toFixed(1) + '% O₂';
        mimicO2TankPurity.className = "relative z-30 self-start px-1.5 py-0.5 rounded text-[8px] font-extrabold shadow-sm bg-emerald-500 text-white";
    }
    if (mimicO2TankPress) {
        mimicO2TankPress.textContent = parseFloat(data.pressure).toFixed(2) + ' bar';
    }
    if (mimicO2TankCard) {
        if (psaActive) {
            mimicO2TankCard.className = "relative w-36 h-36 bg-white border-2 border-emerald-500 rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 glow-adsorbing shadow-sm";
        } else {
            mimicO2TankCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm";
        }
    }

    // Booster & Solenoids
    if (mimicBoosterBadge) {
        mimicBoosterBadge.textContent = boosterActive ? "Running" : "Standby";
        mimicBoosterBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm " + (boosterActive ? "bg-emerald-500 text-white" : "bg-slate-500 text-white");
    }
    if (mimicBoosterState) {
        mimicBoosterState.textContent = boosterActive ? "Discharging" : "Standby";
    }
    if (mimicBoosterCard) {
        mimicBoosterCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm " + (boosterActive ? "glow-adsorbing border-emerald-500" : "");
    }
    setValveState(valveO2Tank, stageProductActive ? 'active' : 'inactive');
    setValveState(valveBooster, stageDeliveryActive ? 'active' : 'inactive');

    // Manifold (Hospital delivery)
    if (mimicManifoldBadge) {
        mimicManifoldBadge.textContent = stageDeliveryActive ? "Delivering" : "Holding";
        mimicManifoldBadge.className = "absolute top-3 right-3 z-30 px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase shadow-sm " + (stageDeliveryActive ? "bg-emerald-500 text-white" : "bg-slate-500 text-white");
    }
    if (mimicManifoldCard) {
        mimicManifoldCard.className = "relative w-36 h-36 bg-white border-2 border-[#DDE3EE] rounded-2xl p-2 flex flex-col justify-between items-center transition duration-200 shadow-sm " + (stageDeliveryActive ? "glow-adsorbing border-emerald-500" : "");
    }

    // Pipes Flow Activation

    const flow = {
        airLoopActive: compressorRunning,
        bedAFeeding: psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb'),
        bedBFeeding: psaActive && (bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb'),
        bedAProducing: psaActive && (bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb'),
        bedBProducing: psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb'),
        equalizing: psaActive && (bedPhase === 'equalize-1' || bedPhase === 'equalize-2'),
        tankFilling: psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb' || bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb'),
        deliveryActive: psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb' || bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb')
    };

    setPipe3D(pipeIntake, { active: flow.airLoopActive, color: '#3B82F6', flowRate: data.flow_rate });
    setPipe3D(pipeCompDryer, { active: flow.airLoopActive, color: '#2563EB', flowRate: data.flow_rate });

    setPipe3D(pipeManifoldPsaIn, { active: flow.bedAFeeding || flow.bedBFeeding, color: '#3B82F6', flowRate: data.flow_rate });
    setPipe3D(pipeManifoldPsaA, { active: flow.bedAFeeding, color: '#3B82F6', flowRate: data.flow_rate });
    setPipe3D(pipeManifoldPsaB, { active: flow.bedBFeeding, color: '#3B82F6', flowRate: data.flow_rate });
    setPipe3D(pipeManifoldPsaVertA, { active: flow.bedAFeeding, color: '#3B82F6', flowRate: data.flow_rate });
    setPipe3D(pipeManifoldPsaVertB, { active: flow.bedBFeeding, color: '#3B82F6', flowRate: data.flow_rate });

    setPipe3D(pipePsaO2Out, { active: flow.bedAProducing || flow.bedBProducing, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipePsaO2A, { active: flow.bedAProducing, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipePsaO2B, { active: flow.bedBProducing, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipePsaO2VertA, { active: flow.bedAProducing, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipePsaO2VertB, { active: flow.bedBProducing, color: '#10B981', flowRate: data.flow_rate });

    // Equalization pipeline
    setPipe3D(pipePsaEq, { active: flow.equalizing, color: '#0D9488', flowRate: data.flow_rate });

    setPipe3D(pipeO2Booster, { active: flow.tankFilling, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipeBoosterManifold, { active: flow.deliveryActive, color: '#10B981', flowRate: data.flow_rate });
    setPipe3D(pipeO2ManifoldHospital, { active: flow.deliveryActive, color: '#059669', flowRate: data.flow_rate });

    // Triggering particle animations across all process equipment cards
    updateCardParticles('compressor-particles', 'compressor', compressorRunning ? 'active' : 'idle');
    updateCardParticles('dryer-particles', 'dryer', compressorRunning ? 'active' : 'idle');
    updateCardParticles('air-tank-particles', 'tank-air', compressorRunning ? 'active' : 'idle');
    
    updateCardParticles('psa-a-particles', 'bed', psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb') ? 'adsorb' : (psaActive && (bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb') ? 'vent' : 'idle'));
    updateCardParticles('psa-b-particles', 'bed', psaActive && (bedPhase === 'b-pressurize' || bedPhase === 'b-adsorb') ? 'adsorb' : (psaActive && (bedPhase === 'a-pressurize' || bedPhase === 'a-adsorb') ? 'vent' : 'idle'));
    
    updateCardParticles('o2-tank-particles', 'tank-o2', flow.tankFilling ? 'active' : 'idle');
    updateCardParticles('booster-particles', 'booster', boosterActive ? 'active' : 'idle');
    updateCardParticles('hospital-particles', 'hospital', flow.deliveryActive ? 'active' : 'idle');

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
    if (scoreVal) scoreVal.textContent = healthScore + '%';
    
    // Update SVG circle stroke
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
    
    // Update procedural cycle labels
    if (cycleIcon && cycleLabel && cycleDesc) {
        if (data.compressor_status === 1) {
            cycleLabel.textContent = proceduralStage.label;
            cycleDesc.textContent = proceduralStage.desc;
            cycleIcon.className = "w-5 h-5 text-[#2B8AC6] animate-spin";
        } else {
            cycleLabel.textContent = "PSA Cycle: Standby";
            cycleDesc.textContent = "Compressor and PSA skid are idle. No process flow is active.";
            cycleIcon.className = "w-5 h-5 text-gray-400";
        }
    }

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