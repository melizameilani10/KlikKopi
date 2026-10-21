<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'name'     => 'Customer PERKOCI',
            'username' => 'customer.demo',
            'password' => Hash::make('password'),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $attributes['role'] = 'customer';
        }

        // firstOrNew + forceFill: idempoten (tidak duplikat saat db:seed diulang)
        // dan tidak bergantung pada $fillable di model User.
        User::firstOrNew(['email' => 'customer@perkoci.test'])
            ->forceFill($attributes)
            ->save();
    }
}
