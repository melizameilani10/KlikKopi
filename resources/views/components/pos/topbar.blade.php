{{-- Top header kasir. Props: $kasir (array) --}}
@props(['kasir' => []])

<header class="sticky top-0 z-20 border-b border-pk-brown/10 bg-pk-bg/95 backdrop-blur">
    <div class="mx-auto flex w-full max-w-[1480px] flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3 sm:px-6 xl:px-8">
        <button id="pos-menu-btn" type="button" class="flex h-10 w-10 items-center justify-center rounded-xl border border-pk-brown/15 text-pk-brown lg:hidden" aria-label="Buka navigasi" aria-controls="pos-sidebar">
            <x-pos.icon name="menu" class="h-5 w-5" />
        </button>

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <p class="truncate font-heading text-lg font-semibold text-pk-brown">PERKOCI EATERY</p>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-pk-green px-2.5 py-1 text-[11px] font-bold text-white">
                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    Shift Aktif
                </span>
            </div>
            <p class="truncate text-xs text-pk-brown-soft">{{ $kasir['now_label'] ?? '' }}</p>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-pk-brown/15 bg-white px-3 py-1.5 text-xs font-semibold text-pk-brown">
                <x-pos.icon name="user" class="h-4 w-4 text-pk-green" />
                {{ $kasir['code'] ?? 'Kasir 01' }} • Shift {{ $kasir['shift'] ?? 'Pagi' }}
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-pk-brown/15 bg-white px-3 py-1.5 text-xs font-semibold text-pk-brown">
                <span class="h-2 w-2 rounded-full bg-pk-green"></span>
                Printer Online
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-pk-brown px-3 py-1.5 text-xs font-semibold text-white">
                <x-pos.icon name="register" class="h-4 w-4 text-pk-khaki" />
                Cash Drawer Rp {{ $kasir['drawer'] ?? '500.000' }}
            </span>
        </div>
    </div>
</header>
