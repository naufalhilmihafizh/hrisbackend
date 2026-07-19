<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeaveRequest;
use App\Http\Requests\Api\RejectRequest;
use App\Http\Resources\LeaveResource;
use App\Services\LeaveService;
use App\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LeaveController extends Controller
{
    protected LeaveService $leaveService;
    protected ApprovalService $approvalService;

    public function __construct(LeaveService $leaveService, ApprovalService $approvalService)
    {
        $this->leaveService = $leaveService;
        $this->approvalService = $approvalService;
    }

    /**
     * Display a listing of employee's own leave requests.
     */
    public function index(Request $request): JsonResponse
    {
        $leaves = $this->leaveService->getHistory($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan cuti berhasil diambil.',
            'data' => LeaveResource::collection($leaves),
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        try {
            $leave = $this->leaveService->requestLeave($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil diajukan.',
                'data' => new LeaveResource($leave),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified leave request.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $leave = $this->leaveService->getLeaveDetail($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Detail pengajuan cuti berhasil diambil.',
                'data' => new LeaveResource($leave),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * Display pending leaves of subordinates (Manager only).
     */
    public function pending(Request $request): JsonResponse
    {
        $leaves = $this->leaveService->getPendingLeaves($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan cuti pending berhasil diambil.',
            'data' => LeaveResource::collection($leaves),
        ]);
    }

    /**
     * Approve a leave request (Manager only).
     */
    public function approve(int $id, Request $request): JsonResponse
    {
        try {
            $leave = $this->approvalService->approveLeave($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil disetujui.',
                'data' => new LeaveResource($leave),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti tidak ditemukan.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Reject a leave request (Manager only).
     */
    public function reject(int $id, RejectRequest $request): JsonResponse
    {
        try {
            $leave = $this->approvalService->rejectLeave(
                $request->user(),
                $id,
                $request->rejection_reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan cuti ditolak.',
                'data' => new LeaveResource($leave),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan cuti tidak ditemukan.',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
