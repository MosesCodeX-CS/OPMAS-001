<div class="flex items-start gap-3 bg-gray-900 border rounded-lg px-4 py-3
    {{ $alarm->severity === 'CRITICAL' ? 'border-red-800' :
       ($alarm->severity === 'WARNING'  ? 'border-yellow-800' : 'border-gray-700') }}">
    <span class="text-xs font-bold mt-0.5 px-2 py-0.5 rounded
        {{ $alarm->severity === 'CRITICAL' ? 'bg-red-900 text-red-300' :
           ($alarm->severity === 'WARNING'  ? 'bg-yellow-900 text-yellow-300' : 'bg-gray-700 text-gray-300') }}">
        {{ $alarm->severity }}
    </span>
    <div>
        <p class="text-sm text-gray-200">{{ $alarm->message }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $alarm->created_at->diffForHumans() }}</p>
    </div>
</div>