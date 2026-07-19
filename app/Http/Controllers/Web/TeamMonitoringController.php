<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeamMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $isManager = auth()->user()->role === 'manager';
        $today = Carbon::today()->toDateString();

        $teamQuery = User::with(['department', 'position'])
            ->where('role', 'employee')
            ->where('is_active', true);

        if ($isManager) {
            $teamQuery->where('manager_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $teamQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teamMembers = $teamQuery->orderBy('name')->paginate(10);
        $teamIds = (clone $teamQuery)->pluck('id');

        $attendanceToday = Attendance::whereIn('user_id', $teamIds)
            ->whereDate('date', $today)
            ->get();

        $summary = [
            'team_count' => $teamIds->count(),
            'present_today' => $attendanceToday->where('status', 'present')->count(),
            'late_today' => $attendanceToday->where('status', 'late')->count(),
            'absent_today' => max(0, $teamIds->count() - $attendanceToday->count()),
            'pending_leaves' => Leave::whereIn('user_id', $teamIds)->where('status', 'pending')->count(),
            'pending_overtimes' => Overtime::whereIn('user_id', $teamIds)->where('status', 'pending')->count(),
        ];

        $attendanceHistory = Attendance::with('user')
            ->whereIn('user_id', $teamIds)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->limit(20)
            ->get();

        return view('teams.index', compact('teamMembers', 'summary', 'attendanceHistory'));
    }
}
