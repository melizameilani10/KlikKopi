{{-- Info toko & meja. Props: $table (array) --}}
@props(['table' => []])

<section class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm" aria-label="Info toko dan meja">
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-pk-green font-heading text-xl font-semibold text-white">P</span>
    <div class="min-w-0 flex-1">
        <p class="flex items-center gap-1.5 text-sm font-bold text-pk-ink">
            <span class="h-2 w-2 shrink-0 rounded-full bg-pk-green"></span>
            Buka • 08:00–22:00
        </p>
        <p class="mt-0.5 truncate text-xs text-pk-brown-soft">{{ $table['label'] ?? 'Meja 08' }} • {{ $table['type'] ?? 'Dine-in' }} • Free WiFi</p>
    </div>
    @if (! empty($table['status']))
        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-pk-mint px-2.5 py-1 text-[11px] font-bold text-pk-green">
            {{ $table['status'] }}
        </span>
    @endif
</section>
