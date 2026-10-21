<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi kolom yang dibutuhkan alur transaksi pesanan.
 *
 * Catatan skema KlikKopi: PK tabel `users` bernama `id_user`
 * (bukan `id` bawaan Laravel), sehingga FK mengarah ke users.id_user.
 *
 * - pesanan.kode_pesanan        : kode unik pesanan (contoh KK-AB12CD)
 * - pesanan.id_user             : pencatat pesanan (akun customer)
 * - pembayaran.bukti_pembayaran : path bukti transfer / QRIS (opsional)
 * - users.role                  : perluas enum agar mendukung role 'customer'
 *
 * Dijaga idempoten (hasColumn) karena sebagian kolom mungkin sudah ada
 * akibat drift skema DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pesanan', 'kode_pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('kode_pesanan', 20)->nullable()->unique();
            });
        } elseif (! Schema::hasIndex('pesanan', ['kode_pesanan'])) {
            // Kolom sudah ada (drift) tapi index unik belum -> tambahkan.
            Schema::table('pesanan', function (Blueprint $table) {
                $table->unique('kode_pesanan');
            });
        }

        if (! Schema::hasColumn('pesanan', 'id_user')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->foreignId('id_user')->nullable()
                    ->constrained('users', 'id_user')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('pembayaran', 'bukti_pembayaran')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->string('bukti_pembayaran')->nullable();
            });
        }

        // Enum role bawaan hanya admin/kasir/manager; tambahkan 'customer'.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kasir', 'manager', 'customer'])->default('kasir')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'kasir', 'manager'])->default('kasir')->change();
        });

        if (Schema::hasColumn('pembayaran', 'bukti_pembayaran')) {
            Schema::table('pembayaran', function (Blueprint $table) {
                $table->dropColumn('bukti_pembayaran');
            });
        }

        if (Schema::hasColumn('pesanan', 'id_user')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropConstrainedForeignId('id_user');
            });
        }

        if (Schema::hasColumn('pesanan', 'kode_pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropUnique(['kode_pesanan']);
                $table->dropColumn('kode_pesanan');
            });
        }
    }
};
