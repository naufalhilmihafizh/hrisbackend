<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Departemen IT', 'description' => 'Departemen Teknologi Informasi'],
            ['name' => 'Departemen HR', 'description' => 'Departemen Sumber Daya Manusia'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['name' => $dept['name']], $dept);
        }
    }
}
