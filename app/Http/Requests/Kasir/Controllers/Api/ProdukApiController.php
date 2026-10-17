<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdukApiController extends Controller
{
    /**
     * GET /api/v1/produk
     * Katalog produk publik beserta ketersediaan stok.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Produk::with('stok');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->query('kategori'));
        }

        // Default hanya produk aktif agar customer tidak memesan barang nonaktif.
        $query->where('status', $request->query('status', 'aktif'));

        $produk = $query->orderBy('id_produk')->get()->map(fn (Produk $p) => [
            'id_produk'   => $p->id_produk,
            'nama_produk' => $p->nama_produk,
            'kategori'    => $p->kategori,
            'harga'       => (float) $p->harga,
            'deskripsi'   => $p->deskripsi,
            'status'      => $p->status,
            'jumlah_stok' => $p->stok?->jumlah_stok,
            'tersedia'    => $p->status === 'aktif' && ($p->stok === null || $p->stok->jumlah_stok > 0),
        ]);

        return ApiResponse::success($produk, 'Daftar produk.');
    }
}
