@extends('layouts.app')

@section('title', isset($employee) ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('header-title', 'Manajemen Karyawan')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ isset($employee) ? 'Edit Karyawan' : 'Tambah Karyawan Baru' }}</h2>
        <p class="page-subtitle">{{ isset($employee) ? 'Perbarui informasi karyawan.' : 'Masukkan detail karyawan baru ke dalam sistem.' }}</p>
    </div>
    <a href="{{ route('web.employees.index') }}" class="btn btn-secondary">
        <i data-lucide="arrow-left"></i>
        <span>Kembali</span>
    </a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ isset($employee) ? route('web.employees.update', $employee) : route('web.employees.store') }}" method="POST">
        @csrf
        @if(isset($employee))
            @method('PUT')
        @endif

        <h3 class="heading-3 mb-4">Informasi Dasar</h3>
        <div class="form-row mb-4">
            <div class="form-group">
                <label class="form-label" for="name">Nama Lengkap <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name ?? '') }}" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email <span style="color:var(--danger);">*</span></label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email ?? '') }}" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row mb-4">
            <div class="form-group">
                <label class="form-label" for="password">Password {{ isset($employee) ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($employee) ? '' : 'required' }}>
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            
            <div class="form-group">
                <label class="form-label" for="role">Hak Akses (Role) <span style="color:var(--danger);">*</span></label>
                <select id="role" name="role" class="form-control form-select @error('role') is-invalid @enderror" required>
                    <option value="employee" {{ old('role', $employee->role ?? '') == 'employee' ? 'selected' : '' }}>Karyawan</option>
                    <option value="manager" {{ old('role', $employee->role ?? '') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="admin" {{ old('role', $employee->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <hr style="border:0; border-top:1px solid var(--border); margin:24px 0;">
        <h3 class="heading-3 mb-4">Informasi Pekerjaan</h3>

        <div class="form-row mb-4">
            <div class="form-group">
                <label class="form-label" for="department_id">Departemen <span style="color:var(--danger);">*</span></label>
                <select id="department_id" name="department_id" class="form-control form-select @error('department_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="position_id">Jabatan <span style="color:var(--danger);">*</span></label>
                <select id="position_id" name="position_id" class="form-control form-select @error('position_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}" data-dept="{{ $pos->department_id }}" {{ old('position_id', $employee->position_id ?? '') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                    @endforeach
                </select>
                @error('position_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row mb-4">
            <div class="form-group">
                <label class="form-label" for="manager_id">Manajer (Atasan)</label>
                <select id="manager_id" name="manager_id" class="form-control form-select @error('manager_id') is-invalid @enderror">
                    <option value="">-- Tidak Ada --</option>
                    @foreach($managers as $mgr)
                        <option value="{{ $mgr->id }}" {{ old('manager_id', $employee->manager_id ?? '') == $mgr->id ? 'selected' : '' }}>{{ $mgr->name }} ({{ ucfirst($mgr->role) }})</option>
                    @endforeach
                </select>
                @error('manager_id')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="join_date">Tanggal Bergabung <span style="color:var(--danger);">*</span></label>
                <input type="date" id="join_date" name="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', isset($employee) ? $employee->join_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                @error('join_date')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group mb-6">
            <label class="form-label" for="base_salary">Gaji Pokok (Rp) <span style="color:var(--danger);">*</span></label>
            <input type="number" id="base_salary" name="base_salary" class="form-control @error('base_salary') is-invalid @enderror" value="{{ old('base_salary', $employee->base_salary ?? 0) }}" min="0" required>
            @error('base_salary')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        @if(isset($employee))
        <div class="form-group mb-6" style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} style="width:16px;height:16px;">
            <label for="is_active" class="form-label" style="margin-bottom:0; cursor:pointer;">Akun Aktif</label>
        </div>
        @endif

        <div class="flex justify-end items-center mt-6 pt-4" style="border-top:1px solid var(--border);">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i>
                <span>Simpan Data Karyawan</span>
            </button>
        </div>
    </form>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const managerSelect = document.getElementById('manager_id');
        
        function toggleManagerSelect() {
            if (roleSelect.value === 'manager' || roleSelect.value === 'admin') {
                managerSelect.value = '';
                managerSelect.disabled = true;
            } else {
                managerSelect.disabled = false;
            }
        }
        
        // Listen for changes on the role dropdown
        roleSelect.addEventListener('change', toggleManagerSelect);
        
        // Run immediately on page load (useful for Edit mode or if validation fails)
        toggleManagerSelect();
    });
</script>
@endpush
@endsection
