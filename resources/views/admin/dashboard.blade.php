@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('top-actions')
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white hover:bg-[#2c1f16]">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Unduh Rekap Cepat
    </button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Pusat Kendali Kafe &amp; Dapur</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Ringkasan Operasional &amp; Inventori</p>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.metric-card :value="$metrics['menuAktif']" label="Menu Aktif" :hint="$metrics['menuTotal'].' total SKU di katalog'" icon="receipt" tone="brown" />
        <x-admin.metric-card :value="$metrics['menipis']" label="Stok Menipis" hint="Perlu restock hari ini" icon="alert" tone="amber" />
        <x-admin.metric-card :value="$metrics['habis']" label="Stok Habis" hint="Perlu tindakan segera" icon="x" tone="red" />
        <x-admin.metric-card :value="$metrics['pesanan']" label="Pesanan Masuk" hint="Hari ini • sinkron POS Kasir & QR Meja" icon="cart-plus" tone="green" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card xl:col-span-2" aria-label="Peringatan stok">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="font-heading text-lg font-semibold text-pk-brown">Daftar Peringatan Stok Terendah</h2>
                    <p class="text-xs text-pk-brown-soft">Bahan kritis yang memerlukan pemesanan kembali.</p>
                </div>
                <a href="{{ route('admin.stocks.index') }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-bold text-pk-green hover:underline">
                    Buka Gudang
                    <x-pos.icon name="chevron-right" class="h-4 w-4" />
                </a>
            </div>

            @if (empty($alerts))
                <div class="mt-3 rounded-xl bg-pk-sand-2 p-6 text-center text-sm font-semibold text-pk-brown-soft">
                    @if ($metrics['menuTotal'] === 0)
                        Belum ada data stok. Tambahkan menu &amp; stok lewat Kelola Menu, atau jalankan seeder demo.
                    @else
                        Semua stok dalam kondisi aman.
                    @endif
                </div>
            @else
                <ul class="mt-3 divide-y divide-pk-brown/10">
                    @foreach ($alerts as $a)
                        <li class="flex items-center gap-3 py-2.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pk-sand font-heading text-lg font-semibold text-pk-brown">
                                {{ strtoupper(mb_substr($a['nama'], 0, 1)) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-pk-brown">{{ $a['nama'] }}</p>
                                <p class="text-xs text-pk-brown-soft">{{ $a['kategori'] }} • Sisa {{ $a['jumlah'] }} (min {{ $a['min'] }})</p>
                            </div>
                            <x-admin.badge :status="$a['status'] === 'HABIS' ? 'HABIS' : 'SEGERA ORDER'" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-2xl bg-pk-brown p-5 text-white shadow-card" aria-label="Sinkronisasi sistem">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-heading text-lg font-semibold">Sinkronisasi Sistem</h2>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold text-pk-live">
                    <span class="h-1.5 w-1.5 rounded-full bg-pk-live"></span>Live Sync
                </span>
            </div>
            <dl class="mt-3 space-y-2.5 text-sm">
                <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
                    <dt class="text-white/70">Terminal Kasir Utama</dt>
                    <dd><x-admin.badge status="LIVE" /></dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
                    <dt class="text-white/70">Kasir</dt>
                    <dd class="font-bold">Dimas Aryo</dd>
                </div>
                <div class="flex items-center justify-between border-b border-white/10 pb-2.5">
                    <dt class="text-white/70">Shift</dt>
                    <dd class="font-bold">Siang</dd>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <dt class="text-white/70">Sesi QR Meja</dt>
                        <dd class="font-bold">{{ $occupancy['pct'] }}% Okupansi</dd>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/15">
                        <div class="h-full rounded-full bg-pk-khaki" style="width: {{ $occupancy['pct'] }}%"></div>
                    </div>
                    <p class="mt-1.5 text-xs text-white/60">{{ $occupancy['terisi'] }} terisi • {{ $occupancy['tersedia'] }} tersedia dari {{ $occupancy['total'] }} meja</p>
                </div>
            </dl>
        </section>
    </div>
@endsection
