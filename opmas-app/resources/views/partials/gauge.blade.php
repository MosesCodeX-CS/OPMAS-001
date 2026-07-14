<div class="rounded-xl p-5 shadow-sm border" style="background:#FFFFFF; border-color:#DDE3EE;">
    <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#6B7A90;">{{ $label }}</p>
    <p class="text-3xl font-mono font-bold" style="color:#1B3A6B;">
        <span id="{{ $id }}">{{ $value !== null ? number_format($value, 2) : '—' }}</span>
        <span class="text-base font-normal ml-1" style="color:#6B7A90;">{{ $unit }}</span>
    </p>
</div>