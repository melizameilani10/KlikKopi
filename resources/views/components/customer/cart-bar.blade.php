{{-- Sticky cart bar. Props: $totals --}}
@props(['totals' => ['count' => 0, 'total' => 0]])

@if (($totals['count'] ?? 0) > 0)
    <div id="cart-bar" class="fixed inset-x-0 bottom-[68px] z-30 px-4" aria-live="polite">
        <div class="mx-auto flex w-full max-w-[448px] items-center gap-3 rounded-2xl bg-pk-brown p-3 pl-4 text-white shadow-bar">
            <div class="min-w-0 flex-1">
                <p id="cart-bar-count" class="text-xs font-semibold text-white/75">{{ $totals['count'] }} Item di Pesanan</p>
                <p id="cart-bar-total" class="font-heading text-lg font-semibold leading-tight">Rp {{ number_format($totals['total'], 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('order.cart') }}" class="shrink-0 rounded-xl bg-pk-green px-4 py-2.5 text-sm font-bold text-white">
                Lihat Keranjang (<span id="cart-bar-badge">{{ $totals['count'] }}</span>)
            </a>
        </div>
    </div>
@endif
