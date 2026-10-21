@extends('layouts.admin')

@section('title', 'Export & Cetak Laporan')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="export" />
@endsection

@section('top-actions')
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2 text-sm font-bold text-pk-brown">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Cetak Halaman
    </button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Pusat Export &amp; Cetak Dokumen</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Unduh laporan dalam format CSV atau cetak halaman (simpan sebagai PDF) untuk periode yang dipilih.</p>
    </div>

    <section class="no-print mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Pilih periode">
        <form method="GET" action="{{ route('manager.export.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
            <label class="text-xs font-bold text-pk-brown-soft">Dari
                <input type="date" name="dari" value="{{ $filters['dari'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <label class="text-xs font-bold text-pk-brown-soft">Sampai
                <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none" />
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2 text-sm font-bold text-white">Terapkan</button>
                <a href="{{ route('manager.export.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
            </div>
        </form>
        <p class="mt-2 text-xs font-semibold text-pk-brown-soft">Periode aktif: {{ $filters['dari'] }} s.d. {{ $filters['sampai'] }}</p>
    </section>

    @php
        $q = ['dari' => $filters['dari'], 'sampai' => $filters['sampai']];
        $reports = [
            [
                'title' => 'Laporan Penjualan Lengkap',
                'desc' => $counts['transaksi'].' transaksi berhasil pada periode ini.',
                'icon' => 'receipt',
                'links' => [
                    ['label' => 'Unduh CSV', 'url' => route('manager.sales.export', $q)],
                    ['label' => 'Lihat Laporan', 'url' => route('manager.sales.index', $q), 'ghost' => true],
                ],
            ],
            [
                'title' => 'Laporan Laba Rugi & Keuangan',
                'desc' => $counts['masuk'].' pemasukan • '.$counts['keluar'].' pengeluaran tercatat.',
                'icon' => 'banknote',
                'links' => [
                    ['label' => 'Unduh CSV', 'url' => route('manager.finance.export', $q)],
                    ['label' => 'Lihat Laporan', 'url' => route('manager.finance.index', $q), 'ghost' => true],
                ],
            ],
            [
                'title' => 'Audit Pajak PB1',
                'desc' => 'Estimasi pajak Rp '.number_format($counts['pajak'], 0, ',', '.').' pada periode ini.',
                'icon' => 'list',
                'links' => [
                    ['label' => 'Lihat & Cetak PB1', 'url' => route('manager.pb1.index', $q), 'ghost' => true],
                ],
            ],
        ];
    @endphp

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        @foreach ($reports as $r)
            <section class="flex flex-col rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="{{ $r['title'] }}">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-pk-mint text-pk-green">
                    <x-pos.icon :name="$r['icon']" class="h-5 w-5" />
                </span>
                <h2 class="mt-3 font-heading text-lg font-semibold text-pk-brown">{{ $r['title'] }}</h2>
                <p class="mt-1 flex-1 text-sm text-pk-brown-soft">{{ $r['desc'] }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($r['links'] as $l)
                        <a href="{{ $l['url'] }}" @class(['inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold', 'bg-pk-green text-white' => empty($l['ghost']), 'border border-pk-brown/15 bg-white text-pk-brown' => ! empty($l['ghost'])])>
                            <x-pos.icon name="printer" class="h-4 w-4" />
                            {{ $l['label'] }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <p class="no-print mt-4 text-xs text-pk-brown-soft">Format CSV dapat dibuka langsung di Excel/Sheets. Untuk PDF, gunakan tombol Cetak lalu pilih “Save as PDF”.</p>
@endsection
