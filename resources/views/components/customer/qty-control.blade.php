{{-- Kontrol quantity. Props: $key, $qty --}}
@props(['key' => '', 'qty' => 1])

<div class="inline-flex items-center gap-1 rounded-xl border border-pk-brown/15 p-1" data-qty-control="{{ $key }}">
    <button type="button" data-qty-dec="{{ $key }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-pk-brown hover:bg-pk-sand" aria-label="Kurangi">
        <x-pos.icon name="minus" class="h-4 w-4" />
    </button>
    <span data-qty-val="{{ $key }}" class="w-6 text-center text-sm font-bold text-pk-brown">{{ $qty }}</span>
    <button type="button" data-qty-inc="{{ $key }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-pk-green text-white" aria-label="Tambah">
        <x-pos.icon name="plus" class="h-4 w-4" />
    </button>
</div>
