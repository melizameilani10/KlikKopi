@extends('layouts.admin')

@section('title', 'Kelola QR Meja')

@section('top-actions')
    <button type="button" data-open-add-table class="no-print inline-flex items-center gap-2 rounded-xl bg-pk-green px-4 py-2 text-sm font-bold text-white hover:bg-pk-green-hover">
        <x-pos.icon name="plus" class="h-4 w-4" />
        Tambah Meja
    </button>
@endsection

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Kelola QR Meja</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">{{ $occupancy['terisi'] }} terisi • {{ $occupancy['tersedia'] }} tersedia dari {{ $occupancy['total'] }} meja</p>
    </div>

    @if (empty($tables))
        <div class="mt-4">
            <x-admin.empty-state title="Belum ada meja"
                hint="Tambahkan meja pertama — QR dibuat otomatis dan langsung bisa dipindai customer."
                :action-url="route('admin.menu.index')" action-label="Ke Kelola Menu" />
        </div>
    @else
        <section class="mt-4 rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" aria-label="Daftar meja">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">No Meja</th>
                            <th class="pb-2 pr-3">Area</th>
                            <th class="pb-2 pr-3">Status</th>
                            <th class="pb-2 pr-3">QR Status</th>
                            <th class="pb-2 pr-3">QR Code</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($tables as $t)
                            <tr>
                                <td class="py-2.5 pr-3 font-heading text-base font-semibold text-pk-brown">{{ $t['label'] }}</td>
                                <td class="py-2.5 pr-3 text-pk-brown-soft">{{ $t['area'] }}</td>
                                <td class="py-2.5 pr-3"><x-admin.badge :status="$t['status']" /></td>
                                <td class="py-2.5 pr-3"><x-admin.badge status="ACTIVE" /></td>
                                <td class="py-2.5 pr-3 font-mono text-xs text-pk-brown-soft">{{ $t['qr'] }}</td>
                                <td class="py-2.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" data-qr-view="{{ $t['id'] }}" class="rounded-lg bg-pk-sand px-2.5 py-1.5 text-xs font-bold text-pk-brown">Lihat QR</button>
                                        <form method="POST" action="{{ route('admin.tables.status', $t['id']) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $t['status'] === 'terisi' ? 'tersedia' : 'terisi' }}" />
                                            <button type="submit" class="rounded-lg border border-pk-brown/20 px-2.5 py-1.5 text-xs font-bold text-pk-brown">
                                                {{ $t['status'] === 'terisi' ? 'Kosongkan' : 'Isi' }}
                                            </button>
                                        </form>
                                        <button type="button" data-qr-regen="{{ $t['id'] }}" data-qr-label="{{ $t['label'] }}" class="rounded-lg border border-pk-brown/20 px-2.5 py-1.5 text-xs font-bold text-pk-brown">Regenerate</button>
                                        <button type="button" data-table-delete="{{ $t['id'] }}" data-table-label="{{ $t['label'] }}" class="rounded-lg border border-pk-danger/30 px-2.5 py-1.5 text-xs font-bold text-pk-danger">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <p class="mt-2 text-xs text-pk-brown-soft">Kolom area mengikuti konvensi tampilan (01–12 Indoor AC, 13+ Outdoor Garden) — kolom area belum ada di database.</p>

    {{-- Modal tambah meja --}}
    <x-admin.modal id="modal-add-table" title="Tambah Meja">
        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <label class="block text-sm font-semibold text-pk-brown">No Meja
                    <input type="text" name="no_meja" required maxlength="50" placeholder="cth: 21"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green" />
                </label>
                <label class="block text-sm font-semibold text-pk-brown">Status
                    <select name="status" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none">
                        <option value="tersedia">Tersedia</option>
                        <option value="terisi">Terisi</option>
                    </select>
                </label>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
                <button type="submit" class="rounded-xl bg-pk-green px-4 py-2.5 text-sm font-bold text-white">Simpan</button>
            </div>
        </form>
    </x-admin.modal>

    {{-- Modal QR --}}
    <div id="modal-qr" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-label="QR Meja">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-card">
            <h3 id="qr-title" class="font-heading text-xl font-semibold text-pk-brown">QR Meja</h3>
            <img id="qr-image" src="" alt="QR Code Meja" class="mx-auto mt-3 h-52 w-52 rounded-xl border border-pk-brown/15 object-contain" />
            <p id="qr-payload" class="mt-2 font-mono text-xs text-pk-brown-soft"></p>
            <p class="mt-1 text-xs text-pk-brown-soft">Pindai untuk membuka self-order meja ini.</p>
            <div class="mt-4 grid grid-cols-3 gap-2">
                <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-3 py-2.5 text-sm font-bold text-pk-brown">Tutup</button>
                <button type="button" id="qr-copy" class="rounded-xl border border-pk-brown/20 px-3 py-2.5 text-sm font-bold text-pk-brown">Salin Link</button>
                <a id="qr-download" href="#" target="_blank" rel="noopener" class="rounded-xl bg-pk-brown px-3 py-2.5 text-sm font-bold text-white">Download</a>
            </div>
        </div>
    </div>

    {{-- Modal regenerate --}}
    <x-admin.modal id="modal-regen" title="Regenerate QR?">
        <p>QR lama <strong id="regen-label" class="text-pk-brown"></strong> tidak akan berlaku lagi.</p>
        <x-slot:actions>
            <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
            <form id="regen-form" method="POST" action="">
                @csrf
                <button type="submit" class="rounded-xl bg-pk-brown px-4 py-2.5 text-sm font-bold text-white">Ya, Buat Baru</button>
            </form>
        </x-slot:actions>
    </x-admin.modal>

    {{-- Modal hapus --}}
    <x-admin.modal id="modal-delete-table" title="Hapus meja?">
        <p>Meja <strong id="delete-table-label" class="text-pk-brown"></strong> akan dihapus permanen.</p>
        <x-slot:actions>
            <button type="button" data-modal-close class="rounded-xl bg-pk-sand px-4 py-2.5 text-sm font-bold text-pk-brown">Batal</button>
            <form id="delete-table-form" method="POST" action="">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-xl bg-pk-danger px-4 py-2.5 text-sm font-bold text-white">Ya, Hapus</button>
            </form>
        </x-slot:actions>
    </x-admin.modal>

    <script>
        window.ADMIN_TABLES = @json($tables);
        window.ADMIN_TABLE_REGEN_BASE = @json(url('/admin/tables'));
    </script>
@endsection
