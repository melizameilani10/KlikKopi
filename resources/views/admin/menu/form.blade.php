@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Tambah Menu' : 'Edit Menu')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">{{ $mode === 'create' ? 'Tambah Menu Baru' : 'Edit Menu' }}</h1>
            <p class="mt-1 text-sm text-pk-brown-soft">{{ $mode === 'create' ? 'Tambahkan produk baru ke katalog.' : ($product->nama_produk.' • '.App\Support\AdminData::sku($product->id_produk)) }}</p>
        </div>
        <a href="{{ route('admin.menu.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2 text-sm font-bold text-pk-brown">
            <x-pos.icon name="arrow-left" class="h-4 w-4" />
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ $mode === 'create' ? route('admin.menu.store') : route('admin.menu.update', $product->id_produk) }}" class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card xl:col-span-2" aria-label="Form produk">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">Informasi Produk</h2>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="block text-sm font-semibold text-pk-brown sm:col-span-2">Nama Produk
                    <input type="text" name="nama_produk" required maxlength="255" value="{{ old('nama_produk', $product->nama_produk ?? '') }}"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Kategori
                    <input type="text" name="kategori" required maxlength="100" list="kategori-list" value="{{ old('kategori', $product->kategori ?? '') }}"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                    <datalist id="kategori-list">
                        @foreach ($categories as $c)
                            <option value="{{ $c }}"></option>
                        @endforeach
                    </datalist>
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Status
                    <select name="status" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green">
                        <option value="aktif" @selected(old('status', $product->status ?? 'aktif') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status', $product->status ?? '') === 'nonaktif')>Nonaktif</option>
                    </select>
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Harga Jual (Rp)
                    <input type="number" id="harga-jual" name="harga" required min="0" step="500" value="{{ old('harga', $product->harga ?? '') }}"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Estimasi HPP (Rp)
                    <input type="number" id="harga-hpp" min="0" step="500" placeholder="Opsional — simulasi margin saja"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                </label>
                <label class="block text-sm font-semibold text-pk-brown sm:col-span-2">Deskripsi
                    <textarea name="deskripsi" rows="3" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green">{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>
                </label>
                @if ($mode === 'create')
                    <label class="block text-sm font-semibold text-pk-brown">Stok Awal
                        <input type="number" name="stok_awal" min="0" value="{{ old('stok_awal', 0) }}"
                            class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                    </label>
                    <label class="block text-sm font-semibold text-pk-brown">Stok Minimum
                        <input type="number" name="min_stok" min="0" value="{{ old('min_stok', 0) }}"
                            class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                    </label>
                @endif
            </div>

            <h2 class="mt-5 font-heading text-lg font-semibold text-pk-brown">Komposisi Resep</h2>
            <p class="text-xs text-pk-brown-soft">Struktur siap integrasi — menjadi dasar deduksi stok saat modul resep tersedia.</p>
            <ul id="recipe-list" class="mt-2 space-y-2">
                @if ($mode === 'edit')
                    <li class="flex items-center justify-between gap-2 rounded-xl bg-pk-sand-2 px-3 py-2 text-sm">
                        <span class="font-semibold text-pk-brown">House Blend Espresso <span class="font-normal text-pk-brown-soft">• 18 gram</span></span>
                        <button type="button" data-recipe-remove class="text-pk-danger" aria-label="Hapus bahan"><x-pos.icon name="trash" class="h-4 w-4" /></button>
                    </li>
                @endif
            </ul>
            <div class="mt-2 flex gap-2">
                <input type="text" id="recipe-name" placeholder="Nama bahan (cth: Gula Aren)" class="flex-1 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none focus:border-pk-green" />
                <input type="text" id="recipe-qty" placeholder="Takaran" class="w-28 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2 text-sm outline-none focus:border-pk-green" />
                <button type="button" id="recipe-add" class="shrink-0 rounded-xl bg-pk-sand px-4 py-2 text-sm font-bold text-pk-brown">+ Tambah Item</button>
            </div>
        </section>

        <aside class="space-y-4" aria-label="Harga dan margin">
            <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card">
                <h2 class="font-heading text-lg font-semibold text-pk-brown">Harga &amp; Margin</h2>
                <dl class="mt-2 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">Harga Jual</dt><dd id="margin-jual" class="font-bold text-pk-brown">Rp 0</dd></div>
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">Estimasi HPP</dt><dd id="margin-hpp" class="font-bold text-pk-brown">Rp 0</dd></div>
                    <div class="flex justify-between border-t border-pk-brown/10 pt-2"><dt class="text-pk-brown-soft">Proyeksi Laba Kotor</dt><dd id="margin-profit" class="font-heading text-base font-semibold text-pk-green">Rp 0 / cup</dd></div>
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">Margin</dt><dd id="margin-pct" class="font-bold text-pk-green">0%</dd></div>
                </dl>
                <p class="mt-2 text-[11px] text-pk-brown-soft">HPP hanya simulasi di browser dan tidak disimpan — kolom HPP belum ada di database.</p>
            </section>

            @if ($mode === 'edit' && isset($stok))
                <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card">
                    <h2 class="font-heading text-lg font-semibold text-pk-brown">Stok Saat Ini</h2>
                    <p class="mt-1 font-heading text-2xl font-semibold text-pk-brown">{{ $stok->jumlah_stok }} <span class="text-sm font-normal text-pk-brown-soft">(min {{ $stok->min_stok }})</span></p>
                    <a href="{{ route('admin.stocks.index') }}" class="mt-2 block rounded-xl bg-pk-brown px-4 py-2.5 text-center text-sm font-bold text-white">Kelola di Gudang</a>
                </section>
            @endif

            <button type="submit" class="w-full rounded-xl bg-pk-green px-4 py-3 text-sm font-bold text-white hover:bg-pk-green-hover">
                {{ $mode === 'create' ? 'Simpan Menu' : 'Simpan Perubahan' }}
            </button>
        </aside>
    </form>
@endsection
