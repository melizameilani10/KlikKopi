<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agregasi data panel manajer — dari database asli
 * (transaksi, pembayaran, pesanan, pemasukan, pengeluaran).
 * Kosong → empty state jujur.
 */
class ManagerData
{
    public static function rupiah(int|float $n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }

    /** Periode default: bulan berjalan. Return [dari, sampai] Y-m-d. */
    public static function period(?string $dari, ?string $sampai): array
    {
        $sampai ??= date('Y-m-d');
        $dari ??= substr($sampai, 0, 7).'-01';

        return [$dari, $sampai];
    }

    /** Query dasar penjualan (transaksi berhasil + relasinya). */
    public static function salesQuery(string $dari, string $sampai)
    {
        return DB::table('transaksi')
            ->join('pembayaran', 'pembayaran.id_pembayaran', '=', 'transaksi.id_pembayaran')
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
            ->leftJoin('meja', 'meja.id_meja', '=', 'pesanan.id_meja')
            ->leftJoin('metode_pembayaran', 'metode_pembayaran.id_metode', '=', 'pembayaran.id_metode')
            ->leftJoin('users', 'users.id', '=', 'transaksi.id_user')
            ->where('transaksi.status_transaksi', 'berhasil')
            ->whereDate('transaksi.tanggal_transaksi', '>=', $dari)
            ->whereDate('transaksi.tanggal_transaksi', '<=', $sampai)
            ->select(
                'transaksi.id_transaksi', 'transaksi.tanggal_transaksi',
                'pesanan.nomor_antrean', 'pesanan.total_harga',
                'meja.no_meja', 'metode_pembayaran.nama_metode',
                'users.name as kasir'
            );
    }

