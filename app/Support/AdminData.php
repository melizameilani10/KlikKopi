<?php

namespace App\Support;

use App\Models\Meja;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agregasi data panel admin — selalu dari database asli
 * (tabel produk, stok, meja, pesanan). Kosong → empty state jujur.
 */
class AdminData
{
    public static function rupiah(int|float $n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }

    /** SKU tampilan (kolom SKU tidak ada di DB) — derivasi dari id. */
    public static function sku(int $id): string
    {
        return 'SKU-PRK-'.str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }

    public static function menuAktifCount(): int
    {
        try {
            return Produk::where('status', 'aktif')->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function menuCount(): int
    {
        try {
            return Produk::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Baris stok + info produk. */
    public static function stockRows(): array
    {
        try {
            if (! Schema::hasTable('stok')) {
                return [];
            }

            return DB::table('stok')
                ->leftJoin('produk', 'produk.id_produk', '=', 'stok.id_produk')
                ->select('stok.*', 'produk.nama_produk', 'produk.kategori', 'produk.status as produk_status')
                ->orderBy('stok.jumlah_stok')
                ->get()
                ->map(fn ($r) => [
                    'id_stok' => $r->id_stok,
                    'id_produk' => $r->id_produk,
                    'nama' => $r->nama_produk ?? "Produk #{$r->id_produk}",
                    'kategori' => $r->kategori ?? '-',
                    'jumlah' => (int) $r->jumlah_stok,
                    'min' => (int) $r->min_stok,
                    'updated' => $r->updated_at,
                    'status' => self::stockStatus((int) $r->jumlah_stok, (int) $r->min_stok),
                ])->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public static function stockStatus(int $jumlah, int $min): string
    {
        if ($jumlah <= 0) {
            return 'HABIS';
        }
        if ($jumlah <= $min) {
            return 'MENIPIS';
        }

        return 'AMAN';
    }

    public static function stockCounts(): array
    {
        $rows = self::stockRows();
        $c = ['total' => count($rows), 'aman' => 0, 'menipis' => 0, 'habis' => 0];
        foreach ($rows as $r) {
            match ($r['status']) {
                'AMAN' => $c['aman']++,
                'MENIPIS' => $c['menipis']++,
                'HABIS' => $c['habis']++,
                default => null,
            };
        }

        return $c;
    }

    public static function lowStockAlerts(int $limit = 6): array
    {
        return array_slice(array_values(array_filter(
            self::stockRows(),
            fn ($r) => $r['status'] !== 'AMAN'
        )), 0, $limit);
    }

    public static function occupancy(): array
    {
        try {
            $total = Meja::count();
            $terisi = Meja::where('status', 'terisi')->count();

            return [
                'total' => $total,
                'terisi' => $terisi,
                'tersedia' => $total - $terisi,
                'pct' => $total > 0 ? (int) round($terisi / $total * 100) : 0,
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'terisi' => 0, 'tersedia' => 0, 'pct' => 0];
        }
    }

    public static function pesananHariIni(): int
    {
        try {
            if (! Schema::hasTable('pesanan')) {
                return 0;
            }

            return DB::table('pesanan')->whereDate('tanggal_pesan', today())->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Kategori unik dari DB, fallback ke daftar desain bila kosong. */
    public static function categories(): array
    {
        try {
            $cats = Produk::query()
                ->whereNotNull('kategori')
                ->distinct()
                ->orderBy('kategori')
                ->pluck('kategori')
                ->all();
            if (! empty($cats)) {
                return $cats;
            }
        } catch (\Throwable) {
        }

        return ['Signature Coffee', 'Pastry & Bakery', 'Paket Pairing', 'Manual Brew', 'Non-Kopi & Teh'];
    }

    /**
     * Area meja — konvensi tampilan (kolom area belum ada di DB).
     * 01–12 Indoor AC, 13+ Outdoor Garden.
     */
    public static function tableArea(string $no): string
    {
        return ((int) ltrim($no, '0') <= 12) ? 'Indoor AC' : 'Outdoor Garden';
    }

    public static function adminMeta($user): array
    {
        $now = now()->locale('id');

        return [
            'name' => $user->name ?? 'Admin',
            'code' => 'Admin 01',
            'username' => $user->username ?? '',
            'today' => $now->translatedFormat('d M Y'),
            'today_label' => 'Hari ini: '.$now->translatedFormat('d M Y'),
        ];
    }
}
