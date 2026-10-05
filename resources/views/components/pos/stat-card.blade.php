{{-- Kartu ringkasan statistik. Props: $title, $value, $hint, $icon, $tone --}}
@props(['title' => '', 'value' => '', 'hint' => null, 'icon' => null, 'tone' => 'default'])

@php
    $iconWrap = match ($tone) {
        'green' => 'bg-pk-green text-white',
        'amber' => 'bg-[#fdf0e1] text-[#9a5b14]',
        'brown' => 'bg-pk-brown text-pk-khaki',
        'khaki' => 'bg-[#f0eedf] text-[#65601e]',
        default => 'bg-pk-sand text-pk-brown',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-pk-brown-soft">{{ $title }}</p>
            <p class="mt-1 truncate font-heading text-[26px] font-semibold leading-none text-pk-brown">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1.5 text-xs text-pk-brown-soft">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $iconWrap }}">
                <x-pos.icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>
</div>
