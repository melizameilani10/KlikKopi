<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PresentsPesanan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pesanan\BayarPesananRequest;
use App\Http\Requests\Pesanan\StorePesananRequest;
use App\Services\PesananService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PesananController extends Controller
{
    use PresentsPesanan;

    public function __construct(private readonly PesananService $service)
    {
    }

    /**
     * POST /api/v1/pesanan  (role: customer)
     * Buat pesanan baru; total dihitung otomatis dari harga produk.
     */
    public function store(StorePesananRequest $request): JsonResponse
    {
        $pesanan = $this->service->createOrder($request->user(), $request->validated());

        return ApiResponse::success(
            $this->presentPesanan($pesanan),
            "Pesanan {$pesanan->kode_pesanan} berhasil dibuat.",
            201,
        );
    }

    /**
     * POST /api/v1/pesanan/{kode}/bayar  (role: customer)
     * Catat pembayaran (opsional unggah bukti) — menunggu verifikasi kasir.
     */
    public function bayar(BayarPesananRequest $request, string $kode): JsonResponse
    {
        $pembayaran = $this->service->payOrder(
            $request->user(),
            $kode,
            $request->validated(),
            $request->file('bukti'),
        );

        return ApiResponse::success(
            $pembayaran->toArray(),
            'Pembayaran diterima, menunggu verifikasi kasir.',
            201,
        );
    }
}
