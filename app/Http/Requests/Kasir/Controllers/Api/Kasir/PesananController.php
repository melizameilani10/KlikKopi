<?php

namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Api\Concerns\PresentsPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\VerifikasiPesananRequest;
use App\Models\Pesanan;
use App\Services\PesananService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    use PresentsPesanan;

    public function __construct(private readonly PesananService $service)
    {
    }

    /**
     * GET /api/v1/kasir/pesanan/pending  (role: kasir)
     * Antrean pesanan yang belum selesai (pending/diproses).
     */
    public function pending(Request $request): JsonResponse
    {
        $query = Pesanan::with(['meja', 'detail.produk', 'pembayaran.metode', 'user:id_user,name,username,role'])
            ->whereIn('status_pesanan', ['pending', 'diproses']);

        if ($request->filled('status')) {
            $query->where('status_pesanan', $request->query('status'));
        }

        $daftar = $query->orderBy('tanggal_pesan')->get()
            ->map(fn (Pesanan $p) => $this->presentPesanan($p));

        return ApiResponse::success($daftar, 'Antrean pesanan.');
    }

    /**
     * PUT /api/v1/kasir/pesanan/{kode}/verifikasi  (role: kasir)
     * Ubah status pesanan: diproses, selesai (lunas), atau batal.
     */
    public function verifikasi(VerifikasiPesananRequest $request, string $kode): JsonResponse
    {
        $pesanan = $this->service->verifyOrder(
            $request->user(),
            $kode,
            $request->validated()['status'],
        );

        return ApiResponse::success(
            $this->presentPesanan($pesanan),
            "Status pesanan {$pesanan->kode_pesanan} diperbarui menjadi {$pesanan->status_pesanan}.",
        );
    }
}
