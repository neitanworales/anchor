<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Company;

class MVPSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['slug' => 'empresa-demo'],
            ['name' => 'Empresa Demo']
        );

        $employee = User::updateOrCreate(
            ['email' => 'employee@test.com'],
            ['name' => 'Employee', 'password' => Hash::make('password')]
        );

        $approver = User::updateOrCreate(
            ['email' => 'approver@test.com'],
            ['name' => 'Approver', 'password' => Hash::make('password')]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        $company->users()->syncWithoutDetaching([
            $employee->id => ['role' => 'EMPLOYEE', 'is_active' => true],
            $approver->id => ['role' => 'APPROVER', 'is_active' => true],
            $admin->id => ['role' => 'ADMIN', 'is_active' => true],
        ]);

        Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Comidas'], [
            'max_per_report' => 3000,
            'requires_cfdi' => false
        ]);

        Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Gasolina'], [
            'max_per_report' => 5000,
            'requires_cfdi' => false
        ]);

        Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Hotel'], [
            'max_per_report' => 15000,
            'requires_cfdi' => true
        ]);

        Category::updateOrCreate(['company_id' => $company->id, 'name' => 'Vuelo'], [
            'max_per_report' => 30000,
            'requires_cfdi' => true
        ]);
    }
}