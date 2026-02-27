<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;

class MVPSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'employee@test.com'],
            ['name' => 'Employee', 'password' => Hash::make('password'), 'role' => 'EMPLOYEE']
        );

        User::updateOrCreate(
            ['email' => 'approver@test.com'],
            ['name' => 'Approver', 'password' => Hash::make('password'), 'role' => 'APPROVER']
        );

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'ADMIN']
        );

        Category::updateOrCreate(['name' => 'Comidas'], [
            'max_per_report' => 3000,
            'requires_cfdi' => false
        ]);

        Category::updateOrCreate(['name' => 'Gasolina'], [
            'max_per_report' => 5000,
            'requires_cfdi' => false
        ]);

        Category::updateOrCreate(['name' => 'Hotel'], [
            'max_per_report' => 15000,
            'requires_cfdi' => true
        ]);

        Category::updateOrCreate(['name' => 'Vuelo'], [
            'max_per_report' => 30000,
            'requires_cfdi' => true
        ]);
    }
}