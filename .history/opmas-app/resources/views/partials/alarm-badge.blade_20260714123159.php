<div class="flex items-start gap-3 rounded-lg px-4 py-3 border"
     style="background:#FFFFFF; border-color:{{ $alarm->severity === 'CRITICAL' ? '#FCA5A5' : ($alarm->severity === 'WARNING' ? '#FCD34D' : '#DDE3EE') }};">
    <span class="text-xs font-bold mt-0.5 px-2 py-0.5 rounded"
          style="background:{{ $alarm->severity === 'CRITICAL' ? '#FEE2E2' : ($alarm->severity === 'WARNING' ? '#FEF9C3' : '#EEF2FF') }};
                 color:{{ $alarm->severity === 'CRITICAL' ? '#B91C1C' : ($alarm->severity === 'WARNING' ? '#92400E' : '#1B3A6B') }};">
        {{ $alarm->severity }}
    </span>
    <div class="flex-1">
        <p class="text-sm" style="color:#1A2A3A;">{{ $alarm->message }}</p>
        <p class="text-xs mt-0.5" style="color:#6B7A90;">{{ $alarm->created_at->diffForHumans() }}</p>
    </div>
    @if(!$alarm->resolved)
        <form action="{{ route('alarms.resolve', $alarm) }}" method="POST" class="ml-auto flex items-center">
            @csrf
            <button type="submit" class="rounded-full bg-[#1B3A6B] text-white px-3 py-1 text-xs font-semibold hover:bg-[#16376a] transition">Resolve</button>
        </form>
    @endif
</div>