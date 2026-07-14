@extends('layouts.app')
@section('title', 'Equipment')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold" style="color:#1B3A6B;">Equipment</h1>
    <p class="text-sm mt-0.5" style="color:#6B7A90;">Plant equipment status and maintenance schedule</p>
</div>

<div class="rounded-xl shadow-sm border overflow-hidden" style="background:#FFFFFF; border-color:#DDE3EE;">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b text-xs font-semibold uppercase tracking-widest"
                style="border-color:#DDE3EE; background:#F4F6F9; color:#1B3A6B;">
                <th class="text-left px-5 py-3">Code</th>
                <th class="text-left px-5 py-3">Name</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-left px-5 py-3">Last Service</th>
                <th class="text-left px-5 py-3">Next Service</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipment as $item)
            <tr class="border-b hover:bg-gray-50" style="border-color:#DDE3EE;">
                <td class="px-5 py-3 font-mono text-sm" style="color:#2B8AC6;">{{ $item->code }}</td>
                <td class="px-5 py-3 font-medium" style="color:#1A2A3A;">{{ $item->name }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                          style="background:{{ $item->status === 'ONLINE' ? '#DCFCE7' : ($item->status === 'FAULT' ? '#FEE2E2' : ($item->status === 'MAINTENANCE' ? '#FEF9C3' : '#F1F5F9')) }};
                                 color:{{ $item->status === 'ONLINE' ? '#15803D' : ($item->status === 'FAULT' ? '#B91C1C' : ($item->status === 'MAINTENANCE' ? '#92400E' : '#475569')) }};">
                        {{ $item->status }}
                    </span>
                </td>
                <td class="px-5 py-3" style="color:#6B7A90;">{{ $item->last_service?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-5 py-3" style="color:#6B7A90;">{{ $item->next_service?->format('Y-m-d') ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-8 text-center" style="color:#6B7A90;">No equipment records yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection