@extends('layouts.app')

@section('title', 'Absensi')
@section('header-title', 'Pemantauan Absensi')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Riwayat Absensi</h2>
        <p class="page-subtitle">Pantau kehadiran harian karyawan.</p>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form action="{{ route('web.attendances.index') }}" method="GET" class="flex gap-4 w-full">
            <div class="form-group" style="margin-bottom:0;">
                <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <select name="user_id" class="form-control form-select">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string) request('user_id') === (string) $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <select name="status" class="form-control form-select">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Alpha/Absen</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('web.attendances.index') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <i data-lucide="clock"></i>
            <p>Tidak ada data absensi untuk filter yang dipilih.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Lokasi Masuk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}</td>
                        <td>
                            <div class="font-medium">{{ $attendance->user->name }}</div>
                            <div class="text-muted text-sm">{{ $attendance->user->department->name ?? '-' }}</div>
                        </td>
                        <td class="{{ $attendance->status === 'late' ? 'text-danger font-medium' : '' }}">
                            {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}
                        </td>
                        <td>
                            {{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}
                        </td>
                        <td>
                            @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                <a href="https://maps.google.com/?q={{ $attendance->check_in_latitude }},{{ $attendance->check_in_longitude }}" target="_blank" class="btn btn-sm btn-ghost" title="Lihat Peta">
                                    <i data-lucide="map-pin"></i> Peta
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($attendance->status == 'present')
                                <span class="badge badge-present">Hadir</span>
                            @elseif($attendance->status == 'late')
                                <span class="badge badge-late">Terlambat</span>
                            @elseif($attendance->status == 'absent')
                                <span class="badge badge-absent">Absen</span>
                            @else
                                <span class="badge badge-neutral">{{ ucfirst($attendance->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            {{ $attendances->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
