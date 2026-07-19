<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $summary = [
            'attendance_total' => Attendance::count(),
            'leave_total' => Leave::count(),
            'overtime_total' => Overtime::count(),
            'payroll_total' => (float) Payroll::sum('total_salary'),
            'active_employees' => User::where('role', 'employee')->where('is_active', true)->count(),
        ];

        $monthly = [
            'attendance' => Attendance::whereMonth('date', $month)->whereYear('date', $year)->count(),
            'leave' => Leave::whereMonth('start_date', $month)->whereYear('start_date', $year)->count(),
            'overtime' => Overtime::whereMonth('overtime_date', $month)->whereYear('overtime_date', $year)->count(),
            'payroll' => (float) Payroll::where('period_month', $month)->where('period_year', $year)->sum('total_salary'),
        ];

        $activityRecap = [
            'present_today' => Attendance::whereDate('date', Carbon::today())->whereIn('status', ['present', 'late'])->count(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
            'pending_overtimes' => Overtime::where('status', 'pending')->count(),
            'processed_payrolls' => Payroll::whereIn('status', ['processed', 'paid'])->count(),
        ];

        return view('reports.index', compact('summary', 'monthly', 'activityRecap'));
    }
}
