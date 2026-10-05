@extends('layouts.customer')

@section('title', 'Checkout')

@section('content')
    <x-customer.header :table="$table" title="Checkout" :show-back="true" />

    <form method="POST" action="{{ route('order.checkout.store') }}" class="px-4 pb-6 pt-4">
        @csrf
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Ringkasan pesanan">
            <p class="text-sm font-bold text-pk-brown">{{ $table['label'] }} • {{ $table['type'] }}</p>
            <ul class="mt-2 divide-y divide-pk-brown/10 text-sm">
                @foreach ($cart as $line)
                    <li class="flex justify-between gap-2 py-1.5">
                        <span class="min-w-0">{{ $line['qty'] }}× {{ $line['name'] }}</span>
                        <span class="shrink-0 font-bold text-pk-brown">Rp {{ number_format(App\Support\CustomerData::lineTotal($line), 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="mt-3">
            <x-customer.order-summary :totals="$totals" />
        </div>

        <h2 class="mt-4 text-xs font-bold uppercase tracking-[0.16em] text-pk-brown-soft">Metode Pembayaran</h2>
        <div class="mt-2 grid grid-cols-2 gap-2" role="radiogroup" aria-label="Metode pembayaran">
            @foreach ($methods as $m)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-pk-brown/15 bg-white p-3 has-[:checked]:border-pk-green has-[:checked]:bg-[#eef5ea] has-[:checked]:ring-1 has-[:checked]:ring-pk-green">
                    <input type="radio" name="method" value="{{ $m['key'] }}" class="h-5 w-5 shrink-0 accent-[#195905]" @checked($loop->first)>
                    <span class="min-w-0">
                        <span class="flex items-center gap-1.5 text-sm font-bold text-pk-brown"><x-pos.icon :name="$m['icon']" class="h-4 w-4 text-pk-green" />{{ $m['label'] }}</span>
                        <span class="block truncate text-[11px] text-pk-brown-soft">{{ $m['hint'] }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        @error('method')
            <p class="mt-2 rounded-xl bg-[#fbe9e7] p-3 text-xs font-bold text-pk-danger">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-pk-brown-soft">QRIS &amp; E-Wallet integration-ready: kode bayar dibuat setelah backend payment terhubung. “Bayar Nanti” dicatat dan dibayar di kasir.</p>

        <button type="submit" class="mt-3 w-full rounded-xl bg-pk-green px-4 py-3.5 text-sm font-bold text-white">
            Bayar Rp {{ number_format($totals['total'], 0, ',', '.') }}
        </button>
    </form>

    @section('bottomnav')
        <x-customer.bottom-nav active="cart" :cart-count="$cartCount" />
    @endsection
@endsection
