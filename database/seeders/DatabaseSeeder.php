<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun Super Admin (Satu-satunya data seeder awal agar tidak mengganggu data produksi)
        User::updateOrCreate(
            ['email' => 'admin@master-hub.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
