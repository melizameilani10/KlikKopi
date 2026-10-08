@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="sales" />
@endsection

@section('top-actions')
    <a href="{{ route('manager.sales.export', request()->query()) }}" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Export CSV
    </a>
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2 text-sm font-bold text-pk-brown">Cetak</button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Laporan Penjualan</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Transaksi berhasil dari POS kasir &amp; QR meja.</p>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.metric-card :value="'Rp '.number_format($summary['omzet'], 0, ',', '.')" label="Omzet" icon="banknote" tone="green" />
        <x-admin.metric-card :value="$summary['count']" label="Transaksi" icon="receipt" tone="brown" />
        <x-admin.metric-card :value="'Rp '.number_format($summary['rata'], 0, ',', '.')" label="Rata-rata / Transaksi" icon="check" />
    </div>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Filter dan tabel penjualan">
        <form method="GET" action="{{ route('manager.sales.index') }}" class="flex flex-col gap-2 lg:flex-row">
            <label class="text-xs font-bold text-pk-brown-soft">Dari
                <input type="date" name="dari" value="{{ $filters['dari'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="text-xs font-bold text-pk-brown-soft">Sampai
                <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="text-xs font-bold text-pk-brown-soft">Metode
                <select name="metode" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none">
                    <option value="">Semua</option>
                    @foreach ($methods as $m)
                        <option value="{{ $m }}" @selected($filters['metode'] === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2 text-sm font-bold text-white">Filter</button>
                <a href="{{ route('manager.sales.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
            </div>
        </form>

        @if (empty($rows))
            <div class="mt-4">
                <x-admin.empty-state title="Belum ada transaksi" hint="Tidak ada transaksi berhasil pada periode/metode yang dipilih." />
            </div>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Waktu</th>
                            <th class="pb-2 pr-3">Kode</th>
                            <th class="pb-2 pr-3">Antrean</th>
                            <th class="pb-2 pr-3">Meja</th>
                            <th class="pb-2 pr-3">Metode</th>
                            <th class="pb-2 pr-3">Kasir</th>
                            <th class="pb-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="whitespace-nowrap py-2.5 pr-3 text-xs text-pk-brown-soft">{{ $r['waktu'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3 font-mono text-xs">{{ $r['kode'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3 font-bold text-pk-brown">{{ $r['antrean'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3">{{ $r['meja'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3">{{ $r['metode'] }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3">{{ $r['kasir'] }}</td>
                                <td class="whitespace-nowrap py-2.5 text-right font-bold text-pk-brown">Rp {{ number_format($r['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
