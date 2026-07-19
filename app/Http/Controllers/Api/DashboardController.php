<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Get dashboard summary stats based on authenticated user's role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = [];

        try {
            if ($user->role === 'admin') {
                $stats = $this->reportingService->getAdminDashboardStats();
            } elseif ($user->role === 'manager') {
                $stats = $this->reportingService->getManagerDashboardStats($user);
            } else {
                $stats = $this->reportingService->getEmployeeDashboardStats($user);
            }

            // Inject personal attendance/leave/overtime summary only for employee.
            if ($user->role === 'employee') {
                $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
                $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->toDateString();

                $totalHadir = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $totalCuti = \App\Models\Leave::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where(function($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                          ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
                    })
                    ->count();

                $totalLembur = \App\Models\Overtime::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereBetween('overtime_date', [$startOfMonth, $endOfMonth])
                    ->count();

                $stats['stats'] = [
                    'total_hadir' => $totalHadir,
                    'total_cuti' => $totalCuti,
                    'total_lembur' => $totalLembur
                ];

                if (!isset($stats['today_attendance'])) {
                    $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
                        ->where('date', \Carbon\Carbon::today()->toDateString())
                        ->first();
                    $stats['today_attendance'] = $todayAttendance ? [
                        'check_in_time' => $todayAttendance->check_in_time ? \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i:s') : null,
                        'check_out_time' => $todayAttendance->check_out_time ? \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i:s') : null,
                        'status' => $todayAttendance->status,
                    ] : null;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil.',
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mengambil data dashboard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
