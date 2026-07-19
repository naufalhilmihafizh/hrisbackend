<?php

namespace Database\Seeders;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $emp1 = User::where('email', 'employee1@hris.com')->first();
        $emp2 = User::where('email', 'employee2@hris.com')->first();
        $emp3 = User::where('email', 'employee3@hris.com')->first();
        $emp4 = User::where('email', 'employee4@hris.com')->first();

        $itManager = User::where('email', 'it.manager@hris.com')->first();
        $hrManager = User::where('email', 'hr.manager@hris.com')->first();

        // 1. Employee 1 (IT): Approved Annual Leave
        if ($emp1 && $itManager) {
            Leave::create([
                'user_id' => $emp1->id,
                'leave_type' => 'annual',
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-17',
                'reason' => 'Keperluan keluarga',
                'status' => 'approved',
                'approved_by' => $itManager->id,
                'approved_at' => Carbon::now(),
            ]);
        }

        // 2. Employee 2 (IT): Pending Sick Leave
        if ($emp2) {
            Leave::create([
                'user_id' => $emp2->id,
                'leave_type' => 'sick',
                'start_date' => '2026-06-20',
                'end_date' => '2026-06-21',
                'reason' => 'Demam tinggi',
                'status' => 'pending',
            ]);
        }

        // 3. Employee 3 (HR): Rejected Annual Leave
        if ($emp3 && $hrManager) {
            Leave::create([
                'user_id' => $emp3->id,
                'leave_type' => 'annual',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'reason' => 'Liburan ke luar kota',
                'status' => 'rejected',
                'approved_by' => $hrManager->id,
                'approved_at' => Carbon::now(),
                'rejection_reason' => 'Sedang banyak proyek rekrutmen penting',
            ]);
        }

        // 4. Employee 4 (HR): Approved Sick Leave
        if ($emp4 && $hrManager) {
            Leave::create([
                'user_id' => $emp4->id,
                'leave_type' => 'sick',
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-11',
                'reason' => 'Rawat jalan rumah sakit',
                'status' => 'approved',
                'approved_by' => $hrManager->id,
                'approved_at' => Carbon::now(),
            ]);
        }
    }
}
