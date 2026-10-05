{{-- Tombol POS. Props: $variant (primary|brown|sand|danger|danger-solid|ghost), $icon, $href --}}
@props(['variant' => 'primary', 'icon' => null, 'href' => null])

@php
    $styles = [
        'primary' => 'bg-pk-green text-white hover:bg-pk-green-hover',
        'brown' => 'bg-pk-brown text-white hover:bg-[#2c1f16]',
        'sand' => 'bg-pk-sand text-pk-brown hover:bg-[#e6e2d5]',
        'danger' => 'bg-white text-pk-danger border border-pk-danger/30 hover:bg-[#fdf0ef]',
        'danger-solid' => 'bg-pk-danger text-white hover:bg-[#931d17]',
        'ghost' => 'bg-transparent text-pk-brown hover:bg-pk-sand',
    ];
    $cls = $styles[$variant] ?? $styles['primary'];
    $base = "inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition-colors $cls";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
        @if ($icon)
            <x-pos.icon :name="$icon" class="h-[18px] w-[18px]" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $base]) }}>
        @if ($icon)
            <x-pos.icon :name="$icon" class="h-[18px] w-[18px]" />
        @endif
        {{ $slot }}
    </button>
@endif
