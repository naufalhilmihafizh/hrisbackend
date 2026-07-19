<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOvertimeRequest;
use App\Http\Requests\Api\RejectRequest;
use App\Http\Resources\OvertimeResource;
use App\Services\OvertimeService;
use App\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OvertimeController extends Controller
{
    protected OvertimeService $overtimeService;
    protected ApprovalService $approvalService;

    public function __construct(OvertimeService $overtimeService, ApprovalService $approvalService)
    {
        $this->overtimeService = $overtimeService;
        $this->approvalService = $approvalService;
    }

    /**
     * Display a listing of employee's own overtime requests.
     */
    public function index(Request $request): JsonResponse
    {
        $overtimes = $this->overtimeService->getHistory($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan lembur berhasil diambil.',
            'data' => OvertimeResource::collection($overtimes),
        ]);
    }

    /**
     * Store a newly created overtime request.
     */
    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        try {
            $overtime = $this->overtimeService->requestOvertime($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan lembur berhasil diajukan.',
                'data' => new OvertimeResource($overtime),
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
     * Display the specified overtime request.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $overtime = $this->overtimeService->getOvertimeDetail($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Detail pengajuan lembur berhasil diambil.',
                'data' => new OvertimeResource($overtime),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan lembur tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * Display pending overtimes of subordinates (Manager only).
     */
    public function pending(Request $request): JsonResponse
    {
        $overtimes = $this->overtimeService->getPendingOvertimes($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengajuan lembur pending berhasil diambil.',
            'data' => OvertimeResource::collection($overtimes),
        ]);
    }

    /**
     * Approve an overtime request (Manager only).
     */
    public function approve(int $id, Request $request): JsonResponse
    {
        try {
            $overtime = $this->approvalService->approveOvertime($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan lembur berhasil disetujui.',
                'data' => new OvertimeResource($overtime),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan lembur tidak ditemukan.',
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
     * Reject an overtime request (Manager only).
     */
    public function reject(int $id, RejectRequest $request): JsonResponse
    {
        try {
            $overtime = $this->approvalService->rejectOvertime(
                $request->user(),
                $id,
                $request->rejection_reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan lembur ditolak.',
                'data' => new OvertimeResource($overtime),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan lembur tidak ditemukan.',
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
