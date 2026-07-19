<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\TeamMonitoringController;

Route::get('/', function () {
    return redirect()->route('web.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('web.login');
    Route::post('/login', [AuthController::class, 'login'])->name('web.login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('web.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('web.dashboard');
    Route::get('/account/profile', [AccountController::class, 'edit'])->name('web.account.profile');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('web.account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('web.account.password.update');

    // Admin Only: Master Data, CRUD employees, and Payroll management
    Route::middleware('role:admin')->group(function () {
        Route::resource('employees', \App\Http\Controllers\Web\EmployeeController::class)->except(['index', 'show'])->names('web.employees');
        Route::resource('departments', \App\Http\Controllers\Web\DepartmentController::class)->except(['show'])->names('web.departments');
        Route::resource('positions', \App\Http\Controllers\Web\PositionController::class)->except(['show'])->names('web.positions');
        Route::get('/reports', [ReportController::class, 'index'])->name('web.reports.index');
        
        Route::post('/payrolls/generate', [\App\Http\Controllers\Web\PayrollController::class, 'generate'])->name('web.payrolls.generate');
        Route::post('/payrolls/{payroll}/process', [\App\Http\Controllers\Web\PayrollController::class, 'process'])->name('web.payrolls.process');
    });

    // Admin & Manager: Monitoring, Team viewing, Approvals, and Payroll slips viewing
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/employees', [\App\Http\Controllers\Web\EmployeeController::class, 'index'])->name('web.employees.index');
        Route::get('/teams', [TeamMonitoringController::class, 'index'])->name('web.teams.index');
        Route::get('/attendances', [\App\Http\Controllers\Web\AttendanceController::class, 'index'])->name('web.attendances.index');
        
        Route::get('/leaves', [\App\Http\Controllers\Web\LeaveController::class, 'index'])->name('web.leaves.index');
        Route::put('/leaves/{leave}', [\App\Http\Controllers\Web\LeaveController::class, 'update'])->name('web.leaves.update');
        
        Route::get('/overtimes', [\App\Http\Controllers\Web\OvertimeController::class, 'index'])->name('web.overtimes.index');
        Route::put('/overtimes/{overtime}', [\App\Http\Controllers\Web\OvertimeController::class, 'update'])->name('web.overtimes.update');
        
        Route::get('/payrolls', [\App\Http\Controllers\Web\PayrollController::class, 'index'])->name('web.payrolls.index');
        Route::get('/payrolls/{payroll}', [\App\Http\Controllers\Web\PayrollController::class, 'show'])->name('web.payrolls.show');
    });
});
