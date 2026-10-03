<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class KasirUserSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'name'     => 'Dimas',
            'username' => 'kasir.dimas',
            'password' => Hash::make('Perkoci#2026'),
            'pin'      => Hash::make('123456'),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $attributes['role'] = 'kasir';
        }

        // forceFill: tidak bergantung pada $fillable di model User.
        User::firstOrNew(['email' => 'kasir.dimas@perkoci.id'])
            ->forceFill($attributes)
            ->save();
    }
}
