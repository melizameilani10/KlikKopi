{{-- Badge status pesanan. Props: $status (key), $label --}}
@props(['status' => 'waiting_payment', 'label' => ''])

@php
    $map = [
        'waiting_payment' => 'bg-[#fdf0e1] text-[#9a5b14] border-[#f0d9b8]',
        'cooking' => 'bg-[#f0eedf] text-[#65601e] border-[#ddd9b8]',
        'ready' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'new_qr' => 'bg-[#eef2f7] text-[#3d546e] border-[#d3dfee]',
        'done' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'cancelled' => 'bg-[#fbe9e7] text-[#b3261e] border-[#f3c4c0]',
        'paid' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
    ];
    $tone = $map[$status] ?? 'bg-pk-sand text-pk-brown border-pk-brown/15';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold leading-none $tone"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $label }}
</span>
