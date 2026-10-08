@extends('layouts.customer')

@section('title', 'E-Receipt')

@section('content')
    <x-customer.header :table="$table" title="Detail Transaksi" :show-back="true" />

    <div class="px-4 pb-6 pt-4">
        <section id="receipt" class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="E-Receipt">
            <p class="text-center font-heading text-lg font-semibold text-pk-brown">PERKOCI EATERY</p>
            <p class="text-center text-[11px] font-bold uppercase tracking-[0.18em] text-pk-brown-soft">Bukti Transaksi Pembayaran Elektronik</p>

            <dl class="mt-3 space-y-1.5 border-y border-dashed border-pk-brown/25 py-3 text-xs">
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Nomor struk</dt><dd class="font-bold text-pk-brown">{{ $order['code'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Waktu transaksi</dt><dd class="font-bold text-pk-brown">{{ $order['time'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Nomor meja</dt><dd class="font-bold text-pk-brown">{{ $order['table']['label'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Kasir / sistem</dt><dd class="font-bold text-pk-brown">{{ $order['cashier'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Metode pembayaran</dt><dd class="font-bold text-pk-brown">{{ $order['method_label'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Reference ID</dt><dd class="font-bold text-pk-brown">{{ $order['ref'] }}</dd></div>
            </dl>

            <ul class="mt-3 space-y-1.5 text-sm">
                @foreach ($order['items'] as $it)
                    <li>
                        <span class="flex justify-between gap-2"><span>{{ $it['qty'] }}× {{ $it['name'] }}</span><span class="font-bold text-pk-brown">Rp {{ number_format(App\Support\CustomerData::lineTotal($it), 0, ',', '.') }}</span></span>
                        @foreach ($it['options'] ?? [] as $o)
                            <span class="block pl-6 text-xs text-pk-brown-soft">• {{ $o['value'] }}</span>
                        @endforeach
                    </li>
                @endforeach
            </ul>

            <dl class="mt-3 space-y-1.5 border-t border-dashed border-pk-brown/25 pt-3 text-sm">
                <div class="flex justify-between text-pk-brown-soft"><dt>Subtotal</dt><dd class="font-semibold text-pk-brown">Rp {{ number_format($order['totals']['subtotal'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between text-pk-brown-soft"><dt>Service</dt><dd class="font-semibold text-pk-brown">Rp {{ number_format($order['totals']['service'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between text-pk-brown-soft"><dt>PB1</dt><dd class="font-semibold text-pk-brown">Rp {{ number_format($order['totals']['pb1'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between font-heading text-lg font-semibold text-pk-brown"><dt>Total Transaksi</dt><dd>Rp {{ number_format($order['totals']['total'], 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-pk-brown-soft">Dibayar</dt><dd class="font-bold text-pk-brown">Rp {{ number_format($order['totals']['total'], 0, ',', '.') }}</dd></div>
            </dl>

            @if ($order['paid'])
                <p class="mt-3 rounded-xl bg-pk-mint p-3 text-center text-sm font-bold text-pk-green">Status Lunas</p>
            @else
                <p class="mt-3 rounded-xl bg-[#fdf0e1] p-3 text-center text-sm font-bold text-[#9a5b14]">Bayar di Kasir — tunjukkan struk ini</p>
            @endif
        </section>

        <div class="mt-3 space-y-2">
            <button type="button" id="btn-receipt-print" class="w-full rounded-xl bg-pk-brown px-4 py-3 text-sm font-bold text-white">Unduh PDF E-Receipt</button>
            <button type="button" id="btn-receipt-wa" data-code="{{ $order['code'] }}" class="w-full rounded-xl border border-pk-brown/20 bg-white px-4 py-3 text-sm font-bold text-pk-brown">Kirim ke WhatsApp</button>
            <a href="{{ route('order.menu') }}" class="block rounded-xl bg-pk-green px-4 py-3 text-center text-sm font-bold text-white">Kembali ke Menu</a>
        </div>
        <p class="mt-2 text-center text-[11px] text-pk-brown-soft">PDF &amp; WhatsApp integration-ready — terhubung otomatis saat backend tersedia.</p>
    </div>

    @section('bottomnav')
        <x-customer.bottom-nav active="status" :cart-count="$cartCount" />
    @endsection
@endsection
