@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Overview')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i data-lucide="users"></i>
        </div>
        <div class="stat-label">Total Karyawan</div>
        <div class="stat-value">{{ $stats['total_employees'] }}</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="stat-label">Hadir Hari Ini</div>
        <div class="stat-value">{{ $stats['present_today'] }}</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i data-lucide="calendar-off"></i>
        </div>
        <div class="stat-label">Cuti Berjalan</div>
        <div class="stat-value">{{ $stats['on_leave_today'] }}</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i data-lucide="clock"></i>
        </div>
        <div class="stat-label">Cuti Pending</div>
        <div class="stat-value">{{ $stats['pending_leaves'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i data-lucide="timer"></i>
        </div>
        <div class="stat-label">Lembur Pending</div>
        <div class="stat-value">{{ $stats['pending_overtimes'] ?? 0 }}</div>
    </div>

    @if(auth()->user()->role === 'admin')
    <div class="stat-card">
        <div class="stat-icon info">
            <i data-lucide="user-check"></i>
        </div>
        <div class="stat-label">Total Manager</div>
        <div class="stat-value">{{ $stats['total_managers'] ?? 0 }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon primary">
            <i data-lucide="clipboard-list"></i>
        </div>
        <div class="stat-label">Total Absensi</div>
        <div class="stat-value">{{ $stats['total_attendances'] ?? 0 }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i data-lucide="calendar-range"></i>
        </div>
        <div class="stat-label">Total Cuti</div>
        <div class="stat-value">{{ $stats['total_leaves'] ?? 0 }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <i data-lucide="timer"></i>
        </div>
        <div class="stat-label">Total Lembur</div>
        <div class="stat-value">{{ $stats['total_overtimes'] ?? 0 }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i data-lucide="wallet"></i>
        </div>
        <div class="stat-label">Ringkasan Payroll Bulan Ini</div>
        <div class="stat-value">Rp {{ number_format((float) ($stats['payroll_summary'] ?? 0), 0, ',', '.') }}</div>
    </div>
    @endif
</div>

<div class="form-row">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Absensi Terkini</h3>
        </div>
        @if($recentAttendances->isEmpty())
            <div class="empty-state">
                <i data-lucide="inbox"></i>
                <p>Belum ada data absensi terbaru.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Waktu Masuk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAttendances as $attendance)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $attendance->user->name }}</div>
                                <div class="text-muted text-sm">{{ $attendance->user->department->name ?? '-' }}</div>
                            </td>
                            <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                            <td>
                                @if($attendance->status == 'present')
                                    <span class="badge badge-present">Hadir</span>
                                @elseif($attendance->status == 'late')
                                    <span class="badge badge-late">Terlambat</span>
                                @else
                                    <span class="badge badge-neutral">{{ ucfirst($attendance->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Menunggu Persetujuan</h3>
        </div>
        @if($pendingApprovals->isEmpty())
            <div class="empty-state">
                <i data-lucide="check-circle"></i>
                <p>Tidak ada pengajuan cuti yang menunggu persetujuan.</p>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingApprovals as $leave)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $leave->user->name }}</div>
                                <div class="text-muted text-sm">{{ $leave->type }}</div>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - 
                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }}
                            </td>
                            <td>
                                <a href="{{ route('web.leaves.index') }}" class="btn btn-sm btn-ghost">
                                    <i data-lucide="eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
