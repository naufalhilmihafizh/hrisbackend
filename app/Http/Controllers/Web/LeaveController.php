<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with(['user.department', 'approver']);

        if (auth()->user()->role === 'manager') {
            $query->whereHas('user', function($q) {
                $q->where('manager_id', auth()->id());
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('leaves.index', compact('leaves'));
    }

    public function update(Request $request, Leave $leave)
    {
        if (auth()->user()->role === 'manager' && $leave->user->manager_id != auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk menyetujui pengajuan ini.');
        }

        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_notes' => 'nullable|string'
        ]);

        $leave->update([
            'status' => $data['status'],
            'approved_by' => auth()->id(),
            'manager_notes' => $data['manager_notes'] ?? null
        ]);

        return back()->with('success', 'Status pengajuan cuti berhasil diperbarui.');
    }
}
