<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $itDept = Department::where('name', 'Departemen IT')->first();
        $hrDept = Department::where('name', 'Departemen HR')->first();

        $itManagerPos = Position::where('name', 'Manajer IT')->first();
        $devPos = Position::where('name', 'Programmer')->first();
        $hrManagerPos = Position::where('name', 'Manajer HR')->first();
        $hrStaffPos = Position::where('name', 'Staf HRD')->first();

        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@hris.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'join_date' => '2024-01-01',
                'base_salary' => 12000000,
            ]
        );

        // 2. IT Manager
        $itManager = User::updateOrCreate(
            ['email' => 'it.manager@hris.com'],
            [
                'name' => 'Manajer IT',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department_id' => $itDept?->id,
                'position_id' => $itManagerPos?->id,
                'is_active' => true,
                'join_date' => '2024-02-01',
                'base_salary' => 10000000,
            ]
        );

        // 3. HR Manager
        $hrManager = User::updateOrCreate(
            ['email' => 'hr.manager@hris.com'],
            [
                'name' => 'Manajer HR',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department_id' => $hrDept?->id,
                'position_id' => $hrManagerPos?->id,
                'is_active' => true,
                'join_date' => '2024-02-15',
                'base_salary' => 9500000,
            ]
        );

        // 4. Employee 1 (IT / Programmer)
        User::updateOrCreate(
            ['email' => 'employee1@hris.com'],
            [
                'name' => 'Karyawan 1 (IT)',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $itDept?->id,
                'position_id' => $devPos?->id,
                'manager_id' => $itManager->id,
                'is_active' => true,
                'join_date' => '2025-01-01',
                'base_salary' => 8000000,
            ]
        );

        // 5. Employee 2 (IT / Programmer)
        User::updateOrCreate(
            ['email' => 'employee2@hris.com'],
            [
                'name' => 'Karyawan 2 (IT)',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $itDept?->id,
                'position_id' => $devPos?->id,
                'manager_id' => $itManager->id,
                'is_active' => true,
                'join_date' => '2025-02-01',
                'base_salary' => 8000000,
            ]
        );

        // 6. Employee 3 (HR / Staff)
        User::updateOrCreate(
            ['email' => 'employee3@hris.com'],
            [
                'name' => 'Karyawan 3 (HR)',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $hrDept?->id,
                'position_id' => $hrStaffPos?->id,
                'manager_id' => $hrManager->id,
                'is_active' => true,
                'join_date' => '2025-03-01',
                'base_salary' => 6000000,
            ]
        );

        // 7. Employee 4 (HR / Staff)
        User::updateOrCreate(
            ['email' => 'employee4@hris.com'],
            [
                'name' => 'Karyawan 4 (HR)',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $hrDept?->id,
                'position_id' => $hrStaffPos?->id,
                'manager_id' => $hrManager->id,
                'is_active' => true,
                'join_date' => '2025-03-15',
                'base_salary' => 6000000,
            ]
        );
    }
}
