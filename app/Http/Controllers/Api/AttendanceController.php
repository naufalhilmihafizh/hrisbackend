<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    private function ensureEmployeeRole(Request $request): void
    {
        if ($request->user()->role !== 'employee') {
            throw ValidationException::withMessages([
                'role' => ['Fitur absensi pribadi hanya untuk role employee.'],
            ]);
        }
    }

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Check-in today.
     */
    public function checkIn(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $this->ensureEmployeeRole($request);
            $attendance = $this->attendanceService->checkIn(
                $request->user(),
                (float) $request->latitude,
                (float) $request->longitude,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil dilakukan.',
                'data' => new AttendanceResource($attendance),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat check-in.',
            ], 500);
        }
    }

    /**
     * Check-out today.
     */
    public function checkOut(StoreAttendanceRequest $request): JsonResponse
    {
        try {
            $this->ensureEmployeeRole($request);
            $attendance = $this->attendanceService->checkOut(
                $request->user(),
                (float) $request->latitude,
                (float) $request->longitude
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil dilakukan.',
                'data' => new AttendanceResource($attendance),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat check-out.',
            ], 500);
        }
    }

    /**
     * Get attendance status of logged-in employee today.
     */
    public function today(Request $request): JsonResponse
    {
        $this->ensureEmployeeRole($request);
        $attendance = $this->attendanceService->getTodayStatus($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Status absensi hari ini berhasil diambil.',
            'data' => $attendance ? new AttendanceResource($attendance) : null,
        ]);
    }

    /**
     * Get attendance history of logged-in employee.
     */
    public function index(Request $request): JsonResponse
    {
        $this->ensureEmployeeRole($request);
        $limit = $request->query('limit', 30);
        $attendances = $this->attendanceService->getHistory($request->user(), (int) $limit);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat absensi berhasil diambil.',
            'data' => AttendanceResource::collection($attendances),
        ]);
    }

    /**
     * Get monthly attendance recap for logged-in employee.
     */
    public function monthly(Request $request): JsonResponse
    {
        $this->ensureEmployeeRole($request);
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        $recap = $this->attendanceService->getMonthlyRecap($request->user(), $month, $year);

        return response()->json([
            'success' => true,
            'message' => 'Rekap absensi bulanan berhasil diambil.',
            'data' => $recap,
        ]);
    }

    /**
     * Get team's attendance status today (Manager only).
     */
    public function team(Request $request): JsonResponse
    {
        $teamAttendance = $this->attendanceService->getTeamTodayAttendance($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Data absensi tim hari ini berhasil diambil.',
            'data' => $teamAttendance, // Returns array of user profiles with 'today_attendance' status
        ]);
    }

    /**
     * Get team's attendance history by month (Manager only).
     */
    public function teamHistory(Request $request): JsonResponse
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);
        $limit = (int) $request->query('limit', 50);

        $history = $this->attendanceService->getTeamHistory($request->user(), $month, $year, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat absensi tim berhasil diambil.',
            'data' => AttendanceResource::collection($history),
        ]);
    }
}
