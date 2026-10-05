@extends('layouts.customer')

@section('title', 'Keranjang Saya')

@section('content')
    <x-customer.header :table="$table" title="Keranjang Saya" :show-back="true" />

    <div class="px-4 pb-6 pt-4">
        <p class="inline-flex items-center gap-1.5 rounded-full bg-pk-mint px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-pk-green">
            <x-pos.icon name="check" class="h-3.5 w-3.5" />Terverifikasi QR
        </p>
        <p class="mt-2 text-sm text-pk-brown-soft">Pesanan untuk <strong class="text-pk-brown">{{ $table['label'] }}</strong> • {{ $table['area'] }}<br>Sajian akan langsung diantar oleh barista &amp; staf Perkoci ke meja Anda.</p>

        @if (! empty($emptyCheckout))
            <div class="mt-3 rounded-xl bg-[#fdf0e1] p-3 text-xs font-bold text-[#9a5b14]">Keranjang masih kosong — pilih menu dulu sebelum checkout.</div>
        @endif

        @if (empty($cart))
            <div class="mt-4 rounded-2xl border border-dashed border-pk-brown/25 bg-white p-8 text-center">
                <x-pos.icon name="cart-plus" class="mx-auto h-10 w-10 text-pk-brown-soft" />
                <p class="mt-2 font-heading text-lg font-semibold text-pk-brown">Keranjang masih kosong</p>
                <p class="mt-1 text-sm text-pk-brown-soft">Yuk pilih kopi dan makanan favoritmu.</p>
                <a href="{{ route('order.menu') }}" class="mt-4 block rounded-xl bg-pk-green px-4 py-3 text-sm font-bold text-white">Lihat Menu</a>
            </div>
        @else
            <ul id="cart-list" class="mt-3 space-y-2.5">
                @foreach ($cart as $line)
                    <li class="rounded-2xl border border-pk-brown/10 bg-white p-3 shadow-card" data-cart-line="{{ $line['key'] }}">
                        <div class="flex gap-3">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-pk-sand font-heading text-2xl font-semibold text-pk-brown" aria-hidden="true">
                                {{ strtoupper(mb_substr($line['name'], 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-pk-brown">{{ $line['name'] }}</p>
                                @foreach ($line['options'] ?? [] as $o)
                                    <p class="truncate text-xs text-pk-brown-soft">{{ $o['value'] }}</p>
                                @endforeach
                                @if (! empty($line['note']))
                                    <p class="truncate text-xs italic text-pk-brown-soft">“{{ $line['note'] }}”</p>
                                @endif
                                <p class="mt-1 text-sm font-bold text-pk-brown"><span data-line-qty>{{ $line['qty'] }}×</span> <span data-line-total>Rp {{ number_format(App\Support\CustomerData::lineTotal($line), 0, ',', '.') }}</span></p>
                            </div>
                            <button type="button" data-cart-remove="{{ $line['key'] }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-pk-danger" aria-label="Hapus item">
                                <x-pos.icon name="trash" class="h-[18px] w-[18px]" />
                            </button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <x-customer.qty-control :key="$line['key']" :qty="$line['qty']" />
                            <a href="{{ route('order.product', $line['product_id']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-pk-green">
                                <x-pos.icon name="pencil" class="h-3.5 w-3.5" />Edit
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-3">
                <x-customer.order-summary :totals="$totals" />
            </div>

            <a href="{{ route('order.checkout') }}" class="mt-3 block rounded-xl bg-pk-green px-4 py-3.5 text-center text-sm font-bold text-white">
                Lanjut ke Pembayaran • Rp {{ number_format($totals['total'], 0, ',', '.') }}
            </a>
            <a href="{{ route('order.menu') }}" class="mt-2 block rounded-xl border border-pk-brown/20 bg-white px-4 py-3 text-center text-sm font-bold text-pk-brown">+ Tambah Pesanan Lain</a>
        @endif
    </div>

    @section('bottomnav')
        <x-customer.bottom-nav active="cart" :cart-count="$cartCount" />
    @endsection
@endsection
