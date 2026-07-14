<div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
    <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">{{ $label }}</p>
    <p class="text-3xl font-mono font-bold text-white">
        <span id="{{ $id }}">{{ $value !== null ? number_format($value, 2) : '—' }}</span>
        <span class="text-base text-gray-500 font-normal ml-1">{{ $unit }}</span>
    </p>
</div>