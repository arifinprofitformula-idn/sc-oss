<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@epistore.online'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
