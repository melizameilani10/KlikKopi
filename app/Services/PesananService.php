<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Logika bisnis alur pesanan: pembuatan, pembayaran, dan verifikasi kasir.
 * Semua penulisan dibungkus DB::transaction; baris stok/meja dikunci
 * dengan lockForUpdate() agar tidak bentrok saat akses bersamaan.
 */
class PesananService
{
    /**
     * Buat pesanan + detail dan potong stok dalam satu transaksi DB.
     *
     * @throws ValidationException bila meja terpakai / stok tidak cukup / produk nonaktif.
     */
    public function createOrder(User $user, array $data): Pesanan
    {
        return DB::transaction(function () use ($user, $data) {
            // Kunci baris meja agar tidak dipakai pesanan lain di waktu yang sama.
            $meja = Meja::whereKey($data['id_meja'])->lockForUpdate()->first();

            if (! $meja) {
                throw ValidationException::withMessages([
                    'id_meja' => ['Meja tidak ditemukan.'],
                ]);
            }

            // Anti-bentrok: tolak bila meja masih punya pesanan aktif.
            $terpakai = Pesanan::where('id_meja', $meja->id_meja)
                ->whereIn('status_pesanan', ['pending', 'diproses'])
                ->exists();

            if ($terpakai) {
                throw ValidationException::withMessages([
                    'id_meja' => ["Meja {$meja->no_meja} sedang terpakai."],
                ]);
            }

            $total = 0;
            $details = [];

            foreach ($data['items'] as $index => $item) {
                $produk = Produk::whereKey($item['id_produk'])->first();

                if (! $produk) {
                    throw ValidationException::withMessages([
                        "items.{$index}.id_produk" => ['Produk tidak ditemukan.'],
                    ]);
                }

                if ($produk->status !== 'aktif') {
                    throw ValidationException::withMessages([
                        "items.{$index}.id_produk" => ["Produk {$produk->nama_produk} sedang tidak tersedia."],
                    ]);
                }

                $jumlah = (int) $item['jumlah'];

                // Kunci baris stok supaya dua pesanan bersamaan tidak membuat stok minus.
                $stok = Stok::where('id_produk', $produk->id_produk)->lockForUpdate()->first();

                if ($stok && $stok->jumlah_stok < $jumlah) {
                    throw ValidationException::withMessages([
                        "items.{$index}.jumlah" => ["Stok {$produk->nama_produk} tidak cukup (tersisa {$stok->jumlah_stok})."],
                    ]);
                }

                $subtotal = (float) $produk->harga * $jumlah;
                $total += $subtotal;

                $details[] = [
                    'id_produk' => $produk->id_produk,
                    'jumlah'    => $jumlah,
                    'harga'     => $produk->harga,
                    'subtotal'  => $subtotal,
                    'topping'   => $item['topping'] ?? null,
                    'gula'      => $item['gula'] ?? null,
                    'es'        => $item['es'] ?? null,
                    'catatan'   => $item['catatan'] ?? null,
                ];

                // Kurangi stok bila produk sudah punya catatan stok.
                if ($stok) {
                    $stok->jumlah_stok -= $jumlah;
                    $stok->updated_at = now();
                    $stok->save();
                }
            }

            // Nomor antrean harian, lanjut dari yang tertinggi hari ini.
            $nomorAntrean = (int) (Pesanan::whereDate('tanggal_pesan', now()->toDateString())
                ->max('nomor_antrean') ?? 0) + 1;

            $pesanan = Pesanan::create([
                'kode_pesanan'   => $this->generateKode(),
                'id_meja'        => $meja->id_meja,
                'id_user'        => $user->id_user,
                'nomor_antrean'  => $nomorAntrean,
                'tanggal_pesan'  => now(),
                'total_harga'    => $total,
                'status_pesanan' => 'pending',
            ]);

            $pesanan->detail()->createMany($details);

            // Tandai meja sedang terisi.
            $meja->status = 'terisi';
            $meja->save();

            return $pesanan->load(['meja', 'detail.produk', 'pembayaran']);
        });
    }

    /**
     * Catat pembayaran dari customer (status menunggu verifikasi kasir).
     * Bila sudah ada pembayaran pending, baris tersebut diperbarui.
     *
     * @throws NotFoundHttpException bila kode pesanan tidak ada.
     * @throws ValidationException bila pesanan sudah final.
     */
    public function payOrder(User $user, string $kode, array $data, ?UploadedFile $bukti = null): Pembayaran
    {
        return DB::transaction(function () use ($kode, $data, $bukti) {
            $pesanan = Pesanan::where('kode_pesanan', $kode)->lockForUpdate()->first();

            if (! $pesanan) {
                throw new NotFoundHttpException('Pesanan tidak ditemukan.');
            }

            if (in_array($pesanan->status_pesanan, ['selesai', 'batal'], true)) {
                throw ValidationException::withMessages([
                    'pesanan' => ['Pesanan sudah final, tidak bisa dibayar lagi.'],
                ]);
            }

            $jumlahBayar = (float) $data['jumlah_bayar'];
            $total = (float) $pesanan->total_harga;
            $kembalian = $jumlahBayar > $total ? $jumlahBayar - $total : 0;

            $path = $bukti ? $bukti->store('bukti-pembayaran', 'public') : null;

            // Perbarui pembayaran pending yang belum diverifikasi, atau buat baru.
            $pembayaran = $pesanan->pembayaran()
                ->where('status_pembayaran', 'pending')
                ->orderByDesc('id_pembayaran')
                ->first();

            if ($pembayaran) {
                $pembayaran->update([
                    'id_metode'          => $data['id_metode'],
                    'jumlah_bayar'       => $jumlahBayar,
                    'kembalian'          => $kembalian,
                    'bukti_pembayaran'   => $path ?? $pembayaran->bukti_pembayaran,
                    'tanggal_pembayaran' => now(),
                ]);
            } else {
                $pembayaran = Pembayaran::create([
                    'id_pesanan'         => $pesanan->id_pesanan,
                    'id_metode'          => $data['id_metode'],
                    'jumlah_bayar'       => $jumlahBayar,
                    'kembalian'          => $kembalian,
                    'status_pembayaran'  => 'pending',
                    'bukti_pembayaran'   => $path,
                    'tanggal_pembayaran' => now(),
                ]);
            }

            return $pembayaran->load('metode');
        });
    }

    /**
     * Verifikasi kasir: ubah status pesanan (diproses/selesai/batal),
     * tandai pembayaran berhasil, dan catat transaksi bila selesai.
     * Pembatalan mengembalikan stok.
     *
     * @throws NotFoundHttpException|ValidationException
     */
    public function verifyOrder(User $kasir, string $kode, string $status): Pesanan
    {
        return DB::transaction(function () use ($kasir, $kode, $status) {
            $pesanan = Pesanan::where('kode_pesanan', $kode)->lockForUpdate()->first();

            if (! $pesanan) {
                throw new NotFoundHttpException('Pesanan tidak ditemukan.');
            }

            $pembayaran = $pesanan->pembayaran()->orderByDesc('id_pembayaran')->lockForUpdate()->first();

            if ($status === 'diproses') {
                if ($pesanan->status_pesanan !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => ['Hanya pesanan berstatus pending yang bisa diproses.'],
                    ]);
                }
                $pesanan->status_pesanan = 'diproses';
                $pesanan->save();
            }

            if ($status === 'selesai') {
                if (in_array($pesanan->status_pesanan, ['selesai', 'batal'], true)) {
                    throw ValidationException::withMessages([
                        'status' => ['Pesanan sudah berstatus final.'],
                    ]);
                }

                if (! $pembayaran) {
                    throw ValidationException::withMessages([
                        'pembayaran' => ['Pesanan belum dibayar.'],
                    ]);
                }

                $pembayaran->status_pembayaran = 'berhasil';
                $pembayaran->save();

                $pesanan->status_pesanan = 'selesai';
                $pesanan->save();

                // Catat transaksi untuk laporan penjualan manajer.
                Transaksi::create([
                    'id_pembayaran'     => $pembayaran->id_pembayaran,
                    'id_user'           => $kasir->id_user,
                    'tanggal_transaksi' => now(),
                    'status_transaksi'  => 'berhasil',
                ]);

                $this->freeMejaIfIdle($pesanan->id_meja);
            }

            if ($status === 'batal') {
                if (in_array($pesanan->status_pesanan, ['selesai', 'batal'], true)) {
                    throw ValidationException::withMessages([
                        'status' => ['Pesanan sudah berstatus final.'],
                    ]);
                }

                // Kembalikan stok item yang dibatalkan.
                foreach ($pesanan->detail as $detail) {
                    $stok = Stok::where('id_produk', $detail->id_produk)->lockForUpdate()->first();
                    if ($stok) {
                        $stok->jumlah_stok += $detail->jumlah;
                        $stok->updated_at = now();
                        $stok->save();
                    }
                }

                if ($pembayaran) {
                    $pembayaran->status_pembayaran = 'gagal';
                    $pembayaran->save();
                }

                $pesanan->status_pesanan = 'batal';
                $pesanan->save();

                $this->freeMejaIfIdle($pesanan->id_meja);
            }

            return $pesanan->load(['meja', 'detail.produk', 'pembayaran.metode', 'user']);
        });
    }

    /** Bebaskan meja bila tidak ada lagi pesanan aktif di meja tersebut. */
    private function freeMejaIfIdle(int $idMeja): void
    {
        $masihAktif = Pesanan::where('id_meja', $idMeja)
            ->whereIn('status_pesanan', ['pending', 'diproses'])
            ->exists();

        if (! $masihAktif) {
            Meja::whereKey($idMeja)->update(['status' => 'tersedia']);
        }
    }

    /** Buat kode pesanan unik berformat KK-XXXXXX. */
    private function generateKode(): string
    {
        do {
            $kode = 'KK-'.strtoupper(Str::random(6));
        } while (Pesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }
}
