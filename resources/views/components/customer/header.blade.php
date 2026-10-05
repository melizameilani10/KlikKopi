{{-- Header customer. Props: $table, $title?, $showBack? --}}
@props(['table' => [], 'title' => null, 'showBack' => false])

<header class="sticky top-0 z-20 border-b border-pk-brown/10 bg-pk-bg/95 backdrop-blur">
    <div class="mx-auto flex w-full max-w-[480px] items-center gap-2 px-4 py-3">
        @if ($showBack)
            <button type="button" onclick="history.back()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-pk-brown/15 text-pk-brown" aria-label="Kembali">
                <x-pos.icon name="arrow-left" class="h-5 w-5" />
            </button>
        @else
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pk-green font-heading text-lg font-semibold text-white">P</span>
        @endif
        <div class="min-w-0 flex-1">
            @if ($title)
                <p class="truncate font-heading text-lg font-semibold leading-tight text-pk-brown">{{ $title }}</p>
            @else
                <p class="font-heading text-lg font-semibold leading-tight text-pk-brown">PERKOCI EATERY</p>
            @endif
            <p class="flex items-center gap-1 truncate text-xs text-pk-brown-soft">
                <x-pos.icon name="pin" class="h-3.5 w-3.5 shrink-0 text-pk-amber" />
                {{ $table['label'] ?? 'Meja 08' }} • {{ $table['type'] ?? 'Dine-in' }}
                <span class="inline-flex items-center gap-1 font-bold text-pk-green"><x-pos.icon name="wifi" class="h-3.5 w-3.5" />Free WiFi</span>
            </p>
        </div>
        @if (! empty($table['status']))
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-pk-mint px-2.5 py-1 text-[11px] font-bold text-pk-green">
                <span class="h-1.5 w-1.5 rounded-full bg-pk-green"></span>{{ $table['status'] }}
            </span>
        @endif
    </div>
</header>
