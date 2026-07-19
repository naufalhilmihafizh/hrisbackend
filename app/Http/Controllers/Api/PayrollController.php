<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayrollResource;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Get all finalized payroll slips for logged-in employee.
     */
    public function index(Request $request): JsonResponse
    {
        $payrolls = $this->payrollService->getHistory($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Daftar slip gaji berhasil diambil.',
            'data' => PayrollResource::collection($payrolls),
        ]);
    }

    /**
     * Get specific payroll slip details.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $payroll = $this->payrollService->getPayrollDetail($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Detail slip gaji berhasil diambil.',
                'data' => new PayrollResource($payroll->load('user')),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Slip gaji tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * Get the latest finalized payroll slip.
     */
    public function latest(Request $request): JsonResponse
    {
        $payroll = $this->payrollService->getLatestPayroll($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji terbaru berhasil diambil.',
            'data' => $payroll ? new PayrollResource($payroll) : null,
        ]);
    }
}