    public static function salesRows(string $dari, string $sampai, ?string $metode = null): array
    {
        try {
            $q = self::salesQuery($dari, $sampai)->orderBy('transaksi.tanggal_transaksi', 'desc');
            if ($metode) {
                $q->where('metode_pembayaran.nama_metode', $metode);
            }

            return $q->get()->map(fn ($r) => [
                'waktu' => $r->tanggal_transaksi,
                'kode' => 'TRX-'.str_pad((string) $r->id_transaksi, 5, '0', STR_PAD_LEFT),
                'antrean' => '#A-'.$r->nomor_antrean,
                'meja' => $r->no_meja ? 'Meja '.$r->no_meja : '-',
                'metode' => $r->nama_metode ?? '-',
                'kasir' => $r->kasir ?? '-',
                'total' => (float) $r->total_harga,
            ])->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public static function methods(): array
    {
        try {
            if (! Schema::hasTable('metode_pembayaran')) {
                return [];
            }

            return DB::table('metode_pembayaran')->orderBy('nama_metode')->pluck('nama_metode')->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public static function summary(string $dari, string $sampai): array
    {
        $rows = self::salesRows($dari, $sampai);
        $omzet = array_sum(array_column($rows, 'total'));
        $byMethod = [];
        foreach ($rows as $r) {
            $byMethod[$r['metode']] = ($byMethod[$r['metode']] ?? 0) + $r['total'];
        }
        arsort($byMethod);

        return [
            'omzet' => $omzet,
            'count' => count($rows),
            'rata' => count($rows) > 0 ? $omzet / count($rows) : 0,
            'byMethod' => $byMethod,
        ];
    }

    /** Omzet per hari (untuk grafik 7 hari terakhir). */
    public static function dailySeries(int $days = 7): array
    {
        $out = [];
        try {
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $sum = self::salesQuery($d, $d)->sum('pesanan.total_harga');
                $out[] = ['tanggal' => $d, 'label' => date('d M', strtotime($d)), 'total' => (float) $sum];
            }
        } catch (\Throwable) {
        }

        return $out;
    }

    /** Menu terlaris dari detail_pesanan + produk. */
    public static function topMenus(string $dari, string $sampai, int $limit = 5): array
    {
        try {
            if (! Schema::hasTable('detail_pesanan')) {
                return [];
            }

            return DB::table('detail_pesanan')
                ->join('pembayaran', 'pembayaran.id_pesanan', '=', 'detail_pesanan.id_pesanan')
                ->join('transaksi', 'transaksi.id_pembayaran', '=', 'pembayaran.id_pembayaran')
                ->join('produk', 'produk.id_produk', '=', 'detail_pesanan.id_produk')
                ->where('transaksi.status_transaksi', 'berhasil')
                ->whereDate('transaksi.tanggal_transaksi', '>=', $dari)
                ->whereDate('transaksi.tanggal_transaksi', '<=', $sampai)
                ->select('produk.nama_produk', DB::raw('SUM(detail_pesanan.jumlah) as qty'), DB::raw('SUM(detail_pesanan.subtotal) as total'))
                ->groupBy('produk.nama_produk')
                ->orderByDesc('qty')
                ->limit($limit)
                ->get()
                ->map(fn ($r) => ['nama' => $r->nama_produk, 'qty' => (int) $r->qty, 'total' => (float) $r->total])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /* ---------- KEUANGAN ---------- */

    public static function pemasukan(string $dari, string $sampai): array
    {
        try {
            if (! Schema::hasTable('pemasukan')) {
                return ['total' => 0, 'rows' => []];
            }
            $rows = DB::table('pemasukan')
                ->leftJoin('users', 'users.id', '=', 'pemasukan.id_user')
                ->whereDate('pemasukan.tanggal', '>=', $dari)
                ->whereDate('pemasukan.tanggal', '<=', $sampai)
                ->orderBy('pemasukan.tanggal', 'desc')
                ->select('pemasukan.*', 'users.name as user')
                ->get();

            return [
                'total' => (float) $rows->sum('jumlah'),
                'rows' => $rows->map(fn ($r) => [
                    'tanggal' => $r->tanggal, 'keterangan' => $r->keterangan ?? 'Penjualan',
                    'user' => $r->user ?? '-', 'jumlah' => (float) $r->jumlah,
                ])->all(),
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'rows' => []];
        }
    }

    public static function pengeluaran(string $dari, string $sampai): array
    {
        try {
            if (! Schema::hasTable('pengeluaran')) {
                return ['total' => 0, 'rows' => []];
            }
            $rows = DB::table('pengeluaran')
                ->leftJoin('users', 'users.id', '=', 'pengeluaran.id_user')
                ->whereDate('pengeluaran.tanggal', '>=', $dari)
                ->whereDate('pengeluaran.tanggal', '<=', $sampai)
                ->orderBy('pengeluaran.tanggal', 'desc')
                ->select('pengeluaran.*', 'users.name as user')
                ->get();

            return [
                'total' => (float) $rows->sum('jumlah'),
                'rows' => $rows->map(fn ($r) => [
                    'id' => $r->id_pengeluaran, 'tanggal' => $r->tanggal,
                    'keterangan' => $r->keterangan ?? '-', 'user' => $r->user ?? '-',
                    'jumlah' => (float) $r->jumlah,
                ])->all(),
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'rows' => []];
        }
    }

    /* ---------- PB1 (estimasi 10% dari total, dilabeli jelas) ---------- */

    public static function pb1(string $dari, string $sampai): array
    {
        $rows = self::salesRows($dari, $sampai);
        $dasar = array_sum(array_column($rows, 'total'));
        $perHari = [];
        foreach ($rows as $r) {
            $d = substr((string) $r['waktu'], 0, 10);
            $perHari[$d] = ($perHari[$d] ?? 0) + $r['total'];
        }
        ksort($perHari);

        return [
            'dasar' => $dasar,
            'tarif' => 10,
            'pajak' => $dasar * 0.10,
            'count' => count($rows),
            'perHari' => $perHari,
        ];
    }

    public static function managerMeta($user): array
    {
        $now = now()->locale('id');

        return [
            'name' => $user->name ?? 'Manajer',
            'code' => 'Manajer 01',
            'today_label' => 'Hari ini: '.$now->translatedFormat('d M Y'),
        ];
    }
}
