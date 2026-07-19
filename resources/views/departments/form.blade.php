@extends('layouts.app')

@section('title', isset($department) ? 'Edit Departemen' : 'Tambah Departemen')
@section('header-title', 'Manajemen Departemen')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">{{ isset($department) ? 'Edit Departemen' : 'Tambah Departemen Baru' }}</h2>
        <p class="page-subtitle">{{ isset($department) ? 'Perbarui informasi departemen.' : 'Masukkan detail departemen baru.' }}</p>
    </div>
    <a href="{{ route('web.departments.index') }}" class="btn btn-secondary">
        <i data-lucide="arrow-left"></i>
        <span>Kembali</span>
    </a>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ isset($department) ? route('web.departments.update', $department) : route('web.departments.store') }}" method="POST">
        @csrf
        @if(isset($department))
            @method('PUT')
        @endif

        <div class="form-group">
            <label class="form-label" for="name">Nama Departemen <span style="color:var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name ?? '') }}" required>
            @error('name')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-6">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $department->description ?? '') }}</textarea>
            @error('description')
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
