@extends('layouts.app')

@section('title', 'Departemen')
@section('header-title', 'Manajemen Departemen')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Daftar Departemen</h2>
        <p class="page-subtitle">Kelola unit kerja dan departemen perusahaan.</p>
    </div>
    <a href="{{ route('web.departments.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i>
        <span>Tambah Departemen</span>
    </a>
</div>

<div class="card">
    @if($departments->isEmpty())
        <div class="empty-state">
            <i data-lucide="building-2"></i>
            <p>Belum ada data departemen. Silakan tambah departemen baru.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Departemen</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Karyawan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td class="font-medium">{{ $dept->name }}</td>
                        <td class="text-muted">{{ $dept->description ?: '-' }}</td>
                        <td>
                            <span class="badge badge-info">
                                <i data-lucide="users" style="width:12px;height:12px;"></i>
                                {{ $dept->users_count }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="btn-group" style="justify-content: flex-end;">
                                <a href="{{ route('web.departments.edit', $dept) }}" class="btn btn-sm btn-ghost">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                <form action="{{ route('web.departments.destroy', $dept) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?');" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--danger);">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
