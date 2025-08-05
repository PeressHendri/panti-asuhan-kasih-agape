<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pantiasuhan.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'phone' => '081234567890',
            'address' => 'Jl. Kasih Agape No. 1, Jakarta',
        ]);

        // Create Pengasuh User
        User::create([
            'name' => 'Pengasuh 1',
            'email' => 'pengasuh@pantiasuhan.com',
            'password' => Hash::make('pengasuh1'),
            'role' => 'pengasuh',
            'is_active' => true,
            'phone' => '081234567891',
            'address' => 'Jl. Kasih Agape No. 2, Jakarta',
        ]);

        // Create Donatur User
        User::create([
            'name' => 'Donatur 1',
            'email' => 'donatur@pantiasuhan.com',
            'password' => Hash::make('donatur1'),
            'role' => 'donatur',
            'is_active' => true,
            'phone' => '081234567892',
            'address' => 'Jl. Dermawan No. 10, Jakarta',
        ]);
    }
}