@extends('layouts.app')
@section('title', 'Equipment')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-semibold text-white">Equipment</h1>
    <p class="text-sm text-gray-500 mt-0.5">Plant equipment status and maintenance schedule</p>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-800 text-xs text-gray-500 uppercase tracking-widest">
                <th class="text-left px-5 py-3">Code</th>
                <th class="text-left px-5 py-3">Name</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-left px-5 py-3">Last Service</th>
                <th class="text-left px-5 py-3">Next Service</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipment as $item)
            <tr class="border-b border-gray-800 hover:bg-gray-800/50">
                <td class="px-5 py-3 font-mono text-gray-400">{{ $item->code }}</td>
                <td class="px-5 py-3 text-white">{{ $item->name }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-bold
                        {{ $item->status === 'ONLINE'      ? 'bg-green-900 text-green-300' :
                           ($item->status === 'FAULT'       ? 'bg-red-900 text-red-300' :
                           ($item->status === 'MAINTENANCE' ? 'bg-yellow-900 text-yellow-300' :
                                                              'bg-gray-700 text-gray-400')) }}">
                        {{ $item->status }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-400">{{ $item->last_service?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-400">{{ $item->next_service?->format('Y-m-d') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-8 text-center text-gray-500">No equipment records yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection