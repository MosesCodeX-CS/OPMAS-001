@extends('layouts.app')
@section('title', 'Alarms')

@section('content')
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <h1 class="text-xl font-bold" style="color:#1B3A6B;">Alarms</h1>
        <p class="text-sm mt-0.5" style="color:#6B7A90;">All active and resolved alarms</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('alarms', ['status' => 'active']) }}" class="rounded-full px-4 py-2 text-xs font-semibold {{ $status === 'active' ? 'bg-[#1B3A6B] text-white' : 'bg-white text-[#1B3A6B] border border-[#DDE3EE]' }}">Active</a>
        <a href="{{ route('alarms', ['status' => 'resolved']) }}" class="rounded-full px-4 py-2 text-xs font-semibold {{ $status === 'resolved' ? 'bg-[#1B3A6B] text-white' : 'bg-white text-[#1B3A6B] border border-[#DDE3EE]' }}">Resolved</a>
        <a href="{{ route('alarms', ['status' => 'all']) }}" class="rounded-full px-4 py-2 text-xs font-semibold {{ $status === 'all' ? 'bg-[#1B3A6B] text-white' : 'bg-white text-[#1B3A6B] border border-[#DDE3EE]' }}">All</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs uppercase tracking-widest mb-2" style="color:#6B7A90;">Active alarms</p>
        <p class="text-3xl font-bold" style="color:#B91C1C;">{{ $counts[0] ?? 0 }}</p>
    </div>
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs uppercase tracking-widest mb-2" style="color:#6B7A90;">Resolved alarms</p>
        <p class="text-3xl font-bold" style="color:#15803D;">{{ $counts[1] ?? 0 }}</p>
    </div>
    <div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
        <p class="text-xs uppercase tracking-widest mb-2" style="color:#6B7A90;">Total alarms</p>
        <p class="text-3xl font-bold" style="color:#1B3A6B;">{{ ($counts[0] ?? 0) + ($counts[1] ?? 0) }}</p>
    </div>
</div>

<div class="space-y-2">
    @forelse($alarms as $alarm)
        @include('partials.alarm-badge', ['alarm' => $alarm])
    @empty
        <div class="rounded-xl p-8 text-center border shadow-sm" style="background:#FFFFFF; border-color:#DDE3EE;">
            <p style="color:#6B7A90;">No alarms recorded.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">{{ $alarms->links() }}</div>
@endsection