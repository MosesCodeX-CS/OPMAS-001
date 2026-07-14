@extends('layouts.app')
@section('title', 'Alarms')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold" style="color:#1B3A6B;">Alarms</h1>
    <p class="text-sm mt-0.5" style="color:#6B7A90;">All active and resolved alarms</p>
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