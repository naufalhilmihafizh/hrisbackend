<?php

namespace App\Services;

use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OvertimeService
{
    /**
     * Request overtime for an employee.
     *
     * @throws ValidationException
     */
    public function requestOvertime(User $user, array $data): Overtime
    {
        $overtimeDate = Carbon::parse($data['overtime_date']);

        if ($data['duration_hours'] <= 0) {
            throw ValidationException::withMessages([
                'duration_hours' => ['Durasi lembur harus lebih dari 0 jam.']
            ]);
        }

        // Check if there is already an overtime request on this date (approved or pending)
        $existing = Overtime::where('user_id', $user->id)
            ->whereDate('overtime_date', $overtimeDate->toDateString())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'overtime_date' => ['Anda sudah mengajukan lembur untuk tanggal ini (status pending/approved).']
            ]);
        }

        return Overtime::create([
            'user_id' => $user->id,
            'overtime_date' => $overtimeDate->toDateString(),
            'duration_hours' => $data['duration_hours'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    /**
     * Get history of overtime requests for an employee.
     */
    public function getHistory(User $user): Collection
    {
        return Overtime::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending overtimes of subordinates for a manager.
     */
    public function getPendingOvertimes(User $manager): Collection
    {
        $subordinateIds = $manager->subordinates()->pluck('id');

        return Overtime::whereIn('user_id', $subordinateIds)
            ->where('status', 'pending')
            ->with(['user', 'user.position', 'user.department'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get detail of a specific overtime request.
     *
     * @throws ModelNotFoundException
     */
    public function getOvertimeDetail(int $id, User $user): Overtime
    {
        $overtime = Overtime::with(['user', 'user.position', 'user.department', 'approver'])->findOrFail($id);

        // Security check: Only the employee, their manager, or admin can view
        if ($overtime->user_id !== $user->id && $overtime->user->manager_id !== $user->id && $user->role !== 'admin') {
            throw new ModelNotFoundException('Overtime record not found.');
        }

        return $overtime;
    }
}
