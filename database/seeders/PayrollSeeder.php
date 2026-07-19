<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $emp1 = User::where('email', 'employee1@hris.com')->first();
        $emp2 = User::where('email', 'employee2@hris.com')->first();
        $emp4 = User::where('email', 'employee4@hris.com')->first();

        // 1. Employee 1
        if ($emp1) {
            Payroll::create([
                'user_id' => $emp1->id,
                'period_month' => 5,
                'period_year' => 2026,
                'base_salary' => 8000000,
                'overtime_pay' => 277456.65,
                'overtime_hours' => 4.00,
                'deductions' => 100000,
                'deduction_details' => [
                    'late_penalty' => 100000
                ],
                'total_salary' => 8177456.65,
                'status' => 'paid',
                'paid_at' => Carbon::parse('2026-05-25 10:00:00'),
                'notes' => 'Gaji bulan Mei 2026',
            ]);
        }

        // 2. Employee 2
        if ($emp2) {
            Payroll::create([
                'user_id' => $emp2->id,
                'period_month' => 5,
                'period_year' => 2026,
                'base_salary' => 8000000,
                'overtime_pay' => 0.00,
                'overtime_hours' => 0.00,
                'deductions' => 50000,
                'deduction_details' => [
                    'bpjs_kesehatan' => 50000
                ],
                'total_salary' => 7950000,
                'status' => 'paid',
                'paid_at' => Carbon::parse('2026-05-25 10:00:00'),
                'notes' => 'Gaji bulan Mei 2026',
            ]);
        }

        // 3. Employee 4
        if ($emp4) {
            Payroll::create([
                'user_id' => $emp4->id,
                'period_month' => 5,
                'period_year' => 2026,
                'base_salary' => 6000000,
                'overtime_pay' => 260115.60,
                'overtime_hours' => 5.00,
                'deductions' => 0,
                'deduction_details' => [],
                'total_salary' => 6260115.60,
                'status' => 'paid',
                'paid_at' => Carbon::parse('2026-05-25 10:00:00'),
                'notes' => 'Gaji bulan Mei 2026',
            ]);
        }
    }
}
