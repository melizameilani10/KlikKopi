<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;
use Illuminate\Support\Facades\Validator;

class MejaController extends Controller
{
    /**
     * GET /api/meja
     * Tampilkan semua meja.
     */
    public function index(Request $request)
    {
        $query = Meja::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $meja = $query->get();

        return response()->json([
            'success' => true,
            'data' => $meja,
        ]);
    }

    /**
     * GET /api/meja/{id}
     * Tampilkan satu meja berdasarkan id_meja.
     */
    public function show($id)
    {
        $meja = Meja::find($id);

        if (!$meja) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $meja,
        ]);
    }

    /**
     * POST /api/meja
     * Tambah meja baru. qr_code otomatis dibuat dari no_meja
     * kalau tidak dikirim manual.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_meja' => 'required|string|max:50|unique:meja,no_meja',
            'status' => 'in:tersedia,terisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $meja = Meja::create([
            'no_meja' => $request->no_meja,
            'qr_code' => $request->qr_code ?? 'MEJA-' . strtoupper($request->no_meja),
            'status' => $request->status ?? 'tersedia',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil ditambahkan',
            'data' => $meja,
        ], 201);
    }

    /**
     * PUT/PATCH /api/meja/{id}
     * Update meja (misalnya ganti status jadi terisi/tersedia).
     */
    public function update(Request $request, $id)
    {
        $meja = Meja::find($id);

        if (!$meja) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'no_meja' => 'sometimes|required|string|max:50|unique:meja,no_meja,' . $id . ',id_meja',
            'status' => 'in:tersedia,terisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $meja->update($request->only(['no_meja', 'qr_code', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil diperbarui',
            'data' => $meja,
        ]);
    }

    /**
     * DELETE /api/meja/{id}
     * Hapus meja.
     */
    public function destroy($id)
    {
        $meja = Meja::find($id);

        if (!$meja) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak ditemukan',
            ], 404);
        }

        $meja->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil dihapus',
        ]);
    }
}
