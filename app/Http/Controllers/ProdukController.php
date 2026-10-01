<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    /**
     * GET /api/produk
     * Tampilkan semua produk. Bisa difilter by kategori atau status.
     */
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $produk = $query->get();

        return response()->json([
            'success' => true,
            'data' => $produk,
        ]);
    }

    /**
     * GET /api/produk/{id}
     * Tampilkan satu produk berdasarkan id_produk.
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $produk,
        ]);
    }

    /**
     * POST /api/produk
     * Tambah produk baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $produk = Produk::create([
            'nama_produk' => $request->nama_produk,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk,
        ], 201);
    }

    /**
     * PUT/PATCH /api/produk/{id}
     * Update produk yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_produk' => 'sometimes|required|string|max:255',
            'kategori' => 'sometimes|required|string|max:100',
            'harga' => 'sometimes|required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'in:aktif,nonaktif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $produk->update($request->only([
            'nama_produk', 'kategori', 'harga', 'deskripsi', 'status',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk,
        ]);
    }

    /**
     * DELETE /api/produk/{id}
     * Hapus produk.
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ]);
    }
}
