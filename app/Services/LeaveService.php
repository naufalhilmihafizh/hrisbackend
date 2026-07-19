<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Request leave for an employee.
     *
     * @throws ValidationException
     */
    public function requestLeave(User $user, array $data): Leave
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($startDate->gt($endDate)) {
            throw ValidationException::withMessages([
                'start_date' => ['Tanggal mulai tidak boleh melebihi tanggal selesai.']
            ]);
        }

        // Check if there is an overlapping leave request that is approved or pending
        $overlapping = Leave::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlapping) {
            throw ValidationException::withMessages([
                'leave_dates' => ['Anda sudah mengajukan cuti pada tanggal tersebut (status pending/approved).']
            ]);
        }

        $attachmentPath = null;
        if (request()->hasFile('attachment')) {
            $attachmentPath = request()->file('attachment')->store('leaves', 'public');
        }

        return Leave::create([
            'user_id' => $user->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reason' => $data['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);
    }

    /**
     * Get history of leave requests for an employee.
     */
    public function getHistory(User $user): Collection
    {
        return Leave::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending leaves of subordinates for a manager.
     */
    public function getPendingLeaves(User $manager): Collection
    {
        $subordinateIds = $manager->subordinates()->pluck('id');

        return Leave::whereIn('user_id', $subordinateIds)
            ->where('status', 'pending')
            ->with(['user', 'user.position', 'user.department'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get detail of a specific leave request.
     *
     * @throws ModelNotFoundException
     */
    public function getLeaveDetail(int $id, User $user): Leave
    {
        $leave = Leave::with(['user', 'user.position', 'user.department', 'approver'])->findOrFail($id);

        // Security check: Only the employee, their manager, or admin can view
        if ($leave->user_id !== $user->id && $leave->user->manager_id !== $user->id && $user->role !== 'admin') {
            throw new ModelNotFoundException('Leave record not found.');
        }

        return $leave;
    }
}
