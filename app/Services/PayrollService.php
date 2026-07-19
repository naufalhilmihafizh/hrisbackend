<?php

namespace App\Services;

use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Calculate payroll details for a given month and year.
     */
    public function calculatePayroll(User $employee, int $month, int $year): array
    {
        $baseSalary = (float) $employee->base_salary;

        // 1. Calculate Overtime Pay
        $approvedOvertimes = Overtime::where('user_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('overtime_date', $month)
            ->whereYear('overtime_date', $year)
            ->get();

        $overtimeHours = (float) $approvedOvertimes->sum('duration_hours');
        
        $overtimeDivisor = (int) config('hris.overtime_divisor', 173);
        $overtimeMultiplier = (float) config('hris.overtime_rate_multiplier', 1.5);
        
        // Formula: (Gaji Pokok / 173) x 1.5 x Jam Lembur
        $hourlyRate = $baseSalary / $overtimeDivisor;
        $overtimePay = $overtimeHours * $hourlyRate * $overtimeMultiplier;

        // Round to 2 decimal places
        $overtimePay = round($overtimePay, 2);

        // 2. Calculate Absent Deductions
        $recap = $this->attendanceService->getMonthlyRecap($employee, $month, $year);
        $absentDays = (int) $recap['absent'];
        
        $deductionRate = (float) config('hris.absence_deduction_rate', 1 / 22);
        $absentDeduction = $absentDays * $baseSalary * $deductionRate;
        $absentDeduction = round($absentDeduction, 2);

        $totalDeductions = $absentDeduction;
        
        $deductionDetails = [
            'absent_days' => $absentDays,
            'absent_deduction' => $absentDeduction,
        ];

        // 3. Calculate Total Salary
        $totalSalary = $baseSalary + $overtimePay - $totalDeductions;
        $totalSalary = max(0.0, round($totalSalary, 2));

        return [
            'base_salary' => $baseSalary,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'deductions' => $totalDeductions,
            'deduction_details' => $deductionDetails,
            'total_salary' => $totalSalary,
        ];
    }

    /**
     * Process and save a draft payroll for an employee.
     */
    public function processPayroll(User $employee, int $month, int $year, ?string $notes = null): Payroll
    {
        $calculations = $this->calculatePayroll($employee, $month, $year);

        // Find or create draft payroll
        $payroll = Payroll::where('user_id', $employee->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        if ($payroll && $payroll->status === 'paid') {
            throw ValidationException::withMessages([
                'payroll' => ['Gaji untuk periode ini sudah dibayarkan dan tidak bisa diproses ulang.']
            ]);
        }

        if ($payroll) {
            $payroll->update(array_merge($calculations, [
                'notes' => $notes,
            ]));
        } else {
            $payroll = Payroll::create(array_merge($calculations, [
                'user_id' => $employee->id,
                'period_month' => $month,
                'period_year' => $year,
                'status' => 'draft',
                'notes' => $notes,
            ]));
        }

        return $payroll;
    }

    /**
     * Finalize payroll status to paid or processed.
     */
    public function finalizePayroll(int $payrollId, string $status = 'paid'): Payroll
    {
        $payroll = Payroll::findOrFail($payrollId);

        if (!in_array($status, ['processed', 'paid'])) {
            throw ValidationException::withMessages([
                'status' => ['Status finalisasi tidak valid.']
            ]);
        }

        $updateData = ['status' => $status];
        
        if ($status === 'paid') {
            $updateData['paid_at'] = Carbon::now();
        }

        $payroll->update($updateData);

        return $payroll;
    }

    /**
     * Get all payrolls for an employee.
     */
    public function getHistory(User $user): Collection
    {
        return Payroll::where('user_id', $user->id)
            ->whereIn('status', ['processed', 'paid']) // Only show finalized/paid slips to employee
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();
    }

    /**
     * Get details of a specific payroll slip.
     *
     * @throws ModelNotFoundException
     */
    public function getPayrollDetail(int $id, User $user): Payroll
    {
        $payroll = Payroll::with('user')->findOrFail($id);

        if ($payroll->user_id !== $user->id && $user->role !== 'admin') {
            throw new ModelNotFoundException('Payroll record not found.');
        }

        return $payroll;
    }

    /**
     * Get the latest payroll slip for an employee.
     */
    public function getLatestPayroll(User $user): ?Payroll
    {
        return Payroll::where('user_id', $user->id)
            ->whereIn('status', ['processed', 'paid'])
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->first();
    }
}
