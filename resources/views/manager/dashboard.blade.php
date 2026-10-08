@extends('layouts.admin')

@section('title', 'Dashboard Manajer')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="dashboard" />
@endsection

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Ringkasan Penjualan</h1>
            <p class="mt-1 text-sm text-pk-brown-soft">Periode: {{ $periode }} • Selamat bekerja, {{ $manager['name'] }}</p>
        </div>
        <a href="{{ route('manager.sales.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">
            Laporan Penjualan
            <x-pos.icon name="chevron-right" class="h-4 w-4" />
        </a>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.metric-card :value="'Rp '.number_format($summary['omzet'], 0, ',', '.')" label="Omzet Periode" hint="Total transaksi berhasil" icon="banknote" tone="green" />
        <x-admin.metric-card :value="$summary['count']" label="Transaksi" :hint="'Rata-rata Rp '.number_format($summary['rata'], 0, ',', '.').' /trx'" icon="receipt" tone="brown" />
        <x-admin.metric-card :value="'Rp '.number_format($keluar, 0, ',', '.')" label="Pengeluaran" hint="Periode berjalan" icon="card" tone="amber" />
        <x-admin.metric-card :value="'Rp '.number_format($laba, 0, ',', '.')" label="Estimasi Laba" hint="Omzet − pengeluaran" icon="check" tone="{{ $laba >= 0 ? 'green' : 'red' }}" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card xl:col-span-2" aria-label="Grafik omzet">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">Omzet 7 Hari Terakhir</h2>
            @php $max = max(array_column($series, 'total') + [1]); @endphp
            @if ($max <= 1)
                <p class="mt-3 rounded-xl bg-pk-sand-2 p-6 text-center text-sm font-semibold text-pk-brown-soft">Belum ada transaksi pada 7 hari terakhir.</p>
            @else
                <div class="mt-4 flex h-44 items-end gap-2">
                    @foreach ($series as $d)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                            <span class="text-[11px] font-bold text-pk-brown">{{ $d['total'] > 0 ? number_format($d['total'] / 1000, 0).'rb' : '' }}</span>
                            <div class="w-full max-w-10 rounded-t-lg bg-pk-green" style="height: {{ max($d['total'] / $max * 100, 2) }}%" title="{{ $d['label'] }}: Rp {{ number_format($d['total'], 0, ',', '.') }}"></div>
                            <span class="text-[11px] font-semibold text-pk-brown-soft">{{ $d['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="space-y-4">
            <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Metode pembayaran">
                <h2 class="font-heading text-lg font-semibold text-pk-brown">Per Metode</h2>
                @if (empty($summary['byMethod']))
                    <p class="mt-2 text-sm text-pk-brown-soft">Belum ada data.</p>
                @else
                    <ul class="mt-2 space-y-2">
                        @foreach ($summary['byMethod'] as $m => $t)
                            <li>
                                <div class="flex justify-between text-sm"><span class="font-semibold text-pk-brown">{{ $m }}</span><span class="font-bold">Rp {{ number_format($t, 0, ',', '.') }}</span></div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-pk-sand">
                                    <div class="h-full rounded-full bg-pk-khaki" style="width: {{ $summary['omzet'] > 0 ? $t / $summary['omzet'] * 100 : 0 }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Menu terlaris">
                <h2 class="font-heading text-lg font-semibold text-pk-brown">Menu Terlaris</h2>
                @if (empty($top))
                    <p class="mt-2 text-sm text-pk-brown-soft">Belum ada data penjualan.</p>
                @else
                    <ol class="mt-2 space-y-2">
                        @foreach ($top as $i => $t)
                            <li class="flex items-center gap-2.5">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-pk-sand text-xs font-bold text-pk-brown">{{ $i + 1 }}</span>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-pk-brown">{{ $t['nama'] }}</span>
                                <span class="shrink-0 text-xs font-bold text-pk-green">{{ $t['qty'] }} terjual</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>
    </div>
@endsection
