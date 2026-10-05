{{-- Bottom nav mobile. Props: $active, $cartCount --}}
@props(['active' => 'menu', 'cartCount' => 0])

@php
    $items = [
        ['key' => 'menu', 'label' => 'Menu', 'icon' => 'home', 'route' => 'order.menu'],
        ['key' => 'promo', 'label' => 'Promo', 'icon' => 'star', 'url' => route('order.menu').'#promo'],
        ['key' => 'status', 'label' => 'Status', 'icon' => 'clock', 'route' => 'order.status'],
        ['key' => 'cart', 'label' => 'Keranjang', 'icon' => 'cart-plus', 'route' => 'order.cart', 'badge' => $cartCount],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-pk-brown/10 bg-white shadow-bar" aria-label="Navigasi customer">
    <div class="mx-auto grid w-full max-w-[480px] grid-cols-4">
        @foreach ($items as $it)
            @php $on = $active === $it['key']; @endphp
            <a href="{{ $it['url'] ?? route($it['route']) }}" @if ($on) aria-current="page" @endif
               class="relative flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-bold {{ $on ? 'text-pk-green' : 'text-pk-brown-soft' }}">
                <span class="relative">
                    <x-pos.icon :name="$it['icon']" class="h-6 w-6" />
                    @if (! empty($it['badge']) && $it['badge'] > 0)
                        <span id="nav-cart-badge" class="absolute -right-2 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-pk-danger px-1 text-[10px] font-bold text-white">{{ $it['badge'] }}</span>
                    @endif
                </span>
                {{ $it['label'] }}
                <span class="h-1 w-8 rounded-full {{ $on ? 'bg-pk-green' : 'bg-transparent' }}"></span>
            </a>
        @endforeach
    </div>
</nav>
