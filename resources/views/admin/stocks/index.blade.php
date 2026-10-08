@extends('layouts.admin')

@section('title', 'Kelola Stok')

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Kelola Stok</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Pantau bahan, jumlah, batas minimum, dan lakukan restock.</p>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
        <x-admin.metric-card :value="$counts['total']" label="Total Bahan" icon="list" />
        <x-admin.metric-card :value="$counts['aman']" label="Stok Aman" icon="check" tone="green" />
        <x-admin.metric-card :value="$counts['menipis']" label="Stok Menipis" icon="alert" tone="amber" />
        <x-admin.metric-card :value="$counts['habis']" label="Stok Habis" icon="x" tone="red" />
    </div>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Tabel stok">
        <form method="GET" action="{{ route('admin.stocks.index') }}" class="flex flex-wrap items-center gap-2">
            <p class="text-sm font-bold text-pk-brown">Filter:</p>
            @foreach (['semua' => 'Semua', 'AMAN' => 'Aman', 'MENIPIS' => 'Menipis', 'HABIS' => 'Habis'] as $k => $l)
                <button type="submit" name="status" value="{{ $k }}"
                    class="rounded-full border px-4 py-1.5 text-xs font-bold {{ $filter === $k ? 'border-pk-green bg-pk-green text-white' : 'border-pk-brown/15 text-pk-brown hover:border-pk-green' }}">{{ $l }}</button>
            @endforeach
        </form>

        @if (empty($rows))
            <div class="mt-4">
                <x-admin.empty-state title="Belum ada data stok"
                    hint="Stok tercatat per produk. Tambahkan menu beserta stok awal, atau jalankan seeder demo."
                    :action-url="route('admin.menu.create')" action-label="Tambah Menu + Stok" />
            </div>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Bahan</th>
                            <th class="pb-2 pr-3">Kategori</th>
                            <th class="pb-2 pr-3 text-right">Stok</th>
                            <th class="pb-2 pr-3">Satuan</th>
                            <th class="pb-2 pr-3 text-right">Minimum</th>
                            <th class="pb-2 pr-3">Status</th>
                            <th class="pb-2 pr-3">Update Terakhir</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="py-2.5 pr-3 font-bold text-pk-brown">{{ $r['nama'] }}</td>
                                <td class="py-2.5 pr-3 text-pk-brown-soft">{{ $r['kategori'] }}</td>
                                <td class="py-2.5 pr-3 text-right font-heading text-base font-semibold text-pk-brown">{{ $r['jumlah'] }}</td>
                                <td class="py-2.5 pr-3 text-pk-brown-soft">pcs</td>
                                <td class="py-2.5 pr-3 text-right">{{ $r['min'] }}</td>
                                <td class="py-2.5 pr-3"><x-admin.badge :status="$r['status']" /></td>
                                <td class="whitespace-nowrap py-2.5 pr-3 text-xs text-pk-brown-soft">{{ $r['updated'] ? \Carbon\Carbon::parse($r['updated'])->translatedFormat('d M Y H:i') : '-' }}</td>
                                <td class="py-2.5 text-right">
                                    <button type="button" data-restock="{{ $r['id_stok'] }}" data-restock-name="{{ $r['nama'] }}"
                                        class="rounded-lg bg-pk-green px-3 py-1.5 text-xs font-bold text-white hover:bg-pk-green-hover">+ Restock</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <x-admin.modal id="modal-restock" title="Restock Bahan">
        <p>Tambah stok untuk <strong id="restock-name" class="text-pk-brown"></strong>.</p>
        <form id="restock-form" method="POST" action="{{ route('admin.stocks.restock') }}" class="mt-3">
            @csrf
            <input type="hidden" name="id_stok" id="restock-id" />
            <label class="block text-sm font-semibold text-pk-brown">Jumlah masuk
                <input type="number" name="qty" required min="1" max="100000" value="10"
                    class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
            </label>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
                <button type="submit" class="rounded-xl bg-pk-green px-4 py-2.5 text-sm font-bold text-white">Simpan Restock</button>
            </div>
        </form>
    </x-admin.modal>
@endsection
