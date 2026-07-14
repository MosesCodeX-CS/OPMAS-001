@extends('layouts.app')
@section('title', 'Alarms')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-white">Alarms</h1>
    <p class="text-sm text-gray-500 mt-0.5">All active and resolved alarms</p>
</div>

<div class="space-y-2">
    @forelse($alarms as $alarm)
        @include('partials.alarm-badge', ['alarm' => $alarm])
    @empty
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 text-center">
            <p class="text-gray-500">No alarms recorded.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $alarms->links() }}
</div>
@endsection