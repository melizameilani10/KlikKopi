@extends('layouts.customer')

@section('title', $product['name'])

@section('content')
    <x-customer.header :table="$table" :title="'Detail Menu'" :show-back="true" />

    <div class="px-4 pb-6 pt-4" data-product-page="{{ $product['id'] }}" data-base-price="{{ $product['price'] }}">
        <div class="overflow-hidden rounded-2xl border border-pk-brown/10 bg-white shadow-card">
            <div class="flex h-36 items-center justify-center bg-pk-brown" aria-hidden="true">
                <span class="font-heading text-7xl font-semibold text-pk-khaki">{{ strtoupper(mb_substr($product['name'], 0, 1)) }}</span>
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <h1 class="font-heading text-xl font-semibold leading-snug text-pk-brown">{{ $product['name'] }}</h1>
                    @if (! empty($product['rating']))
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-pk-sand px-2 py-1 text-xs font-bold text-pk-amber">
                            <x-pos.icon name="star" class="h-3.5 w-3.5" />{{ $product['rating'] }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 font-heading text-2xl font-semibold text-pk-green">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm leading-relaxed text-pk-brown-soft">{{ $product['desc'] }}</p>
                @if (! empty($product['tags']))
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($product['tags'] as $t)
                            <span class="rounded-full bg-pk-sand px-2.5 py-1 text-[11px] font-bold text-pk-brown">{{ $t }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($product['sold_out'])
            <div class="mt-3 rounded-2xl bg-pk-sand p-5 text-center">
                <p class="font-heading text-lg font-semibold text-pk-brown">Stok Habis</p>
                <p class="mt-1 text-sm text-pk-brown-soft">Menu ini sedang tidak tersedia. Silakan pilih menu lain.</p>
                <a href="{{ route('order.menu') }}" class="mt-3 block rounded-xl bg-pk-brown px-4 py-3 text-sm font-bold text-white">Kembali ke Menu</a>
            </div>
        @else
            <h2 class="mt-4 font-heading text-lg font-semibold text-pk-brown">Detail Menu &amp; Kustomisasi</h2>

            @if ($product['customizable'])
                <div class="mt-2 space-y-2.5">
                    @foreach ($product['options'] as $gi => $g)
                        <x-customer.option-group :group="$g" :index="$gi" />
                    @endforeach
                </div>
            @endif

            <div class="mt-2.5 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card">
                <label for="barista-note" class="text-sm font-bold text-pk-brown">Catatan Khusus untuk Barista</label>
                <textarea id="barista-note" rows="2" maxlength="120" placeholder="Contoh: tolong pisahkan sedotan, buat lebih pekat…"
                    class="mt-1.5 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none placeholder:text-pk-brown-soft/60 focus:border-pk-green"></textarea>
                <p class="mt-1 text-right text-xs text-pk-brown-soft"><span id="note-count">0</span>/120</p>
            </div>

            <div class="mt-2.5 flex items-center gap-2 rounded-2xl border border-pk-brown/10 bg-white p-3 shadow-card">
                <x-pos.icon name="clock" class="h-5 w-5 shrink-0 text-pk-green" />
                <p class="text-xs text-pk-brown-soft"><strong class="text-pk-brown">Estimasi Waktu Seduh: {{ $product['estimate'] }}</strong><br>Dibuat segar per pesanan oleh barista Perkoci Eatery.</p>
            </div>

            <p id="product-error" class="hidden mt-3 rounded-xl bg-[#fbe9e7] p-3 text-xs font-bold text-pk-danger" role="alert"></p>

            <div class="sticky bottom-[76px] mt-3 rounded-2xl bg-pk-brown p-3 text-white shadow-bar">
                <div class="flex items-center gap-3">
                    <div class="inline-flex items-center gap-1 rounded-xl bg-white/10 p-1">
                        <button type="button" id="pdt-dec" class="flex h-9 w-9 items-center justify-center rounded-lg" aria-label="Kurangi"><x-pos.icon name="minus" class="h-4 w-4" /></button>
                        <span id="pdt-qty" class="w-6 text-center text-sm font-bold">1</span>
                        <button type="button" id="pdt-inc" class="flex h-9 w-9 items-center justify-center rounded-lg bg-pk-green" aria-label="Tambah"><x-pos.icon name="plus" class="h-4 w-4" /></button>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] text-white/70"><span id="pdt-qty-label">1</span> Keranjang</p>
                        <p id="pdt-total" class="font-heading text-lg font-semibold leading-tight">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <button type="button" id="pdt-add" data-product-id="{{ $product['id'] }}" class="mt-2 w-full rounded-xl bg-pk-green px-4 py-3 text-sm font-bold text-white">Tambah ke Keranjang</button>
            </div>
        @endif
    </div>

    @section('bottomnav')
        <x-customer.bottom-nav active="menu" :cart-count="$cartCount" />
    @endsection
@endsection
