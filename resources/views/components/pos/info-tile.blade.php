{{-- Tile info kecil (shift / printer / drawer). Props: $eyebrow, $title, $subtitle, $dot, $icon --}}
@props(['eyebrow' => '', 'title' => '', 'subtitle' => null, 'dot' => 'bg-pk-green', 'icon' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card']) }}>
    <div class="flex items-center gap-2">
        @if ($icon)
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-pk-sand text-pk-brown">
                <x-pos.icon :name="$icon" class="h-[18px] w-[18px]" />
            </span>
        @endif
        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-pk-brown-soft">{{ $eyebrow }}</p>
        <span class="ml-auto h-2 w-2 rounded-full {{ $dot }}"></span>
    </div>
    <p class="mt-2 font-heading text-lg font-semibold leading-tight text-pk-brown">{{ $title }}</p>
    @if ($subtitle)
        <p class="mt-0.5 text-xs text-pk-brown-soft">{{ $subtitle }}</p>
    @endif
</div>
