<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin2@admin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '999999999',
            'country' => 'Brasil',
            'entry_code' => 'ADM002',
            'is_admin' => true
        ]);
    }
}
