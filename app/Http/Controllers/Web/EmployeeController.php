<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'position']);

        if (auth()->user()->role === 'manager') {
            $query->where('manager_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::all();
        $positions = Position::all();
        $managers = User::whereIn('role', ['manager', 'admin'])->get();
        return view('employees.form', compact('departments', 'positions', 'managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'employee'])],
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'join_date' => 'required|date',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('web.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $employee)
    {
        $departments = Department::all();
        $positions = Position::all();
        $managers = User::whereIn('role', ['manager', 'admin'])->where('id', '!=', $employee->id)->get();
        return view('employees.form', compact('employee', 'departments', 'positions', 'managers'));
    }

    public function update(Request $request, User $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'password' => 'nullable|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'employee'])],
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'join_date' => 'required|date',
            'is_active' => 'boolean'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $employee->update($data);
        return redirect()->route('web.employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }
    
    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }
        $employee->delete();
        return redirect()->route('web.employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
