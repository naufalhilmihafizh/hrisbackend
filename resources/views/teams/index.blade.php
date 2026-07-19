@extends('layouts.app')

@section('title', 'Monitoring Tim')
@section('header-title', 'Monitoring Tim')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Monitoring Aktivitas Tim</h2>
        <p class="page-subtitle">Pantau data tim, status kehadiran, dan aktivitas terbaru bawahan.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i data-lucide="users"></i></div>
        <div class="stat-label">Jumlah Anggota Tim</div>
        <div class="stat-value">{{ $summary['team_count'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i data-lucide="check-circle"></i></div>
        <div class="stat-label">Hadir Hari Ini</div>
        <div class="stat-value">{{ $summary['present_today'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-label">Terlambat Hari Ini</div>
        <div class="stat-value">{{ $summary['late_today'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i data-lucide="x-circle"></i></div>
        <div class="stat-label">Belum Absensi</div>
        <div class="stat-value">{{ $summary['absent_today'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i data-lucide="calendar-off"></i></div>
        <div class="stat-label">Cuti Pending Tim</div>
        <div class="stat-value">{{ $summary['pending_leaves'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info"><i data-lucide="timer"></i></div>
        <div class="stat-label">Lembur Pending Tim</div>
        <div class="stat-value">{{ $summary['pending_overtimes'] }}</div>
    </div>
</div>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Tim</h3>
        </div>
        <div class="toolbar">
            <form action="{{ route('web.teams.index') }}" method="GET" class="search-box">
                <i data-lucide="search"></i>
                <input type="text" name="search" placeholder="Cari nama atau email anggota tim..." value="{{ request('search') }}">
            </form>
        </div>

        @if($teamMembers->isEmpty())
            <div class="empty-state">
                <i data-lucide="users"></i>
                <p>Tidak ada anggota tim yang ditemukan.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Departemen</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teamMembers as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->position->name ?? '-' }}</td>
                            <td>{{ $member->department->name ?? '-' }}</td>
                            <td>{{ $member->email }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end">
                {{ $teamMembers->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Histori Absensi Tim (Terbaru)</h3>
        </div>
        @if($attendanceHistory->isEmpty())
            <div class="empty-state">
                <i data-lucide="clock"></i>
                <p>Belum ada histori absensi tim.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceHistory as $attendance)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                            <td>{{ $attendance->user->name ?? '-' }}</td>
                            <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                            <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                            <td>
                                @if($attendance->status === 'present')
                                    <span class="badge badge-present">Hadir</span>
                                @elseif($attendance->status === 'late')
                                    <span class="badge badge-late">Terlambat</span>
                                @else
                                    <span class="badge badge-absent">Tidak Hadir</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
