<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan nilai 'customer' ke enum users.role.
     * Diperlukan agar CustomerUserSeeder / RoleUserSeeder dapat menyimpan role customer.
     * Dibuat minimal: hanya MODIFY enum, tanpa mengubah kolom lain.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'role') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','kasir','manager','customer') NOT NULL DEFAULT 'kasir'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role') && DB::connection()->getDriverName() === 'mysql') {
            // Kembalikan ke enum semula. Gagal jika masih ada baris role=customer.
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','kasir','manager') NOT NULL DEFAULT 'kasir'");
        }
    }
};
