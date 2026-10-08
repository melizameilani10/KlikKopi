@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="finance" />
@endsection

@section('top-actions')
    <a href="{{ route('manager.finance.export', request()->query()) }}" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Export CSV
    </a>
    <button type="button" data-open-add-keluar class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-green px-4 py-2 text-sm font-bold text-white">
        <x-pos.icon name="plus" class="h-4 w-4" />
        Catat Pengeluaran
    </button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Laporan Keuangan</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Pemasukan tercatat dari transaksi • {{ $filters['dari'] }} s.d. {{ $filters['sampai'] }}</p>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.metric-card :value="'Rp '.number_format($masuk['total'], 0, ',', '.')" label="Total Pemasukan" icon="banknote" tone="green" />
        <x-admin.metric-card :value="'Rp '.number_format($keluar['total'], 0, ',', '.')" label="Total Pengeluaran" icon="card" tone="amber" />
        <x-admin.metric-card :value="'Rp '.number_format($laba, 0, ',', '.')" label="Laba Bersih" icon="check" tone="{{ $laba >= 0 ? 'green' : 'red' }}" />
    </div>

    <form method="GET" action="{{ route('manager.finance.index') }}" class="mt-4 flex flex-col gap-2 sm:flex-row">
        <label class="text-xs font-bold text-pk-brown-soft">Dari
            <input type="date" name="dari" value="{{ $filters['dari'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-white px-3 py-2 text-sm outline-none" />
        </label>
        <label class="text-xs font-bold text-pk-brown-soft">Sampai
            <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-white px-3 py-2 text-sm outline-none" />
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2 text-sm font-bold text-white">Filter</button>
            <a href="{{ route('manager.finance.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
        </div>
    </form>

    <div class="mt-3 grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Pemasukan">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">Pemasukan</h2>
            @if (empty($masuk['rows']))
                <p class="mt-2 rounded-xl bg-pk-sand-2 p-5 text-center text-sm font-semibold text-pk-brown-soft">Belum ada pemasukan pada periode ini.</p>
            @else
                <ul class="mt-2 divide-y divide-pk-brown/10">
                    @foreach ($masuk['rows'] as $r)
                        <li class="flex items-center justify-between gap-2 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-pk-brown">{{ $r['keterangan'] }}</p>
                                <p class="text-xs text-pk-brown-soft">{{ $r['tanggal'] }} • {{ $r['user'] }}</p>
                            </div>
                            <p class="shrink-0 font-heading text-base font-semibold text-pk-green">+Rp {{ number_format($r['jumlah'], 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Pengeluaran">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">Pengeluaran</h2>
            @if (empty($keluar['rows']))
                <p class="mt-2 rounded-xl bg-pk-sand-2 p-5 text-center text-sm font-semibold text-pk-brown-soft">Belum ada pengeluaran pada periode ini.</p>
            @else
                <ul class="mt-2 divide-y divide-pk-brown/10">
                    @foreach ($keluar['rows'] as $r)
                        <li class="flex items-center justify-between gap-2 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-pk-brown">{{ $r['keterangan'] }}</p>
                                <p class="text-xs text-pk-brown-soft">{{ $r['tanggal'] }} • {{ $r['user'] }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <p class="font-heading text-base font-semibold text-pk-danger">−Rp {{ number_format($r['jumlah'], 0, ',', '.') }}</p>
                                <button type="button" data-del-keluar="{{ $r['id'] }}" data-del-label="{{ $r['keterangan'] }}" class="rounded-lg border border-pk-danger/30 px-2 py-1 text-xs font-bold text-pk-danger" aria-label="Hapus">×</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <x-admin.modal id="modal-add-keluar" title="Catat Pengeluaran">
        <form method="POST" action="{{ route('manager.finance.keluar.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <label class="block text-sm font-semibold text-pk-brown">Tanggal
                    <input type="date" name="tanggal" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none" />
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Jumlah (Rp)
                    <input type="number" name="jumlah" required min="1000" step="500" placeholder="50000"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none" />
                </label>
            </div>
            <label class="mt-2 block text-sm font-semibold text-pk-brown">Keterangan
                <input type="text" name="keterangan" required maxlength="255" placeholder="cth: Belanja susu & bahan"
                    class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none" />
            </label>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
                <button type="submit" class="rounded-xl bg-pk-green px-4 py-2.5 text-sm font-bold text-white">Simpan</button>
            </div>
        </form>
    </x-admin.modal>

    <x-admin.modal id="modal-del-keluar" title="Hapus pengeluaran?">
        <p>Data <strong id="del-keluar-label" class="text-pk-brown"></strong> akan dihapus permanen.</p>
        <x-slot:actions>
            <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
            <form id="del-keluar-form" method="POST" action="">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-xl bg-pk-danger px-4 py-2.5 text-sm font-bold text-white">Ya, Hapus</button>
            </form>
        </x-slot:actions>
    </x-admin.modal>

    <script>
        window.MGR_KELUAR_DEL_BASE = @json(url('/manager/keuangan/keluar'));
    </script>
@endsection
