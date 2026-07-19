@extends('layouts.app')

@section('title', 'Slip Gaji')
@section('header-title', 'Detail Payroll')

@section('content')
<div class="page-header no-print">
    <div>
        <h2 class="page-title">Slip Gaji</h2>
        <p class="page-subtitle">Periode: {{ \Carbon\Carbon::parse($payroll->period_year . '-' . str_pad($payroll->period_month, 2, '0', STR_PAD_LEFT) . '-01')->format('F Y') }}</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('web.payrolls.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Kembali
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i data-lucide="printer"></i> Cetak PDF
        </button>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="text-center mb-6 pb-6" style="border-bottom: 2px dashed var(--border);">
        <h1 class="heading-2" style="font-family:'Plus Jakarta Sans',sans-serif; color:var(--primary);">SLIP GAJI KARYAWAN</h1>
        <p class="text-muted">Periode: {{ \Carbon\Carbon::parse($payroll->period_year . '-' . str_pad($payroll->period_month, 2, '0', STR_PAD_LEFT) . '-01')->format('F Y') }}</p>
    </div>

    <div class="form-row mb-6">
        <div>
            <p class="text-sm text-muted mb-1">Informasi Karyawan</p>
            <h3 class="font-semibold" style="font-size:1.125rem;">{{ $payroll->user->name }}</h3>
            <p class="text-sm">{{ $payroll->user->position->name ?? '-' }} — {{ $payroll->user->department->name ?? '-' }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-muted mb-1">Status Pembayaran</p>
            @if($payroll->status == 'processed' || $payroll->status == 'paid')
                <div class="badge badge-paid" style="font-size:1rem; padding:4px 12px;">LUNAS ({{ $payroll->paid_at ? \Carbon\Carbon::parse($payroll->paid_at)->format('d M Y') : '-' }})</div>
            @else
                <div class="badge badge-draft" style="font-size:1rem; padding:4px 12px;">DRAFT / BELUM DIBAYAR</div>
            @endif
        </div>
    </div>

    <div class="mb-6">
        <table style="width:100%; border-collapse:collapse; font-size:0.9375rem;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px 0; border-bottom:1px solid var(--border);">Keterangan</th>
                    <th style="text-align:right; padding:8px 0; border-bottom:1px solid var(--border);">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Pendapatan -->
                <tr>
                    <td style="padding:12px 0 4px; font-weight:600;" colspan="2">Penerimaan</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:var(--text-secondary);">Gaji Pokok</td>
                    <td style="padding:8px 0; text-align:right;">{{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                </tr>
                @if($payroll->overtime_pay > 0)
                <tr>
                    <td style="padding:8px 0; color:var(--text-secondary);">Uang Lembur</td>
                    <td style="padding:8px 0; text-align:right;">{{ number_format($payroll->overtime_pay, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:8px 0; font-weight:600; border-bottom:1px solid var(--border-light);">Total Penerimaan</td>
                    <td style="padding:8px 0; text-align:right; font-weight:600; border-bottom:1px solid var(--border-light);">{{ number_format($payroll->base_salary + $payroll->overtime_pay, 0, ',', '.') }}</td>
                </tr>

                <!-- Potongan -->
                <tr>
                    <td style="padding:16px 0 4px; font-weight:600;" colspan="2">Potongan</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:var(--text-secondary);">Potongan Absen/Keterlambatan</td>
                    <td style="padding:8px 0; text-align:right;">{{ number_format($payroll->deductions, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-weight:600; border-bottom:1px solid var(--border-light);">Total Potongan</td>
                    <td style="padding:8px 0; text-align:right; font-weight:600; border-bottom:1px solid var(--border-light);">{{ number_format($payroll->deductions, 0, ',', '.') }}</td>
                </tr>
                
                <!-- Take Home Pay -->
                <tr>
                    <td style="padding:24px 0 8px; font-weight:700; font-size:1.125rem;">Take Home Pay</td>
                    <td style="padding:24px 0 8px; text-align:right; font-weight:700; font-size:1.25rem; color:var(--primary);">Rp {{ number_format($payroll->total_salary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-10 pt-6" style="border-top:1px solid var(--border);">
        <p class="text-center text-sm text-muted">Dokumen ini dihasilkan secara otomatis oleh sistem HRIS. Segala bentuk komplain maksimal 3 hari kerja setelah diterbitkan.</p>
    </div>
</div>
@endsection
