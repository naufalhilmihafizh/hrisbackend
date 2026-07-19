<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected string $employeeToken;
    protected PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@hris.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'base_salary' => 5000000.00, // 5 Million
            'is_active' => true,
        ]);
        $this->employeeToken = $this->employee->createToken('test_token')->plainTextToken;
        
        $this->payrollService = $this->app->make(PayrollService::class);
    }

    /**
     * Test payroll calculation math logic.
     */
    public function test_payroll_calculation(): void
    {
        $month = 5;
        $year = 2026;

        // 1. Add approved overtime: 10 hours on May 15
        Overtime::create([
            'user_id' => $this->employee->id,
            'overtime_date' => '2026-05-15',
            'duration_hours' => 10.0,
            'reason' => 'Server Migration',
            'status' => 'approved',
        ]);

        // 2. Mock Attendance: Add present attendances for Monday to Thursday of first week of May 2026.
        // May 1 2026 is Friday. Let's make employee present on Friday May 1.
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => '2026-05-01',
            'check_in_time' => '2026-05-01 08:30:00',
            'status' => 'present',
        ]);

        // Employee was absent on Monday May 4 and Tuesday May 5 (no attendance records, no approved leaves).
        // Let's create an approved leave for May 6 to May 8.
        Leave::create([
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-05-06',
            'end_date' => '2026-05-08',
            'reason' => 'Liburan',
            'status' => 'approved',
        ]);

        // For all other weekdays in May 2026, let's seed them as present.
        // May 2026 has 21 weekdays.
        // Fridays: 1, 8 (covered by leave), 15, 22, 29
        // Mondays: 4 (absent), 11, 18, 25
        // Tuesdays: 5 (absent), 12, 19, 26
        // Wednesdays: 6 (leave), 13, 20, 27
        // Thursdays: 7 (leave), 14, 21, 28
        // Let's seed present attendance for all other weekdays.
        $weekdays = [
            '2026-05-11', '2026-05-12', '2026-05-13', '2026-05-14', '2026-05-15',
            '2026-05-18', '2026-05-19', '2026-05-20', '2026-05-21', '2026-05-22',
            '2026-05-25', '2026-05-26', '2026-05-27', '2026-05-28', '2026-05-29'
        ];

        foreach ($weekdays as $date) {
            Attendance::create([
                'user_id' => $this->employee->id,
                'date' => $date,
                'check_in_time' => "$date 08:30:00",
                'status' => 'present',
            ]);
        }

        // Run calculation
        $result = $this->payrollService->calculatePayroll($this->employee, $month, $year);

        // Verification:
        // Weekdays total in May 2026: 21 days.
        // Present: 1 (May 1) + 15 (other weekdays) = 16 days.
        // On Leave: 3 days (May 6, 7, 8).
        // Absent: 2 days (May 4, 5).
        // Check calculation:
        // Base salary = 5,000,000
        // Overtime Hours = 10
        // Overtime Pay = 10 * (5,000,000 / 173) * 1.5 = 433,526.01
        // Deductions = 2 days absent * 5,000,000 * (1 / 22) = 454,545.45
        // Expected total = 5,000,000 + 433,526.01 - 454,545.45 = 4,978,980.56

        $this->assertEquals(5000000.00, $result['base_salary']);
        $this->assertEquals(10.0, $result['overtime_hours']);
        $this->assertEquals(433526.01, $result['overtime_pay']);
        $this->assertEquals(454545.45, $result['deductions']);
        $this->assertEquals(2, $result['deduction_details']['absent_days']);
        $this->assertEquals(4978980.56, $result['total_salary']);
    }

    /**
     * Test list and show payroll endpoints.
     */
    public function test_payroll_api_endpoints(): void
    {
        // Create payroll record
        $payroll = Payroll::create([
            'user_id' => $this->employee->id,
            'period_month' => 5,
            'period_year' => 2026,
            'base_salary' => 5000000.00,
            'overtime_pay' => 200000.00,
            'overtime_hours' => 4.5,
            'deductions' => 0,
            'total_salary' => 5200000.00,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        // Get history (index)
        $responseIndex = $this->getJson('/api/payrolls', [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);
        $responseIndex->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.total_salary', 5200000);

        // Get detail (show)
        $responseShow = $this->getJson("/api/payrolls/{$payroll->id}", [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);
        $responseShow->assertStatus(200)
            ->assertJsonPath('data.id', $payroll->id)
            ->assertJsonPath('data.status', 'paid');
    }
}
