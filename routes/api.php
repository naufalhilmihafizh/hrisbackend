<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

    // Dashboard route (displays stats according to role)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Employee attendance routes (personal attendance only)
    Route::middleware('role:employee')->group(function () {
        Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/attendances/today', [AttendanceController::class, 'today']);
        Route::get('/attendances/monthly', [AttendanceController::class, 'monthly']);
        Route::get('/attendances', [AttendanceController::class, 'index']);
    });

    // Manager / Admin Attendance routes
    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/attendances/team', [AttendanceController::class, 'team']);
        Route::get('/attendances/team-history', [AttendanceController::class, 'teamHistory']);
    });

    // Leave routes (Employee own actions)
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves', [LeaveController::class, 'index']);

    // Manager / Admin Leave approvals
    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/leaves/pending', [LeaveController::class, 'pending']);
        Route::put('/leaves/{id}/approve', [LeaveController::class, 'approve']);
        Route::put('/leaves/{id}/reject', [LeaveController::class, 'reject']);
    });

    // Leave show route (needs to be after pending)
    Route::get('/leaves/{id}', [LeaveController::class, 'show']);

    // Overtime routes (Employee own actions)
    Route::post('/overtimes', [OvertimeController::class, 'store']);
    Route::get('/overtimes', [OvertimeController::class, 'index']);

    // Manager / Admin Overtime approvals
    Route::middleware('role:manager,admin')->group(function () {
        Route::get('/overtimes/pending', [OvertimeController::class, 'pending']);
        Route::put('/overtimes/{id}/approve', [OvertimeController::class, 'approve']);
        Route::put('/overtimes/{id}/reject', [OvertimeController::class, 'reject']);
    });

    // Overtime show route (needs to be after pending)
    Route::get('/overtimes/{id}', [OvertimeController::class, 'show']);

    // Payroll routes (Employee own slips)
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::get('/payrolls/latest', [PayrollController::class, 'latest']);
    Route::get('/payrolls/{id}', [PayrollController::class, 'show']);
});

