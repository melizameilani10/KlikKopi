{{-- Kartu menu. Props: $product --}}
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

<article class="flex gap-3 rounded-2xl border border-pk-brown/10 bg-white p-3 shadow-card" data-menu-item data-name="{{ strtolower($product['name'].' '.($product['desc'] ?? '')) }}" data-category="{{ $product['category'] ?? '' }}">
    <div class="flex h-[72px] w-[72px] shrink-0 items-center justify-center rounded-xl font-heading text-3xl font-semibold {{ $tone }}" aria-hidden="true">
        {{ $initial }}
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
            <h3 class="truncate text-sm font-bold text-pk-brown">{{ $product['name'] }}</h3>
            @if (! empty($product['rating']))
                <span class="inline-flex shrink-0 items-center gap-0.5 text-xs font-bold text-pk-amber">
                    <x-pos.icon name="star" class="h-3.5 w-3.5" />{{ $product['rating'] }}
                </span>
            @endif
        </div>
        <p class="mt-0.5 line-clamp-2 text-xs text-pk-brown-soft">{{ $product['short'] ?? $product['desc'] }}</p>
        <div class="mt-1.5 flex items-center justify-between gap-2">
            <p class="font-heading text-base font-semibold text-pk-brown">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
            @if ($product['sold_out'])
                <span class="rounded-lg bg-pk-sand px-3 py-2 text-xs font-bold text-pk-brown-soft">Habis</span>
            @elseif ($product['customizable'])
                <a href="{{ route('order.product', $product['id']) }}" class="rounded-lg bg-pk-green px-3 py-2 text-xs font-bold text-white">+ Tambah</a>
            @else
                <button type="button" data-quick-add="{{ $product['id'] }}" class="rounded-lg bg-pk-green px-3 py-2 text-xs font-bold text-white">+ Tambah</button>
            @endif
        </div>
    </div>
</article>
