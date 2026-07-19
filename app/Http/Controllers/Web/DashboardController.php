<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $isManager = auth()->user()->role === 'manager';
        $subordinateIds = $isManager ? User::where('manager_id', auth()->id())->pluck('id') : null;

        if ($isManager) {
            $stats = [
                'total_employees' => $subordinateIds->count(),
                'present_today' => Attendance::whereDate('date', $today)
                    ->where('status', '!=', 'absent')
                    ->whereIn('user_id', $subordinateIds)
                    ->count(),
                'on_leave_today' => Leave::where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->whereIn('user_id', $subordinateIds)
                    ->count(),
                'pending_leaves' => Leave::where('status', 'pending')
                    ->whereIn('user_id', $subordinateIds)
                    ->count(),
                'pending_overtimes' => Overtime::where('status', 'pending')
                    ->whereIn('user_id', $subordinateIds)
                    ->count(),
            ];
        } else {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $stats = [
                'total_employees' => User::where('role', 'employee')->count(),
                'total_managers' => User::where('role', 'manager')->count(),
                'present_today' => Attendance::whereDate('date', $today)->where('status', '!=', 'absent')->count(),
                'on_leave_today' => Leave::where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->count(),
                'pending_leaves' => Leave::where('status', 'pending')->count(),
                'total_attendances' => Attendance::count(),
                'total_leaves' => Leave::count(),
                'total_overtimes' => Overtime::count(),
                'pending_overtimes' => Overtime::where('status', 'pending')->count(),
                'payroll_summary' => (float) Payroll::where('period_month', $currentMonth)
                    ->where('period_year', $currentYear)
                    ->whereIn('status', ['processed', 'paid'])
                    ->sum('total_salary'),
            ];
        }

        // Recent attendances
        $recentQuery = Attendance::with('user.department');
        if ($isManager) {
            $recentQuery->whereIn('user_id', $subordinateIds);
        }
        $recentAttendances = $recentQuery->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->take(5)
            ->get();

        // Pending approvals (for managers/admins)
        $pendingApprovals = collect([]);
        if (auth()->user()->role === 'admin' || $isManager) {
            $pendingQuery = Leave::with('user')->where('status', 'pending');
            if ($isManager) {
                $pendingQuery->whereIn('user_id', $subordinateIds);
            }
            $pendingApprovals = $pendingQuery->take(5)->get();
        }

        return view('dashboard', compact('stats', 'recentAttendances', 'pendingApprovals'));
    }
}
