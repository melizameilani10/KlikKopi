@extends('layouts.admin')

@section('title', 'Audit PB1')

@section('sidebar')
    <x-manager.sidebar :manager="$manager" active="pb1" />
@endsection

@section('top-actions')
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2 text-sm font-bold text-white">
        <x-pos.icon name="printer" class="h-4 w-4" />
        Cetak Rekap PB1
    </button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Audit Pajak PB1</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Estimasi pajak restoran 10% dari total transaksi berhasil • {{ $filters['dari'] }} s.d. {{ $filters['sampai'] }}</p>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.metric-card :value="'Rp '.number_format($pb1['dasar'], 0, ',', '.')" label="Dasar Pengenaan" hint="Total transaksi berhasil" icon="receipt" tone="brown" />
        <x-admin.metric-card :value="$pb1['tarif'].'%'" label="Tarif PB1" hint="Pajak restoran" icon="list" />
        <x-admin.metric-card :value="'Rp '.number_format($pb1['pajak'], 0, ',', '.')" :label="'Estimasi PB1 Terutang ('.$pb1['count'].' trx)'" icon="banknote" tone="green" />
    </div>

    <form method="GET" action="{{ route('manager.pb1.index') }}" class="mt-4 flex flex-col gap-2 sm:flex-row">
        <label class="text-xs font-bold text-pk-brown-soft">Dari
            <input type="date" name="dari" value="{{ $filters['dari'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-white px-3 py-2 text-sm outline-none" />
        </label>
        <label class="text-xs font-bold text-pk-brown-soft">Sampai
            <input type="date" name="sampai" value="{{ $filters['sampai'] }}" class="mt-1 rounded-xl border border-pk-brown/15 bg-white px-3 py-2 text-sm outline-none" />
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2 text-sm font-bold text-white">Filter</button>
            <a href="{{ route('manager.pb1.index') }}" class="rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">Reset</a>
        </div>
    </form>

    <section class="mt-3 rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Rincian harian PB1">
        <h2 class="font-heading text-lg font-semibold text-pk-brown">Rincian Harian</h2>
        @if (empty($pb1['perHari']))
            <p class="mt-2 rounded-xl bg-pk-sand-2 p-6 text-center text-sm font-semibold text-pk-brown-soft">Belum ada transaksi pada periode ini.</p>
        @else
            <div class="mt-2 overflow-x-auto">
                <table class="w-full min-w-[520px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Tanggal</th>
                            <th class="pb-2 pr-3 text-right">Dasar Pengenaan</th>
                            <th class="pb-2 text-right">PB1 (10%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($pb1['perHari'] as $tgl => $total)
                            <tr>
                                <td class="py-2 pr-3">{{ $tgl }}</td>
                                <td class="py-2 pr-3 text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                <td class="py-2 text-right font-bold text-pk-green">Rp {{ number_format($total * 0.10, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="mt-3 text-[11px] text-pk-brown-soft">Kolom PB1 per transaksi belum ada di database — angka di atas estimasi 10% dari total. Simpan rekap ini sebagai acuan pelaporan.</p>
    </section>
@endsection
