<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 'password' is hashed automatically by the User model's cast.
        User::updateOrCreate(
            ['email' => 'admin.sitirahma@perkoci.id'],
            [
                'name'     => 'Siti Rahma',
                'password' => 'Perkoci#2026',
            ],
        );
    }
}
