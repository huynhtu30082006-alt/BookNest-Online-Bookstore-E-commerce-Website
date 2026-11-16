<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Demo accounts (password for both: Password123)
        User::updateOrCreate(['email' => 'lan@example.com'], [
            'name' => 'Lan Nguyen', 'phone' => '0901234567',
            'address' => '12 Nguyen Van Bao, Go Vap District, Ho Chi Minh City',
            'password' => 'Password123', 'role' => 'customer',
        ]);
        User::updateOrCreate(['email' => 'admin@booknest.test'], [
            'name' => 'BookNest Admin', 'phone' => '0900000000',
            'password' => 'Password123', 'role' => 'admin',
        ]);
    }
}
