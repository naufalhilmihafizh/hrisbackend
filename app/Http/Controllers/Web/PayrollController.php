<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['user.department']);

        if (auth()->user()->role === 'manager') {
            $query->where('user_id', auth()->id());
        } else {
            if ($request->filled('period')) {
                $parts = explode('-', $request->period);
                if (count($parts) == 2) {
                    $query->where('period_year', $parts[0])
                          ->where('period_month', $parts[1]);
                }
            } else {
                $query->where('period_year', now()->year)
                      ->where('period_month', now()->month);
            }
        }

        $payrolls = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::where('is_active', true)->get();
        
        return view('payrolls.index', compact('payrolls', 'users'));
    }

    public function generate(Request $request, PayrollService $payrollService)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|date_format:Y-m'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $parts = explode('-', $request->period);
            $year = (int)$parts[0];
            $month = (int)$parts[1];
            
            $payrollService->processPayroll($user, $month, $year);
            return back()->with('success', 'Payroll berhasil dibuat untuk periode tersebut.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat payroll: ' . $e->getMessage());
        }
    }

    public function process(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', 'Hanya payroll berstatus draft yang dapat diproses.');
        }

        $payroll->update([
            'status' => 'processed',
            'payment_date' => now()
        ]);

        return back()->with('success', 'Payroll berhasil diproses.');
    }

    public function show(Payroll $payroll)
    {
        if (auth()->user()->role === 'manager' && $payroll->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat slip gaji ini.');
        }

        $payroll->load('user.department', 'user.position');
        return view('payrolls.show', compact('payroll'));
    }
}
