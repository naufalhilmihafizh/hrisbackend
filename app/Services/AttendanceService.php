<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Perform check-in for an employee.
     *
     * @throws ValidationException
     */
    public function checkIn(User $user, float $latitude, float $longitude, ?string $notes = null): Attendance
    {
        $today = Carbon::today()->toDateString();

        // Check if user already checked in today
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->check_in_time) {
            throw ValidationException::withMessages([
                'check_in' => ['Anda sudah melakukan check-in hari ini.']
            ]);
        }

        $now = Carbon::now();
        $workStartTime = config('hris.work_start_time', '09:00:00');
        
        // Determine status
        $status = $now->format('H:i:s') > $workStartTime ? 'late' : 'present';

        if ($existing) {
            // If attendance record was pre-created (e.g. marked absent or other reasons), update it
            $existing->update([
                'check_in_time' => $now,
                'check_in_latitude' => $latitude,
                'check_in_longitude' => $longitude,
                'status' => $status,
                'notes' => $notes,
            ]);
            return $existing;
        }

        return Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in_time' => $now,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'status' => $status,
            'notes' => $notes,
        ]);
    }

    /**
     * Perform check-out for an employee.
     *
     * @throws ValidationException
     */
    public function checkOut(User $user, float $latitude, float $longitude): Attendance
    {
        $today = Carbon::today()->toDateString();

        // Find today's attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            throw ValidationException::withMessages([
                'check_out' => ['Anda harus melakukan check-in terlebih dahulu.']
            ]);
        }

        if ($attendance->check_out_time) {
            throw ValidationException::withMessages([
                'check_out' => ['Anda sudah melakukan check-out hari ini.']
            ]);
        }

        $attendance->update([
            'check_out_time' => Carbon::now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ]);

        return $attendance;
    }

    /**
     * Get today's attendance status for an employee.
     */
    public function getTodayStatus(User $user): ?Attendance
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();
    }

    /**
     * Get history of attendance for an employee.
     */
    public function getHistory(User $user, int $limit = 30): Collection
    {
        return Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get today's attendance of subordinates for a manager.
     */
    public function getTeamTodayAttendance(User $manager): Collection
    {
        $subordinateIds = $manager->subordinates()->pluck('id');
        $today = Carbon::today()->toDateString();

        return User::whereIn('id', $subordinateIds)
            ->with(['attendances' => function ($query) use ($today) {
                $query->whereDate('date', $today);
            }, 'position'])
            ->get()
            ->map(function ($subordinate) {
                $subordinate->today_attendance = $subordinate->attendances->first();
                unset($subordinate->attendances);
                return $subordinate;
            });
    }

    /**
     * Get attendance history of subordinates for a manager.
     */
    public function getTeamHistory(User $manager, int $month, int $year, int $limit = 50): Collection
    {
        $subordinateIds = $manager->subordinates()->pluck('id');

        return Attendance::with(['user.position', 'user.department'])
            ->whereIn('user_id', $subordinateIds)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly attendance recap for an employee.
     */
    public function getMonthlyRecap(User $user, int $month, int $year): array
    {
        // Get all attendances for the given month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($a) => $a->date->toDateString());

        // Get approved leaves for the given month
        $leaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($month, $year) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();

        // Calculate working days in the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // If it's the current month, only calculate up to today
        $now = Carbon::now();
        if ($now->year == $year && $now->month == $month) {
            $endDate = $now->copy()->subDay(); // only count completed days up to yesterday
            if ($endDate->lt($startDate)) {
                $endDate = $startDate->copy();
            }
        }

        $workdays = 0;
        $present = 0;
        $late = 0;
        $absent = 0;
        $onLeave = 0;

        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            // Only count weekdays (Mon-Fri)
            if ($date->isWeekend()) {
                continue;
            }
            
            $workdays++;
            $dateStr = $date->toDateString();

            if (isset($attendances[$dateStr])) {
                $attendance = $attendances[$dateStr];
                if ($attendance->status === 'present') {
                    $present++;
                } elseif ($attendance->status === 'late') {
                    $late++;
                } elseif ($attendance->status === 'absent') {
                    // Check if covered by leave
                    if ($this->isDateCoveredByLeaves($date, $leaves)) {
                        $onLeave++;
                    } else {
                        $absent++;
                    }
                }
            } else {
                // No attendance record. Check if covered by leave
                if ($this->isDateCoveredByLeaves($date, $leaves)) {
                    $onLeave++;
                } else {
                    $absent++;
                }
            }
        }

        return [
            'period_workdays' => $workdays,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'leave' => $onLeave,
            'attendance_rate' => $workdays > 0 ? round((($present + $late) / $workdays) * 100, 2) : 0,
        ];
    }

    /**
     * Check if a date is covered by any of the approved leaves.
     */
    private function isDateCoveredByLeaves(Carbon $date, Collection $leaves): bool
    {
        foreach ($leaves as $leave) {
            if ($date->betweenIncluded($leave->start_date, $leave->end_date)) {
                return true;
            }
        }
        return false;
    }
}
