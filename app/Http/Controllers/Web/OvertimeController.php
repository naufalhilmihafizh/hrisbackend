<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $query = Overtime::with(['user.department', 'approver']);

        if (auth()->user()->role === 'manager') {
            $query->whereHas('user', function($q) {
                $q->where('manager_id', auth()->id());
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $overtimes = $query->orderBy('overtime_date', 'desc')->paginate(15);
        return view('overtimes.index', compact('overtimes'));
    }

    public function update(Request $request, Overtime $overtime)
    {
        if (auth()->user()->role === 'manager' && $overtime->user->manager_id != auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk menyetujui pengajuan ini.');
        }

        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_notes' => 'nullable|string'
        ]);

        $overtime->update([
            'status' => $data['status'],
            'approved_by' => auth()->id(),
            'manager_notes' => $data['manager_notes'] ?? null
        ]);

        return back()->with('success', 'Status pengajuan lembur berhasil diperbarui.');
    }
}
