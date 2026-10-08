{{-- Kartu menu vertikal (grid 2 kolom). Props: $product --}}
@props(['product' => []])

@php
    $tone = match ($product['category'] ?? '') {
        'Signature Coffee' => 'bg-pk-brown text-pk-khaki',
        'Manual Brew' => 'bg-[#2c1f16] text-[#e8d9c4]',
        'Non-Coffee' => 'bg-pk-green text-white',
        'Makanan Utama' => 'bg-[#9a5b14] text-white',
        default => 'bg-pk-khaki text-pk-brown',
    };
    $initial = strtoupper(mb_substr($product['name'] ?? 'P', 0, 1));
@endphp

<article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm" data-menu-item data-product-id="{{ $product['id'] }}" data-name="{{ strtolower($product['name'].' '.($product['desc'] ?? '')) }}" data-category="{{ $product['category'] ?? '' }}">
    <div class="relative flex h-28 items-center justify-center {{ $tone }}" aria-hidden="true">
        <span class="font-heading text-5xl font-semibold">{{ $initial }}</span>
        @if (! empty($product['rating']))
            <span class="absolute left-2 top-2 inline-flex items-center gap-0.5 rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-bold text-pk-amber">
                <x-customer.icon name="star" class="!h-3 !w-3" />{{ $product['rating'] }}
            </span>
        @endif
        @if ($product['sold_out'])
            <span class="absolute inset-x-0 bottom-0 bg-black/45 py-1 text-center text-[11px] font-bold uppercase tracking-wider text-white">Habis</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-3">
        <h3 class="line-clamp-1 text-sm font-bold text-pk-ink">{{ $product['name'] }}</h3>
        <p class="mt-0.5 line-clamp-2 min-h-8 text-xs text-pk-brown-soft">{{ $product['short'] ?? $product['desc'] }}</p>
        <div class="mt-auto flex items-center justify-between gap-1 pt-2">
            <p class="font-heading text-[15px] font-semibold text-pk-brown">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
            @if ($product['sold_out'])
                <span class="rounded-lg bg-pk-sand px-2.5 py-1.5 text-xs font-bold text-pk-brown-soft">Habis</span>
            @elseif ($product['customizable'])
                <a href="{{ route('order.product', $product['id']) }}" aria-label="Tambah {{ $product['name'] }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-pk-green text-lg font-bold leading-none text-white">+</a>
            @else
                <button type="button" data-quick-add="{{ $product['id'] }}" aria-label="Tambah {{ $product['name'] }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-pk-green text-lg font-bold leading-none text-white">+</button>
            @endif
        </div>
    </div>
</article>
