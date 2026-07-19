<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    /**
     * Approve a leave request.
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function approveLeave(User $manager, int $leaveId): Leave
    {
        $leave = Leave::with('user')->findOrFail($leaveId);

        // Access control: must be manager of the user, or admin
        if ($leave->user->manager_id !== $manager->id && $manager->role !== 'admin') {
            throw new ModelNotFoundException('Leave record not found or access denied.');
        }

        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Pengajuan cuti ini sudah diproses sebelumnya.']
            ]);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => Carbon::now(),
        ]);

        return $leave;
    }

    /**
     * Reject a leave request.
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function rejectLeave(User $manager, int $leaveId, string $reason): Leave
    {
        $leave = Leave::with('user')->findOrFail($leaveId);

        if ($leave->user->manager_id !== $manager->id && $manager->role !== 'admin') {
            throw new ModelNotFoundException('Leave record not found or access denied.');
        }

        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Pengajuan cuti ini sudah diproses sebelumnya.']
            ]);
        }

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $manager->id,
            'approved_at' => Carbon::now(),
        ]);

        return $leave;
    }

    /**
     * Approve an overtime request.
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function approveOvertime(User $manager, int $overtimeId): Overtime
    {
        $overtime = Overtime::with('user')->findOrFail($overtimeId);

        if ($overtime->user->manager_id !== $manager->id && $manager->role !== 'admin') {
            throw new ModelNotFoundException('Overtime record not found or access denied.');
        }

        if ($overtime->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Pengajuan lembur ini sudah diproses sebelumnya.']
            ]);
        }

        $overtime->update([
            'status' => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => Carbon::now(),
        ]);

        return $overtime;
    }

    /**
     * Reject an overtime request.
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function rejectOvertime(User $manager, int $overtimeId, string $reason): Overtime
    {
        $overtime = Overtime::with('user')->findOrFail($overtimeId);

        if ($overtime->user->manager_id !== $manager->id && $manager->role !== 'admin') {
            throw new ModelNotFoundException('Overtime record not found or access denied.');
        }

        if ($overtime->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Pengajuan lembur ini sudah diproses sebelumnya.']
            ]);
        }

        $overtime->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $manager->id,
            'approved_at' => Carbon::now(),
        ]);

        return $overtime;
    }
}
