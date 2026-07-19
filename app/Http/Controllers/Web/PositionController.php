<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')->withCount('users')->get();
        return view('positions.index', compact('positions'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('positions.form', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id'
        ]);
        
        Position::create($data);
        return redirect()->route('web.positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position)
    {
        $departments = Department::all();
        return view('positions.form', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id'
        ]);
        
        $position->update($data);
        return redirect()->route('web.positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        if ($position->users()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus jabatan yang sedang digunakan karyawan.');
        }
        
        $position->delete();
        return redirect()->route('web.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
