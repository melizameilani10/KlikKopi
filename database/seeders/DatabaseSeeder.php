<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleUserSeeder::class,
            AdminUserSeeder::class,
            KasirUserSeeder::class,
            ManagerUserSeeder::class,
            CustomerUserSeeder::class,
        ]);
    }
}
