<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. The Super Admin
        User::updateOrCreate(['email' => 'admin@denr.gov.ph'], [
            'name' => 'System Administrator',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. The Records Officer - Can create applications only
        User::updateOrCreate(['email' => 'records@denr.gov.ph'], [
            'name' => 'Records Officer',
            'password' => Hash::make('password123'),
            'role' => 'records_officer',
        ]);

        // 3. The Land Management Officer - Can approve/update applications
        User::updateOrCreate(['email' => 'land@denr.gov.ph'], [
            'name' => 'Land Mgt Officer',
            'password' => Hash::make('password123'),
            'role' => 'land_officer',
        ]);
    }
}