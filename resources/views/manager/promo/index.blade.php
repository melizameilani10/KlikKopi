@extends('layouts.admin')

@section('title', 'Laporan Promo')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="promo" />
@endsection

@section('top-actions')
    <a href="{{ route('manager.promo.export', request()->query()) }}" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Export CSV
    </a>
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2 text-sm font-bold text-pk-brown">Cetak</button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Laporan Promo</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Periode {{ $filters['dari'] }} s.d. {{ $filters['sampai'] }} • Kinerja item paket &amp; bundling dari database.</p>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.metric-card :value="$summary['qty'].' porsi'" label="Item Promo Terjual" icon="receipt" tone="green" />
        <x-admin.metric-card :value="'Rp '.number_format($summary['omzet'], 0, ',', '.')" label="Omzet Promo" icon="banknote" tone="brown" />
        <x-admin.metric-card :value="count($rows).' item'" label="Item Promo Aktif" icon="star" tone="amber" />
    </div>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Promo aktif">
        <h2 class="font-heading text-lg font-semibold text-pk-brown">Promo Aktif di Katalog Customer</h2>
        <div class="mt-2 flex flex-wrap items-center gap-3 rounded-xl bg-pk-sand-2 p-4">
            <span class="rounded-full bg-pk-green px-3 py-1 text-xs font-bold text-white">{{ $promo['badge'] }}</span>
            <div class="min-w-0 flex-1">
                <p class="font-heading text-base font-semibold text-pk-brown">{{ $promo['title'] }}</p>
                <p class="truncate text-sm text-pk-brown-soft">{{ $promo['desc'] }}</p>
            </div>
            <p class="text-sm font-bold text-pk-brown">Rp {{ number_format($promo['price'], 0, ',', '.') }} <span class="font-semibold text-pk-brown-soft line-through">Rp {{ number_format($promo['old_price'], 0, ',', '.') }}</span></p>
        </div>
    </section>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Filter dan tabel promo">
        <form method="GET" action="{{ route('manager.promo.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
            <label class="text-xs font-bold text-pk-brown-soft">Dari
                <input type="date" name="dari" value="{{ $filters['dari'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="text-xs font-bold text-pk-brown-soft">Sampai
                <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2 text-sm font-bold text-white">Filter</button>
                <a href="{{ route('manager.promo.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
            </div>
        </form>

        @if (empty($rows))
            <div class="mt-4">
                <x-admin.empty-state title="Belum ada item promo" hint="Tambahkan produk kategori Paket/Promo yang aktif di Kelola Menu (panel Admin)." />
            </div>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Item Promo</th>
                            <th class="pb-2 pr-3">Kategori</th>
                            <th class="pb-2 pr-3 text-right">Harga</th>
                            <th class="pb-2 pr-3 text-right">Qty Terjual</th>
                            <th class="pb-2 text-right">Omzet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="py-2.5 pr-3 font-semibold text-pk-brown">{{ $r['nama'] }}</td>
                                <td class="py-2.5 pr-3 text-pk-brown-soft">{{ $r['kategori'] }}</td>
                                <td class="py-2.5 pr-3 text-right">Rp {{ number_format($r['harga'], 0, ',', '.') }}</td>
                                <td class="py-2.5 pr-3 text-right font-bold text-pk-green">{{ $r['qty'] }}</td>
                                <td class="py-2.5 text-right font-bold">Rp {{ number_format($r['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
