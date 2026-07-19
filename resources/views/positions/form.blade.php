@extends('layouts.app')

@section('title', isset($position) ? 'Edit Jabatan' : 'Tambah Jabatan')
@section('header-title', 'Manajemen Jabatan')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ isset($position) ? 'Edit Jabatan' : 'Tambah Jabatan Baru' }}</h2>
        <p class="page-subtitle">{{ isset($position) ? 'Perbarui informasi jabatan.' : 'Masukkan detail jabatan baru.' }}</p>
    </div>
    <a href="{{ route('web.positions.index') }}" class="btn btn-secondary">
        <i data-lucide="arrow-left"></i>
        <span>Kembali</span>
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ isset($position) ? route('web.positions.update', $position) : route('web.positions.store') }}" method="POST">
        @csrf
        @if(isset($position))
            @method('PUT')
        @endif

        <div class="form-group">
            <label class="form-label" for="name">Nama Jabatan <span style="color:var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $position->name ?? '') }}" required>
            @error('name')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-6">
            <label class="form-label" for="department_id">Departemen <span style="color:var(--danger);">*</span></label>
            <select id="department_id" name="department_id" class="form-control form-select @error('department_id') is-invalid @enderror" required>
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $position->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-between items-center">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i>
                <span>Simpan Data</span>
            </button>
        </div>
    </form>
</div>
@endsection
