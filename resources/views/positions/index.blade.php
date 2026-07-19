@extends('layouts.app')

@section('title', 'Jabatan')
@section('header-title', 'Manajemen Jabatan')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Daftar Jabatan</h2>
        <p class="page-subtitle">Kelola posisi dan jabatan dalam perusahaan.</p>
    </div>
    <a href="{{ route('web.positions.create') }}" class="btn btn-primary">
        <i data-lucide="plus"></i>
        <span>Tambah Jabatan</span>
    </a>
</div>

<div class="card">
    @if($positions->isEmpty())
        <div class="empty-state">
            <i data-lucide="briefcase"></i>
            <p>Belum ada data jabatan. Silakan tambah jabatan baru.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Jabatan</th>
                        <th>Departemen</th>
                        <th>Jumlah Karyawan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($positions as $position)
                    <tr>
                        <td class="font-medium">{{ $position->name }}</td>
                        <td>{{ $position->department->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-neutral">
                                <i data-lucide="users" style="width:12px;height:12px;"></i>
                                {{ $position->users_count }}
                            </span>
                        </td>
                        <td class="text-right">
                            <div class="btn-group" style="justify-content: flex-end;">
                                <a href="{{ route('web.positions.edit', $position) }}" class="btn btn-sm btn-ghost">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                <form action="{{ route('web.positions.destroy', $position) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');" style="display:inline-block;">
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
