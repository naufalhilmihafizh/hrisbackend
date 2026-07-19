@extends('layouts.app')

@section('title', 'Akun Saya')
@section('header-title', 'Akun Saya')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Profil Admin</h2>
        <p class="page-subtitle">Kelola data profil dan keamanan akun.</p>
    </div>
</div>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Profil</h3>
        </div>
        <form method="POST" action="{{ route('web.account.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', auth()->user()->address) }}</textarea>
                </div>
            </div>
            <div class="toolbar" style="justify-content:flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i> Simpan Profil
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ route('web.account.password.update') }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="toolbar" style="justify-content:flex-end;">
                <button type="submit" class="btn btn-secondary">
                    <i data-lucide="lock"></i> Perbarui Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
