@extends('layouts.admin')

@section('title', 'Kelola Menu')

@section('top-actions')
    <a href="{{ route('admin.menu.create') }}" class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-green px-4 py-2 text-sm font-bold text-white hover:bg-pk-green-hover">
        <x-pos.icon name="plus" class="h-4 w-4" />
        Tambah Menu Baru
    </a>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Kelola Katalog Menu &amp; Produk</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Kelola produk, harga, kategori, status, varian, dan informasi menu.</p>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-5">
        <x-admin.metric-card :value="$stats['sku']" label="Total SKU" icon="list" />
        <x-admin.metric-card :value="$stats['aktif']" label="Menu Aktif" icon="check" tone="green" />
        <x-admin.metric-card :value="$stats['nonaktif']" label="Habis / Nonaktif" icon="x" tone="red" />
        <x-admin.metric-card :value="$stats['kategori']" label="Kategori" icon="grid" tone="brown" />
        <div class="col-span-2 rounded-2xl bg-pk-brown p-4 text-white shadow-card xl:col-span-1">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-pk-khaki">Menu Terpopuler</p>
            <p class="mt-1 truncate font-heading text-lg font-semibold">{{ $stats['populer'] }}</p>
        </div>
    </div>

    <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Daftar menu">
        <form method="GET" action="{{ route('admin.menu.index') }}" class="flex flex-col gap-2 lg:flex-row">
            <label class="relative flex-1">
                <span class="sr-only">Cari menu</span>
                <x-pos.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-pk-brown-soft" />
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search menu..."
                    class="w-full rounded-xl border border-pk-brown/15 bg-pk-paper py-2.5 pl-10 pr-3 text-sm outline-none focus:border-pk-green" />
            </label>
            <select name="kategori" class="rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm font-semibold text-pk-brown outline-none">
                <option value="Semua">Semua Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected($filters['kategori'] === $c)>{{ $c }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm font-semibold text-pk-brown outline-none">
                <option value="semua">Semua Status</option>
                <option value="aktif" @selected($filters['status'] === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($filters['status'] === 'nonaktif')>Nonaktif</option>
            </select>
            <button type="submit" class="rounded-xl bg-pk-brown px-5 py-2.5 text-sm font-bold text-white">Filter</button>
        </form>

        @if ($menus->isEmpty())
            <div class="mt-4">
                <x-admin.empty-state title="Belum ada menu"
                    hint="Katalog masih kosong. Tambahkan menu pertama, atau jalankan php artisan db:seed --class=DemoKatalogSeeder untuk data contoh."
                    :action-url="route('admin.menu.create')" action-label="Tambah Menu Baru" />
            </div>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Produk</th>
                            <th class="pb-2 pr-3">SKU</th>
                            <th class="pb-2 pr-3">Kategori</th>
                            <th class="pb-2 pr-3 text-right">HPP / Jual</th>
                            <th class="pb-2 pr-3">Varian</th>
                            <th class="pb-2 pr-3">Status</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($menus as $m)
                            <tr class="align-middle">
                                <td class="py-2.5 pr-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-pk-sand font-heading text-base font-semibold text-pk-brown">
                                            {{ strtoupper(mb_substr($m->nama_produk, 0, 1)) }}
                                        </span>
                                        <span>
                                            <span class="block font-bold text-pk-brown">{{ $m->nama_produk }}</span>
                                            <span class="block max-w-[220px] truncate text-xs text-pk-brown-soft">{{ $m->deskripsi }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-2.5 pr-3 font-mono text-xs text-pk-brown-soft">{{ App\Support\AdminData::sku($m->id_produk) }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3">{{ $m->kategori }}</td>
                                <td class="whitespace-nowrap py-2.5 pr-3 text-right">
                                    <span class="block text-xs text-pk-brown-soft">HPP —</span>
                                    <span class="block font-bold text-pk-brown">Rp {{ number_format($m->harga, 0, ',', '.') }}</span>
                                </td>
                                <td class="whitespace-nowrap py-2.5 pr-3 text-xs text-pk-brown-soft">Reguler</td>
                                <td class="whitespace-nowrap py-2.5 pr-3"><x-admin.badge :status="$m->status" /></td>
                                <td class="whitespace-nowrap py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" data-view-menu="{{ $m->id_produk }}" class="rounded-lg bg-pk-sand px-2.5 py-1.5 text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">View</button>
                                        <a href="{{ route('admin.menu.edit', $m->id_produk) }}" class="rounded-lg bg-pk-brown px-2.5 py-1.5 text-xs font-bold text-white">Edit</a>
                                        <form method="POST" action="{{ route('admin.menu.toggle', $m->id_produk) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-pk-brown/20 px-2.5 py-1.5 text-xs font-bold text-pk-brown" title="Toggle status">
                                                {{ $m->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <button type="button" data-delete-menu="{{ $m->id_produk }}" data-menu-name="{{ $m->nama_produk }}" class="rounded-lg border border-pk-danger/30 px-2.5 py-1.5 text-xs font-bold text-pk-danger hover:bg-[#fdf0ef]">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $menus->links() }}</div>
        @endif
    </section>

    {{-- Modal hapus --}}
    <x-admin.modal id="modal-delete-menu" title="Hapus menu?">
        <p>Menu <strong id="delete-menu-name" class="text-pk-brown"></strong> akan dihapus permanen beserta data stoknya.</p>
        <x-slot:actions>
            <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
            <form id="delete-menu-form" method="POST" action="">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-xl bg-pk-danger px-4 py-2.5 text-sm font-bold text-white">Ya, Hapus</button>
            </form>
        </x-slot:actions>
    </x-admin.modal>

    {{-- Modal detail --}}
    <x-admin.modal id="modal-view-menu" title="Detail Menu">
        <dl class="space-y-2" id="view-menu-body"></dl>
        <x-slot:actions>
            <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Tutup</button>
        </x-slot:actions>
    </x-admin.modal>

    <script>
        window.ADMIN_MENUS = @json($menus->items());
        window.ADMIN_MENU_EDIT_BASE = @json(url('/admin/menu'));
    </script>
@endsection
