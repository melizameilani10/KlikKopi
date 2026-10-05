{{-- Okupansi meja. Props: $tables (array), $occupancy (array) --}}
@props(['tables' => [], 'occupancy' => []])

@php
    $cell = [
        'occupied' => 'bg-pk-brown text-white border-pk-brown',
        'pending' => 'bg-[#fdf0e1] text-[#9a5b14] border-[#f0d9b8]',
        'reserved' => 'bg-white text-pk-brown-soft border-dashed border-pk-brown/30',
        'free' => 'bg-white text-pk-brown border-pk-brown/15',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card']) }}>
    <div class="flex items-center justify-between gap-2">
        <h3 class="font-heading text-base font-semibold text-pk-brown">Okupansi Meja</h3>
        <p class="text-xs font-semibold text-pk-brown-soft">{{ $occupancy['occupied'] ?? 0 }}/{{ $occupancy['total'] ?? 20 }} terisi</p>
    </div>

    <div class="mt-3 grid grid-cols-5 gap-2">
        @foreach ($tables as $t)
            <div class="flex aspect-square items-center justify-center rounded-lg border text-xs font-bold {{ $cell[$t['status']] ?? $cell['free'] }}" title="Meja {{ $t['no'] }} — {{ $t['status'] }}">
                {{ $t['no'] }}
            </div>
        @endforeach
    </div>

    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-semibold text-pk-brown-soft">
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-pk-brown"></span>Terisi</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#e8a94e]"></span>Menunggu bayar</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm border border-dashed border-pk-brown/40 bg-white"></span>Reservasi</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm border border-pk-brown/20 bg-white"></span>Kosong</span>
    </div>

    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-pk-brown/10 pt-3 text-xs">
        <div class="rounded-lg bg-pk-sand-2 px-3 py-2">
            <p class="font-semibold text-pk-brown-soft">Outdoor</p>
            <p class="font-bold text-pk-brown">{{ $occupancy['outdoor'] ?? '-' }}</p>
        </div>
        <div class="rounded-lg bg-pk-sand-2 px-3 py-2">
            <p class="font-semibold text-pk-brown-soft">Indoor</p>
            <p class="font-bold text-pk-brown">{{ $occupancy['indoor'] ?? '-' }}</p>
        </div>
    </div>
</div>
