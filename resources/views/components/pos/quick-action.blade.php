{{-- Aksi cepat dashboard. Props: $title, $description, $icon, $shortcut, $active, $action --}}
@props(['title' => '', 'description' => '', 'icon' => 'plus', 'shortcut' => null, 'active' => false, 'action' => ''])

<button type="button" data-quick-action="{{ $action }}"
    {{ $attributes->merge(['class' => 'flex w-full items-center gap-3 rounded-2xl border p-3 text-left transition-colors '.($active ? 'border-pk-green bg-[#eef5ea]' : 'border-pk-brown/10 bg-white hover:border-pk-green/50')]) }}>
    <span @class([
        'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
        'bg-pk-green text-white' => $active,
        'bg-pk-sand text-pk-brown' => ! $active,
    ])>
        <x-pos.icon :name="$icon" class="h-5 w-5" />
    </span>
    <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-bold text-pk-brown">{{ $title }}</span>
        <span class="block truncate text-xs text-pk-brown-soft">{{ $description }}</span>
    </span>
    @if ($shortcut)
        <kbd class="hidden shrink-0 rounded-md border border-pk-brown/15 bg-white px-1.5 py-0.5 text-[11px] font-bold text-pk-brown-soft sm:inline-block">{{ $shortcut }}</kbd>
    @endif
</button>
