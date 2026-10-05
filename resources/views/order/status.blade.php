@extends('layouts.customer')

@section('title', 'Status Pesanan')

@section('content')
    <x-customer.header :table="$table" title="Status Pesanan" />

    <div class="px-4 pb-6 pt-4">
        @if (session('success'))
            <div class="flex items-start gap-2 rounded-2xl border border-pk-green/30 bg-[#eef5ea] p-3.5 text-sm font-semibold text-pk-green" role="status">
                <x-pos.icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (empty($order))
            <div class="mt-3 rounded-2xl border border-dashed border-pk-brown/25 bg-white p-8 text-center">
                <x-pos.icon name="receipt" class="mx-auto h-10 w-10 text-pk-brown-soft" />
                <p class="mt-2 font-heading text-lg font-semibold text-pk-brown">Belum ada pesanan aktif</p>
                <p class="mt-1 text-sm text-pk-brown-soft">Pesanan dari {{ $table['label'] }} akan terlacak di sini.</p>
                <a href="{{ route('order.menu') }}" class="mt-4 block rounded-xl bg-pk-green px-4 py-3 text-sm font-bold text-white">Mulai Pesan</a>
            </div>
        @else
            <section class="mt-3 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Info pesanan">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-heading text-xl font-semibold text-pk-brown">{{ $order['code'] }}</p>
                        <p class="text-xs text-pk-brown-soft">{{ $table['label'] }} • {{ $order['method_label'] }} • {{ $order['time'] }}</p>
                    </div>
                    @if ($order['paid'])
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-pk-mint px-2.5 py-1 text-[11px] font-bold text-pk-green">Lunas</span>
                    @else
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#fdf0e1] px-2.5 py-1 text-[11px] font-bold text-[#9a5b14]">Bayar di Kasir</span>
                    @endif
                </div>
                <p class="mt-2 font-heading text-2xl font-semibold text-pk-brown">Rp {{ number_format($order['totals']['total'], 0, ',', '.') }}</p>
            </section>

            <section class="mt-3 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Timeline">
                <x-customer.status-timeline :step="$order['step']" :paid="$order['paid']" />
            </section>

            <div class="mt-3 space-y-2">
                <a href="{{ route('order.menu') }}" class="block rounded-xl bg-pk-green px-4 py-3 text-center text-sm font-bold text-white">+ Tambah Pesanan Lain</a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('order.receipt') }}" class="block rounded-xl border border-pk-brown/20 bg-white px-4 py-3 text-center text-sm font-bold text-pk-brown">Lihat E-Receipt</a>
                    <button type="button" id="btn-waiter" class="rounded-xl border border-pk-brown/20 bg-white px-4 py-3 text-sm font-bold text-pk-brown">Panggil Waiter</button>
                </div>
            </div>

            <x-customer.modal id="modal-waiter" title="Panggil Pelayan / Waiter">
                <p>Konfirmasi memanggil pelayan ke <strong class="text-pk-brown">{{ $table['label'] }}</strong>?</p>
                <x-slot:actions>
                    <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
                    <button type="button" id="waiter-confirm" class="rounded-xl bg-pk-green px-4 py-2.5 text-sm font-bold text-white">Ya, Panggil</button>
                </x-slot:actions>
            </x-customer.modal>
        @endif
    </div>

    @section('bottomnav')
        <x-customer.bottom-nav active="status" :cart-count="$cartCount" />
    @endsection
@endsection
