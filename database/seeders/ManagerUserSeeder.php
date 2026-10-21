<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'name'     => 'Manager PERKOCI',
            'username' => 'manager.demo',
            'password' => Hash::make('password'),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $attributes['role'] = 'manager';
        }

        // firstOrNew + forceFill: idempoten (tidak duplikat saat db:seed diulang)
        // dan tidak bergantung pada $fillable di model User.
        User::firstOrNew(['email' => 'manager@perkoci.test'])
            ->forceFill($attributes)
            ->save();
    }
}
