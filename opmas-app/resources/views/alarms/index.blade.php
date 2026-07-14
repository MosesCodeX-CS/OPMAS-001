@extends('layouts.app')
@section('title', 'Alarms')

@section('content')
<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#1B3A6B] tracking-tight">Plant Alarms & Alerts</h1>
        <p class="text-sm text-[#6B7A90] mt-1">Audit log of system anomalies, warning limits, and threshold breaches.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('alarms', ['status' => 'active']) }}" class="px-4 py-2 rounded-lg text-xs font-bold {{ $status === 'active' ? 'bg-[#1B3A6B] text-white shadow-sm' : 'bg-white border border-[#DDE3EE] text-[#1A2A3A] hover:bg-gray-50' }}">Active Only</a>
        <a href="{{ route('alarms', ['status' => 'resolved']) }}" class="px-4 py-2 rounded-lg text-xs font-bold {{ $status === 'resolved' ? 'bg-[#1B3A6B] text-white shadow-sm' : 'bg-white border border-[#DDE3EE] text-[#1A2A3A] hover:bg-gray-50' }}">Resolved Only</a>
        <a href="{{ route('alarms', ['status' => 'all']) }}" class="px-4 py-2 rounded-lg text-xs font-bold {{ $status === 'all' ? 'bg-[#1B3A6B] text-white shadow-sm' : 'bg-white border border-[#DDE3EE] text-[#1A2A3A] hover:bg-gray-50' }}">All Records</a>
    </div>
</div>

<!-- Alarm Counts Grid -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="kijabe-card p-5 border-l-4 border-l-red-600 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Active Alerts</p>
        <p class="text-3xl font-bold text-red-600 font-mono">{{ $counts[0] ?? 0 }}</p>
    </div>
    <div class="kijabe-card p-5 border-l-4 border-l-emerald-600 bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Resolved Alerts</p>
        <p class="text-3xl font-bold text-emerald-600 font-mono">{{ $counts[1] ?? 0 }}</p>
    </div>
    <div class="kijabe-card p-5 border-l-4 border-l-[#2B8AC6] bg-white">
        <p class="text-xs uppercase tracking-widest text-[#6B7A90] mb-1">Total Logs</p>
        <p class="text-3xl font-bold text-[#2B8AC6] font-mono">{{ ($counts[0] ?? 0) + ($counts[1] ?? 0) }}</p>
    </div>
</div>

<!-- Alarm Logs List -->
<div class="space-y-4">
    @forelse($alarms as $alarm)
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-xl kijabe-card bg-white transition-all duration-200 border-l-4
             {{ $alarm->severity === 'CRITICAL' ? 'border-l-red-600' : ($alarm->severity === 'WARNING' ? 'border-l-amber-500' : 'border-l-[#2B8AC6]') }}
             {{ $alarm->resolved ? 'opacity-70 hover:opacity-100' : '' }}">
            
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <span class="text-[10px] font-extrabold px-2.5 py-1 rounded text-white tracking-wider uppercase
                          {{ $alarm->severity === 'CRITICAL' ? 'bg-red-600' : ($alarm->severity === 'WARNING' ? 'bg-amber-500' : 'bg-[#2B8AC6]') }}">
                        {{ $alarm->severity }}
                    </span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-[#1A2A3A]">{{ $alarm->message }}</h3>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#6B7A90] mt-1.5 font-medium">
                        <span class="flex items-center gap-1"><i data-lucide="tag" class="w-3.5 h-3.5"></i> Type: {{ $alarm->type }}</span>
                        <span>•</span>
                        <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Triggered: {{ $alarm->created_at->format('Y-m-d H:i:s') }} ({{ $alarm->created_at->diffForHumans() }})</span>
                        @if($alarm->resolved)
                            <span>•</span>
                            <span class="flex items-center gap-1 text-emerald-600">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Resolved: {{ $alarm->resolved_at?->diffForHumans() }} by {{ $alarm->resolvedByUser?->name ?? 'System Process' }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action buttons (Resolve / Delete) -->
            <div class="flex items-center gap-2 flex-shrink-0 self-end md:self-center">
                @if(!$alarm->resolved && !auth()->user()->isUser())
                    <form action="{{ route('alarms.resolve', $alarm) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#1B3A6B] hover:bg-[#153460] text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                            <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                            <span>Resolve</span>
                        </button>
                    </form>
                @endif

                @if(auth()->user()->isSystemAdmin())
                    <form action="{{ route('alarms.destroy', $alarm) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this alarm record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg bg-red-50 border border-red-200 hover:bg-red-100 text-red-600 transition" title="Delete record">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl p-12 text-center bg-white border border-[#DDE3EE] shadow-sm">
            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shield-check" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <h3 class="text-sm font-semibold text-[#1A2A3A]">All Systems Nominal</h3>
            <p class="text-xs text-[#6B7A90] mt-1">No alarm logs found matching the selected state.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-8">
    {{ $alarms->links() }}
</div>
@endsection