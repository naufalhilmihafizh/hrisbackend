@extends('layouts.app')

@section('title', 'Laporan HR')
@section('header-title', 'Laporan HR')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Laporan HR Ringkas</h2>
        <p class="page-subtitle">Ringkasan absensi, cuti, lembur, payroll, dan aktivitas karyawan.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i data-lucide="clipboard-list"></i></div>
        <div class="stat-label">Laporan Absensi (Total)</div>
        <div class="stat-value">{{ $summary['attendance_total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i data-lucide="calendar-off"></i></div>
        <div class="stat-label">Laporan Cuti (Total)</div>
        <div class="stat-value">{{ $summary['leave_total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i data-lucide="timer"></i></div>
        <div class="stat-label">Laporan Lembur (Total)</div>
        <div class="stat-value">{{ $summary['overtime_total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i data-lucide="wallet"></i></div>
        <div class="stat-label">Laporan Payroll (Total)</div>
        <div class="stat-value">Rp {{ number_format($summary['payroll_total'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="form-row">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Rekap Bulan Berjalan</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <tbody>
                    <tr><th>Absensi Bulan Ini</th><td>{{ $monthly['attendance'] }}</td></tr>
                    <tr><th>Cuti Bulan Ini</th><td>{{ $monthly['leave'] }}</td></tr>
                    <tr><th>Lembur Bulan Ini</th><td>{{ $monthly['overtime'] }}</td></tr>
                    <tr><th>Payroll Bulan Ini</th><td>Rp {{ number_format($monthly['payroll'], 0, ',', '.') }}</td></tr>
                    <tr><th>Karyawan Aktif</th><td>{{ $summary['active_employees'] }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Rekap Aktivitas Karyawan</h3></div>
        <div class="table-wrapper">
            <table class="data-table">
                <tbody>
                    <tr><th>Hadir Hari Ini</th><td>{{ $activityRecap['present_today'] }}</td></tr>
                    <tr><th>Pengajuan Cuti Pending</th><td>{{ $activityRecap['pending_leaves'] }}</td></tr>
                    <tr><th>Pengajuan Lembur Pending</th><td>{{ $activityRecap['pending_overtimes'] }}</td></tr>
                    <tr><th>Payroll Diproses / Dibayar</th><td>{{ $activityRecap['processed_payrolls'] }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
