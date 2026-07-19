<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user.department']);
        $employees = collect();

        if (auth()->user()->role === 'manager') {
            $query->whereHas('user', function($q) {
                $q->where('manager_id', auth()->id());
            });
            $employees = User::where('manager_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $employees = User::where('role', '!=', 'admin')
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        $attendances = $query->orderBy('date', 'desc')
                             ->orderBy('check_in_time', 'desc')
                             ->paginate(15);

        return view('attendances.index', compact('attendances', 'employees'));
    }
}
