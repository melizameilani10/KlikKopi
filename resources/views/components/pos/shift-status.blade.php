{{-- Status shift ringkas (3 tile). Props: $kasir, $printer --}}
@props(['kasir' => [], 'printer' => []])

<div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
    <x-pos.info-tile eyebrow="Shift Aktif" :title="($kasir['code'] ?? 'Kasir 01').' • Shift '.($kasir['shift'] ?? 'Pagi')" :subtitle="$kasir['session'] ?? '07:00 - 15:00 WIB'" dot="bg-pk-green" icon="user" />
    <x-pos.info-tile eyebrow="Printer" :title="$printer['status'] ?? 'Online'" :subtitle="$printer['name'] ?? 'Epson TM-T82'" dot="bg-pk-green" icon="printer" />
    <x-pos.info-tile eyebrow="Cash Drawer" :title="'Rp '.($kasir['drawer'] ?? '500.000')" subtitle="Saldo awal shift" dot="bg-pk-khaki" icon="register" />
</div>
