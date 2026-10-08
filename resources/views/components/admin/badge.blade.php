{{-- Badge status admin generik. Props: $status --}}
@props(['status' => ''])

@php
    $map = [
        'AKTIF' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'AMAN' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'BERHASIL' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'AUTH_OK' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'LIVE' => 'bg-[#e7eee5] text-[#195905] border-[#c8dcc4]',
        'TERSEDIA' => 'bg-[#eef2f7] text-[#3d546e] border-[#d3dfee]',
        'ACTIVE' => 'bg-[#eef2f7] text-[#3d546e] border-[#d3dfee]',
        'MENIPIS' => 'bg-[#fdf0e1] text-[#9a5b14] border-[#f0d9b8]',
        'SEGERA RESTOCK' => 'bg-[#fdf0e1] text-[#9a5b14] border-[#f0d9b8]',
        'SEGERA ORDER' => 'bg-[#fdf0e1] text-[#9a5b14] border-[#f0d9b8]',
        'TERISI' => 'bg-[#f0eedf] text-[#65601e] border-[#ddd9b8]',
        'HABIS' => 'bg-[#fbe9e7] text-[#b3261e] border-[#f3c4c0]',
        'NONAKTIF' => 'bg-[#f0eee7] text-[#705a4c] border-[#ddd5c5]',
        'GAGAL' => 'bg-[#fbe9e7] text-[#b3261e] border-[#f3c4c0]',
    ];
    $tone = $map[strtoupper($status)] ?? 'bg-pk-sand text-pk-brown border-pk-brown/15';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold leading-none $tone"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ strtoupper($status) }}
</span>
