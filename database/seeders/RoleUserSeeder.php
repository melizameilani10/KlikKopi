<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['email' => 'admin.sitirahma@perkoci.id', 'name' => 'Siti Rahma',      'username' => 'admin.sitirahma', 'role' => 'admin'],
            ['email' => 'manager.perkoci@perkoci.id', 'name' => 'Manajer Perkoci', 'username' => 'manager.perkoci',  'role' => 'manager'],
            ['email' => 'kasir.dimas@perkoci.id',     'name' => 'Dimas',           'username' => 'kasir.dimas',      'role' => 'kasir'],
            // Akun demo sesuai spesifikasi (password: password).
            ['email' => 'admin@perkoci.test',   'name' => 'Admin Demo',   'username' => 'admin.demo',   'role' => 'admin',   'password' => 'password'],
            ['email' => 'manager@perkoci.test', 'name' => 'Manager Demo', 'username' => 'manager.demo', 'role' => 'manager', 'password' => 'password'],
            ['email' => 'kasir@perkoci.test',   'name' => 'Kasir Demo',   'username' => 'kasir.demo',   'role' => 'kasir',   'password' => 'password'],
            ['email' => 'customer@perkoci.test', 'name' => 'Customer Demo', 'username' => 'customer.demo', 'role' => 'customer', 'password' => 'password'],
        ];

        $hasRole = Schema::hasColumn('users', 'role');

        foreach ($accounts as $account) {
            $attributes = [
                'name'     => $account['name'],
                'username' => $account['username'],
                'password' => Hash::make($account['password'] ?? 'Perkoci#2026'),
            ];

            if ($hasRole) {
                $attributes['role'] = $account['role'];
            }

            // forceFill: tidak bergantung pada $fillable di model User.
            User::firstOrNew(['email' => $account['email']])
                ->forceFill($attributes)
                ->save();
        }
    }
}
