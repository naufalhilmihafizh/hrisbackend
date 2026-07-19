<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $it = Department::where('name', 'Departemen IT')->first();
        $hr = Department::where('name', 'Departemen HR')->first();

        if ($it) {
            Position::updateOrCreate(
                ['name' => 'Manajer IT', 'department_id' => $it->id],
                ['description' => 'Manajer Departemen IT']
            );
            Position::updateOrCreate(
                ['name' => 'Programmer', 'department_id' => $it->id],
                ['description' => 'Pengembang Perangkat Lunak']
            );
        }

        if ($hr) {
            Position::updateOrCreate(
                ['name' => 'Manajer HR', 'department_id' => $hr->id],
                ['description' => 'Manajer Departemen HR']
            );
            Position::updateOrCreate(
                ['name' => 'Staf HRD', 'department_id' => $hr->id],
                ['description' => 'Staf Sumber Daya Manusia']
            );
        }
    }
}
