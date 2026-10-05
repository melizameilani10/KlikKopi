@extends('layouts.pos-app')

@section('title', 'Dashboard Kasir')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Dashboard</h1>
            <p class="mt-1 text-sm text-pk-brown-soft">Selamat bertugas, <strong class="text-pk-brown">{{ $kasir['first_name'] ?? $kasir['name'] }}</strong> — berikut ringkasan shift pagi ini.</p>
        </div>
        <a href="{{ route('kasir.orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#2c1f16]">
            Buka Daftar Antrean
            <x-pos.icon name="chevron-right" class="h-4 w-4" />
        </a>
    </div>

    @if (session('success'))
        <div class="mt-4 flex items-start gap-2 rounded-2xl border border-pk-green/30 bg-[#eef5ea] p-4 text-sm font-semibold text-pk-green" role="status">
            <x-pos.icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Summary --}}
    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <x-pos.stat-card title="Total Pesanan" :value="$stats['total']" hint="+12% vs kemarin" icon="receipt" tone="brown" />
        <x-pos.stat-card title="Menunggu" :value="$stats['waiting']" hint="Perlu tindakan kasir" icon="clock" tone="amber" />
        <x-pos.stat-card title="Sedang Diproses" :value="$stats['cooking']" hint="5 Minuman • 4 Makanan" icon="pot" tone="khaki" />
        <x-pos.stat-card title="Selesai" :value="$stats['done']" hint="Hari ini" icon="check-double" tone="green" />
        <x-pos.stat-card title="Omzet" :value="$stats['revenue_label']" hint="Tunai + QRIS + Debit" icon="banknote" tone="green" class="col-span-2 md:col-span-1" />
    </div>

    {{-- Status shift --}}
    <div class="mt-4">
        <x-pos.shift-status :kasir="$kasir" :printer="$printer" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        {{-- Live queue --}}
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card xl:col-span-2" aria-label="Antrean terbaru">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="font-heading text-lg font-semibold text-pk-brown">Live Queue</h2>
                    <p class="text-xs text-pk-brown-soft">Antrean pesanan terbaru • <span class="font-bold text-pk-green">LIVE TICKET STREAM</span></p>
                </div>
                <a href="{{ route('kasir.orders.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-pk-green hover:underline">
                    Lihat semua
                    <x-pos.icon name="chevron-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                @foreach ($tickets as $t)
                    <article class="rounded-2xl border border-pk-brown/10 bg-pk-paper p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-heading text-lg font-semibold text-pk-brown">#{{ $t['code'] }}</p>
                                <p class="text-xs font-semibold text-pk-brown">{{ $t['table'] }} • {{ $t['customer'] }}</p>
                            </div>
                            <x-pos.order-status-badge :status="$t['status']" :label="$t['status_label']" />
                        </div>
                        <ul class="mt-2 space-y-1 text-xs text-pk-brown-soft">
                            @foreach (array_slice($t['items'], 0, 2) as $it)
                                <li class="flex justify-between gap-2">
                                    <span class="truncate">{{ $it['qty'] }}× {{ $it['name'] }}</span>
                                    <span class="font-semibold text-pk-brown">Rp {{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-2 flex items-center justify-between border-t border-pk-brown/10 pt-2">
                            <span class="text-[11px] font-semibold text-pk-brown-soft">{{ $t['ago'] }}</span>
                            <span class="font-heading text-base font-semibold text-pk-brown">Rp {{ number_format($t['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('kasir.orders.index', ['ticket' => $t['code']]) }}" class="flex-1 rounded-lg bg-pk-sand px-3 py-2 text-center text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">Detail</a>
                            @if (! ($t['paid'] ?? false))
                                <a href="{{ route('kasir.payment.show', $t['code']) }}" class="flex-1 rounded-lg bg-pk-green px-3 py-2 text-center text-xs font-bold text-white hover:bg-pk-green-hover">Terima Pembayaran</a>
                            @else
                                <a href="{{ route('kasir.orders.index', ['ticket' => $t['code']]) }}" class="flex-1 rounded-lg bg-pk-brown px-3 py-2 text-center text-xs font-bold text-white">Kirim ke Dapur</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Kanan: quick actions + occupancy --}}
        <div class="space-y-4">
            <section class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Aksi cepat">
                <h2 class="font-heading text-lg font-semibold text-pk-brown">Aksi Cepat</h2>
                <p class="text-xs text-pk-brown-soft">Shortcut keyboard kasir</p>
                <div class="mt-3 space-y-2">
                    @foreach ($quickActions as $qa)
                        <x-pos.quick-action :title="$qa['title']" :description="$qa['description']" :icon="$qa['icon']" :shortcut="$qa['shortcut']" :active="$qa['active']" :action="$qa['action']" />
                    @endforeach
                </div>
            </section>

            <x-pos.table-occupancy :tables="$tables" :occupancy="$occupancy" />

            <section class="overflow-hidden rounded-2xl bg-pk-brown p-4 text-white shadow-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-pk-khaki">Menu Unggulan</p>
                <p class="mt-1 font-heading text-lg font-semibold leading-snug">{{ $featured['name'] }}</p>
                <p class="mt-1 text-xs text-white/70">{{ $featured['stock'] }}</p>
            </section>
        </div>
    </div>
@endsection
