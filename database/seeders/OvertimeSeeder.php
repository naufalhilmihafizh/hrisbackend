<?php

namespace Database\Seeders;

use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OvertimeSeeder extends Seeder
{
    public function run(): void
    {
        $emp1 = User::where('email', 'employee1@hris.com')->first();
        $emp2 = User::where('email', 'employee2@hris.com')->first();
        $emp3 = User::where('email', 'employee3@hris.com')->first();
        $emp4 = User::where('email', 'employee4@hris.com')->first();

        $itManager = User::where('email', 'it.manager@hris.com')->first();
        $hrManager = User::where('email', 'hr.manager@hris.com')->first();

        // 1. Employee 1 (IT): Approved Overtime
        if ($emp1 && $itManager) {
            Overtime::create([
                'user_id' => $emp1->id,
                'overtime_date' => '2026-05-25',
                'duration_hours' => 4.00,
                'reason' => 'Menyelesaikan debugging modul auth',
                'status' => 'approved',
                'approved_by' => $itManager->id,
                'approved_at' => Carbon::now(),
            ]);
        }

        // 2. Employee 2 (IT): Pending Overtime
        if ($emp2) {
            Overtime::create([
                'user_id' => $emp2->id,
                'overtime_date' => '2026-05-26',
                'duration_hours' => 3.00,
                'reason' => 'Server maintenance berkala',
                'status' => 'pending',
            ]);
        }

        // 3. Employee 3 (HR): Rejected Overtime
        if ($emp3 && $hrManager) {
            Overtime::create([
                'user_id' => $emp3->id,
                'overtime_date' => '2026-05-27',
                'duration_hours' => 2.00,
                'reason' => 'Merapikan berkas fisik HRD',
                'status' => 'rejected',
                'approved_by' => $hrManager->id,
                'approved_at' => Carbon::now(),
                'rejection_reason' => 'Dapat diselesaikan pada jam kerja reguler',
            ]);
        }

        // 4. Employee 4 (HR): Approved Overtime
        if ($emp4 && $hrManager) {
            Overtime::create([
                'user_id' => $emp4->id,
                'overtime_date' => '2026-05-28',
                'duration_hours' => 5.00,
                'reason' => 'Membantu lembur rekrutmen massal',
                'status' => 'approved',
                'approved_by' => $hrManager->id,
                'approved_at' => Carbon::now(),
            ]);
        }
    }
}
