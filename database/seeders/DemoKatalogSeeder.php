<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data demo modul admin — OPSIONAL, tidak dipanggil DatabaseSeeder.
 * Jalankan manual: php artisan db:seed --class=DemoKatalogSeeder
 */
class DemoKatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('produk') || ! Schema::hasTable('meja')) {
            $this->command->warn('Tabel produk/meja belum ada. Jalankan migrate dulu.');

            return;
        }

        $produk = [
            ['nama_produk' => 'Kopi Susu Perkoci Aren', 'kategori' => 'Signature Coffee', 'harga' => 24000, 'deskripsi' => 'Espresso blend, aren cair, susu creamy.', 'status' => 'aktif', 'stok' => 40, 'min' => 10],
            ['nama_produk' => 'V60 Single Origin Gayo', 'kategori' => 'Manual Brew', 'harga' => 28000, 'deskripsi' => 'Notes: black tea, citrus.', 'status' => 'aktif', 'stok' => 25, 'min' => 8],
            ['nama_produk' => 'Artisan Matcha Latte', 'kategori' => 'Non-Kopi & Teh', 'harga' => 26000, 'deskripsi' => 'Pure Kyoto ceremonial matcha.', 'status' => 'aktif', 'stok' => 30, 'min' => 8],
            ['nama_produk' => 'Croissant Butter', 'kategori' => 'Pastry & Bakery', 'harga' => 25000, 'deskripsi' => 'Double baked dengan almond.', 'status' => 'aktif', 'stok' => 4, 'min' => 8],
            ['nama_produk' => 'Caramel Macchiato', 'kategori' => 'Signature Coffee', 'harga' => 29000, 'deskripsi' => 'Vanilla sweet milk, espresso.', 'status' => 'aktif', 'stok' => 18, 'min' => 8],
            ['nama_produk' => 'Cold Brew Nitro', 'kategori' => 'Signature Coffee', 'harga' => 30000, 'deskripsi' => 'Seduhan 16 jam nitro gas.', 'status' => 'aktif', 'stok' => 0, 'min' => 5],
            ['nama_produk' => 'Paket Ngopi Berdua', 'kategori' => 'Paket Pairing', 'harga' => 49000, 'deskripsi' => '2 kopi + 1 pastry.', 'status' => 'aktif', 'stok' => 12, 'min' => 5],
            ['nama_produk' => 'Nasi Goreng Kampung', 'kategori' => 'Makanan Utama', 'harga' => 32000, 'deskripsi' => 'Autentik, telur & kerupuk.', 'status' => 'nonaktif', 'stok' => 0, 'min' => 5],
        ];

        foreach ($produk as $p) {
            $id = DB::table('produk')->insertGetId([
                'nama_produk' => $p['nama_produk'],
                'kategori' => $p['kategori'],
                'harga' => $p['harga'],
                'deskripsi' => $p['deskripsi'],
                'status' => $p['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_produk');

            if (Schema::hasTable('stok')) {
                DB::table('stok')->insert([
                    'id_produk' => $id,
                    'jumlah_stok' => $p['stok'],
                    'min_stok' => $p['min'],
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (range(1, 20) as $n) {
            $no = str_pad((string) $n, 2, '0', STR_PAD_LEFT);
            DB::table('meja')->updateOrInsert(
                ['no_meja' => $no],
                [
                    'qr_code' => 'MEJA-'.$no,
                    'status' => in_array($n, [3, 8, 12], true) ? 'terisi' : 'tersedia',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Demo katalog admin: 8 produk + stok + 20 meja.');
    }
}
