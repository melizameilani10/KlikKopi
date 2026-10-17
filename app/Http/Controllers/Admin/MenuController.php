<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Support\AdminData;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = (string) $request->query('kategori', 'Semua');
        $status = (string) $request->query('status', 'semua');

        $query = Produk::query()->orderBy('id_produk', 'desc');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_produk', 'like', "%{$q}%")
                    ->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }
        if ($kategori !== 'Semua') {
            $query->where('kategori', $kategori);
        }
        if (in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        $menus = $query->paginate(10)->withQueryString();

        $all = Produk::all();
        $stats = [
            'sku' => $all->count(),
            'aktif' => $all->where('status', 'aktif')->count(),
            'nonaktif' => $all->where('status', 'nonaktif')->count(),
            'kategori' => $all->pluck('kategori')->filter()->unique()->count(),
            'populer' => $all->first()->nama_produk ?? '-',
        ];

        return view('admin.menu.index', [
            'active' => 'menu',
            'admin' => AdminData::adminMeta($request->user()),
            'menus' => $menus,
            'stats' => $stats,
            'categories' => AdminData::categories(),
            'filters' => ['q' => $q, 'kategori' => $kategori, 'status' => $status],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.menu.form', [
            'active' => 'menu',
            'admin' => AdminData::adminMeta($request->user()),
            'product' => null,
            'categories' => AdminData::categories(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'deskripsi' => ['nullable', 'string'],
            'stok_awal' => ['nullable', 'integer', 'min:0'],
            'min_stok' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'nama_produk' => 'nama produk', 'kategori' => 'kategori',
            'harga' => 'harga jual', 'status' => 'status',
            'deskripsi' => 'deskripsi', 'stok_awal' => 'stok awal', 'min_stok' => 'stok minimum',
        ]);

        $produk = Produk::create([
            'nama_produk' => $data['nama_produk'],
            'kategori' => $data['kategori'],
            'harga' => $data['harga'],
            'status' => $data['status'],
            'deskripsi' => $data['deskripsi'] ?? null,
        ]);

        if (! is_null($data['stok_awal'] ?? null)) {
            \DB::table('stok')->insert([
                'id_produk' => $produk->id_produk,
                'jumlah_stok' => (int) $data['stok_awal'],
                'min_stok' => (int) ($data['min_stok'] ?? 0),
                'updated_at' => now(),
            ]);
        }

        AuditTrail::log('KATALOG_MENU', "Tambah menu {$produk->nama_produk}", AdminData::sku($produk->id_produk).' • '.AdminData::rupiah($produk->harga));

        return redirect()->route('admin.menu.index')->with('success', "Menu {$produk->nama_produk} berhasil ditambahkan.");
    }

    public function edit(Request $request, int $id): View
    {
        $product = Produk::findOrFail($id);
        $stok = \DB::table('stok')->where('id_produk', $id)->first();

        return view('admin.menu.form', [
            'active' => 'menu',
            'admin' => AdminData::adminMeta($request->user()),
            'product' => $product,
            'stok' => $stok,
            'categories' => AdminData::categories(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Produk::findOrFail($id);

        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $oldPrice = (float) $product->harga;
        $product->update($data);

        $detail = AdminData::sku($product->id_produk);
        if ((float) $data['harga'] !== $oldPrice) {
            $detail .= ' • '.AdminData::rupiah($oldPrice).' → '.AdminData::rupiah($data['harga']);
        }
        AuditTrail::log('KATALOG_MENU', "Update menu {$product->nama_produk}", $detail);

        return redirect()->route('admin.menu.index')->with('success', "Menu {$product->nama_produk} berhasil diperbarui.");
    }

    public function toggle(Request $request, int $id): RedirectResponse
    {
        $product = Produk::findOrFail($id);
        $product->status = $product->status === 'aktif' ? 'nonaktif' : 'aktif';
        $product->save();

        AuditTrail::log('KATALOG_MENU', "Ubah status {$product->nama_produk} → ".strtoupper($product->status), AdminData::sku($product->id_produk));

        return back()->with('success', "Status {$product->nama_produk} menjadi ".strtoupper($product->status).'.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $product = Produk::findOrFail($id);
        $name = $product->nama_produk;
        $product->delete(); // stok ikut terhapus (FK cascade)

        AuditTrail::log('KATALOG_MENU', "Hapus menu {$name}", AdminData::sku($id));

        return redirect()->route('admin.menu.index')->with('success', "Menu {$name} dihapus.");
    }
}
