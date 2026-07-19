@extends('layouts.app')

@section('title', 'Karyawan')
@section('header-title', 'Manajemen Karyawan')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Daftar Karyawan</h2>
        <p class="page-subtitle">Kelola data karyawan, posisi, dan akses sistem.</p>
    </div>
    @if(Auth::user()->role === 'admin')
    <a href="{{ route('web.employees.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i>
        <span>Tambah Karyawan</span>
    </a>
    @endif
</div>

<div class="card">
    <div class="toolbar">
        <form action="{{ route('web.employees.index') }}" method="GET" class="search-box">
            <i data-lucide="search"></i>
            <input type="text" name="search" placeholder="Cari nama atau email..." value="{{ request('search') }}">
        </form>
    </div>

    @if($employees->isEmpty())
        <div class="empty-state">
            <i data-lucide="users"></i>
            <p>Belum ada data karyawan atau pencarian tidak ditemukan.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Posisi & Dept</th>
                        <th>Role</th>
                        <th>Status</th>
                        @if(Auth::user()->role === 'admin')
                        <th class="text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $employee->name }}</div>
                            <div class="text-muted text-sm">{{ $employee->email }}</div>
                        </td>
                        <td>
                            <div class="font-medium">{{ $employee->position->name ?? '-' }}</div>
                            <div class="text-muted text-sm">{{ $employee->department->name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge badge-neutral" style="text-transform: capitalize;">{{ $employee->role === 'employee' ? 'Karyawan' : $employee->role }}</span>
                        </td>
                        <td>
                            @if($employee->is_active)
                                <span class="badge badge-active">Aktif</span>
                            @else
                                <span class="badge badge-inactive">Nonaktif</span>
                            @endif
                        </td>
                        @if(Auth::user()->role === 'admin')
                        <td class="text-right">
                            <div class="btn-group" style="justify-content: flex-end;">
                                <a href="{{ route('web.employees.edit', $employee) }}" class="btn btn-sm btn-ghost">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                @if($employee->id !== auth()->id())
                                <form action="{{ route('web.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--danger);">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            {{ $employees->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
