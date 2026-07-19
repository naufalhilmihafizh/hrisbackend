<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Get dashboard stats for an employee.
     */
    public function getEmployeeDashboardStats(User $employee): array
    {
        $today = Carbon::today()->toDateString();
        
        // 1. Today's attendance status
        $todayAttendance = Attendance::where('user_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 2. Remaining leave calculation (Standard 12 days per year base)
        $approvedLeaves = Leave::where('user_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->get();

        $usedLeaveDays = 0;
        foreach ($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            // Count only weekdays if desired, but default diffInDays + 1 is standard
            $usedLeaveDays += $start->diffInDays($end) + 1;
        }
        $remainingLeave = max(0, 12 - $usedLeaveDays);

        // 3. Pending requests
        $pendingLeaves = Leave::where('user_id', $employee->id)->where('status', 'pending')->count();
        $pendingOvertimes = Overtime::where('user_id', $employee->id)->where('status', 'pending')->count();

        // 4. Latest slip
        $latestPayroll = Payroll::where('user_id', $employee->id)
            ->whereIn('status', ['processed', 'paid'])
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->first();

        return [
            'today_attendance' => $todayAttendance ? [
                'check_in_time' => $todayAttendance->check_in_time ? Carbon::parse($todayAttendance->check_in_time)->format('H:i:s') : null,
                'check_out_time' => $todayAttendance->check_out_time ? Carbon::parse($todayAttendance->check_out_time)->format('H:i:s') : null,
                'status' => $todayAttendance->status,
            ] : null,
            'remaining_leave_days' => $remainingLeave,
            'pending_requests_count' => $pendingLeaves + $pendingOvertimes,
            'latest_payroll' => $latestPayroll ? [
                'id' => $latestPayroll->id,
                'period_month' => $latestPayroll->period_month,
                'period_year' => $latestPayroll->period_year,
                'total_salary' => $latestPayroll->total_salary,
                'status' => $latestPayroll->status,
            ] : null,
        ];
    }

    /**
     * Get dashboard stats for a manager.
     */
    public function getManagerDashboardStats(User $manager): array
    {
        $today = Carbon::today()->toDateString();
        $subordinateIds = $manager->subordinates()->pluck('id');

        // 1. Total subordinates count
        $subordinatesCount = $subordinateIds->count();

        // 2. Subordinates present today
        $presentTodayCount = Attendance::whereIn('user_id', $subordinateIds)
            ->where('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();

        // 3. Pending approvals for the manager's team
        $pendingLeaves = Leave::whereIn('user_id', $subordinateIds)->where('status', 'pending')->count();
        $pendingOvertimes = Overtime::whereIn('user_id', $subordinateIds)->where('status', 'pending')->count();

        // 4. Team attendance rate this month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        $totalExpectedDays = 0;
        $totalAttendedDays = 0;
        
        // Loop weekdays of the month up to now to get expected days
        $currentDate = Carbon::now()->startOfMonth();
        $todayCarbon = Carbon::today();
        while ($currentDate->lte($todayCarbon)) {
            if (!$currentDate->isWeekend()) {
                $totalExpectedDays += $subordinatesCount;
            }
            $currentDate->addDay();
        }

        if ($totalExpectedDays > 0) {
            $totalAttendedDays = Attendance::whereIn('user_id', $subordinateIds)
                ->whereBetween('date', [$startOfMonth, $today])
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            $teamAttendanceRate = round(($totalAttendedDays / $totalExpectedDays) * 100, 2);
        } else {
            $teamAttendanceRate = 0;
        }

        return [
            'subordinates_count' => $subordinatesCount,
            'present_today_count' => $presentTodayCount,
            'pending_leaves_count' => $pendingLeaves,
            'pending_overtimes_count' => $pendingOvertimes,
            'pending_approvals_count' => $pendingLeaves + $pendingOvertimes,
            'team_attendance_rate' => $teamAttendanceRate,
        ];
    }

    /**
     * Get dashboard stats for Admin/HR.
     */
    public function getAdminDashboardStats(): array
    {
        $today = Carbon::today()->toDateString();

        // 1. Total active employees
        $activeEmployeesCount = User::where('role', '!=', 'admin')
            ->where('is_active', true)
            ->count();

        // 2. Today's attendance recap
        $attendancesToday = Attendance::where('date', $today)->get();
        $present = $attendancesToday->where('status', 'present')->count();
        $late = $attendancesToday->where('status', 'late')->count();
        $absent = $attendancesToday->where('status', 'absent')->count();
        
        // Unrecorded employees today (assuming they are absent if they haven't check-in yet, and it's after work hours)
        $noRecordCount = max(0, $activeEmployeesCount - ($present + $late));

        // 3. Total pending approvals globally
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $pendingOvertimes = Overtime::where('status', 'pending')->count();

        // 4. Current month payroll summary
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $totalPayrollProcessed = (float) Payroll::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->whereIn('status', ['processed', 'paid'])
            ->sum('total_salary');

        return [
            'active_employees_count' => $activeEmployeesCount,
            'today_attendance' => [
                'present' => $present,
                'late' => $late,
                'absent' => $absent + $noRecordCount,
            ],
            'pending_approvals_count' => $pendingLeaves + $pendingOvertimes,
            'current_month_payroll_total' => $totalPayrollProcessed,
        ];
    }
}
