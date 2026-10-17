<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Pesanan;

/**
 * Presenter pesanan agar bentuk data JSON konsisten antar endpoint.
 */
trait PresentsPesanan
{
    protected function presentPesanan(Pesanan $pesanan): array
    {
        $pesanan->loadMissing([
            'meja',
            'detail.produk',
            'pembayaran.metode',
            'user:id_user,name,username,role',
        ]);

        return $pesanan->toArray();
    }
}
