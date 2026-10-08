<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Jejak audit panel admin — disimpan sebagai JSON Lines di
 * storage/app/admin-audit.jsonl (TANPA mengubah schema database).
 *
 * Dicatat: aksi admin di modul Katalog Menu, Stok, QR Meja.
 */
class AuditTrail
{
    private const FILE = 'admin-audit.jsonl';

    public static function log(string $modul, string $aktivitas, ?string $detail = null, string $status = 'BERHASIL', ?string $user = null): void
    {
        try {
            $entry = [
                'time' => now()->format('Y-m-d H:i:s'),
                'user' => $user ?? (auth()->user()->name ?? 'SYSTEM'),
                'modul' => $modul,
                'aktivitas' => $aktivitas,
                'detail' => $detail,
                'status' => $status,
            ];
            Storage::append(self::FILE, json_encode($entry, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable) {
            // Audit tidak boleh menggagalkan aksi utama.
        }
    }

    /** Baca entri terbaru dulu, dengan filter opsional. */
    public static function read(array $filters = []): array
    {
        try {
            if (! Storage::exists(self::FILE)) {
                return [];
            }
            $lines = array_filter(explode("\n", Storage::get(self::FILE)));
            $rows = [];
            foreach ($lines as $line) {
                $row = json_decode($line, true);
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }
            $rows = array_reverse($rows);

            return array_values(array_filter($rows, function ($r) use ($filters) {
                if (! empty($filters['tanggal']) && ! str_starts_with($r['time'], $filters['tanggal'])) {
                    return false;
                }
                if (! empty($filters['user']) && stripos($r['user'], $filters['user']) === false) {
                    return false;
                }
                if (! empty($filters['modul']) && $r['modul'] !== $filters['modul']) {
                    return false;
                }
                if (! empty($filters['status']) && $r['status'] !== $filters['status']) {
                    return false;
                }

                return true;
            }));
        } catch (\Throwable) {
            return [];
        }
    }

    public static function modules(): array
    {
        return ['KATALOG_MENU', 'INVENTORI_STOK', 'QR_MEJA', 'KEUANGAN', 'AUTORISASI'];
    }

    public static function statuses(): array
    {
        return ['BERHASIL', 'AUTH_OK', 'GAGAL'];
    }
}
