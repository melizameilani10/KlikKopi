<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Transaksi demo 7 hari terakhir untuk panel manajer — OPSIONAL.
 * Jalankan manual SETELAH DemoKatalogSeeder:
 *   php artisan db:seed --class=DemoTransaksiSeeder
 */
class DemoTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['pesanan', 'pembayaran', 'transaksi', 'pemasukan', 'pengeluaran', 'metode_pembayaran'] as $t) {
            if (! Schema::hasTable($t)) {
                $this->command->warn("Tabel {$t} belum ada. Jalankan migrate dulu.");

                return;
            }
        }

        $user = DB::table('users')->first();
        $meja = DB::table('meja')->first();
        if (! $user || ! $meja) {
            $this->command->warn('Belum ada user/meja. Jalankan RoleUserSeeder & DemoKatalogSeeder dulu.');

            return;
        }

        foreach (['QRIS', 'Cash', 'Debit'] as $m) {
            DB::table('metode_pembayaran')->updateOrInsert(['nama_metode' => $m], ['created_at' => now(), 'updated_at' => now()]);
        }
        $metodeIds = DB::table('metode_pembayaran')->pluck('id_metode', 'nama_metode')->all();
        $produk = DB::table('produk')->limit(4)->get();
        $samples = [
            ['Cash', 68000, 'Penjualan loket'],
            ['QRIS', 92000, 'Penjualan QRIS'],
            ['Debit', 54000, 'Penjualan EDC'],
            ['QRIS', 47000, 'Penjualan QRIS'],
        ];

        for ($d = 6; $d >= 0; $d--) {
            $date = date('Y-m-d', strtotime("-{$d} days"));
            $n = $d === 0 ? 2 : 3;
            for ($i = 0; $i < $n; $i++) {
                [$metode, $total, $ket] = $samples[($d + $i) % count($samples)];
                $pesanan = DB::table('pesanan')->insertGetId([
                    'id_meja' => $meja->id_meja,
                    'nomor_antrean' => 10 + $i,
                    'tanggal_pesan' => "{$date} 10:0{$i}:00",
                    'total_harga' => $total,
                    'status_pesanan' => 'selesai',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if ($produk->isNotEmpty()) {
                    $p = $produk[($d + $i) % $produk->count()];
                    DB::table('detail_pesanan')->insert([
                        'id_pesanan' => $pesanan,
                        'id_produk' => $p->id_produk,
                        'jumlah' => 2,
                        'harga' => $p->harga,
                        'subtotal' => $p->harga * 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $bayar = DB::table('pembayaran')->insertGetId([
                    'id_pesanan' => $pesanan,
                    'id_metode' => $metodeIds[$metode],
                    'jumlah_bayar' => $total,
                    'kembalian' => 0,
                    'status_pembayaran' => 'berhasil',
                    'tanggal_pembayaran' => "{$date} 10:0{$i}:00",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $trx = DB::table('transaksi')->insertGetId([
                    'id_pembayaran' => $bayar,
                    'id_user' => $user->id,
                    'tanggal_transaksi' => "{$date} 10:0{$i}:00",
                    'status_transaksi' => 'berhasil',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('pemasukan')->insert([
                    'id_transaksi' => $trx,
                    'id_user' => $user->id,
                    'jumlah' => $total,
                    'keterangan' => $ket,
                    'tanggal' => $date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('pengeluaran')->insert([
            ['id_user' => $user->id, 'jumlah' => 350000, 'keterangan' => 'Belanja susu & bahan', 'tanggal' => date('Y-m-d', strtotime('-2 days')), 'created_at' => now(), 'updated_at' => now()],
            ['id_user' => $user->id, 'jumlah' => 120000, 'keterangan' => 'Listrik & air', 'tanggal' => date('Y-m-d', strtotime('-5 days')), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Demo transaksi manajer 7 hari terakhir dibuat.');
    }
}
