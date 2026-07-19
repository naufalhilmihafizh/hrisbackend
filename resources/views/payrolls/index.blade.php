@extends('layouts.app')

@section('title', 'Payroll')
@section('header-title', 'Manajemen Payroll')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Riwayat Penggajian</h2>
        <p class="page-subtitle">Kelola perhitungan gaji dan tunjangan karyawan.</p>
    </div>
    @if(Auth::user()->role === 'admin')
    <button class="btn btn-primary" onclick="document.getElementById('generateModal').classList.add('show')">
        <i data-lucide="calculator"></i>
        <span>Generate Payroll</span>
    </button>
    @endif
</div>

<div class="card">
    <div class="toolbar">
        <form action="{{ route('web.payrolls.index') }}" method="GET" class="flex gap-4 w-full">
            <div class="form-group" style="margin-bottom:0;">
                <input type="month" name="period" class="form-control" value="{{ request('period', date('Y-m')) }}">
            </div>
            <button type="submit" class="btn btn-secondary">Filter Periode</button>
            <a href="{{ route('web.payrolls.index') }}" class="btn btn-ghost">Bulan Ini</a>
        </form>
    </div>

    @if($payrolls->isEmpty())
        <div class="empty-state">
            <i data-lucide="wallet"></i>
            <p>Belum ada data payroll untuk periode yang dipilih.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Karyawan</th>
                        <th>Gaji Pokok</th>
                        <th>Total Terima</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payrolls as $payroll)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payroll->period_year . '-' . str_pad($payroll->period_month, 2, '0', STR_PAD_LEFT) . '-01')->format('M Y') }}</td>
                        <td>
                            <div class="font-medium">{{ $payroll->user->name }}</div>
                            <div class="text-muted text-sm">{{ $payroll->user->department->name ?? '-' }}</div>
                        </td>
                        <td>Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                        <td class="font-semibold" style="color:var(--primary);">Rp {{ number_format($payroll->total_salary, 0, ',', '.') }}</td>
                        <td>
                            @if($payroll->status == 'processed')
                                <span class="badge badge-paid">Dibayarkan</span>
                            @else
                                <span class="badge badge-draft">Draft</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="btn-group" style="justify-content: flex-end;">
                                <a href="{{ route('web.payrolls.show', $payroll) }}" class="btn btn-sm btn-ghost">
                                    <i data-lucide="file-text"></i> Slip Gaji
                                </a>
                                @if(Auth::user()->role === 'admin' && $payroll->status == 'draft')
                                <form action="{{ route('web.payrolls.process', $payroll) }}" method="POST" onsubmit="return confirm('Proses pembayaran untuk payroll ini? Status akan diubah menjadi Dibayarkan.');" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i data-lucide="check" style="width:14px;height:14px;"></i> Proses
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            {{ $payrolls->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

<!-- Generate Modal -->
<div class="modal-overlay" id="generateModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Generate Payroll Baru</h3>
            <button class="modal-close" onclick="document.getElementById('generateModal').classList.remove('show')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form action="{{ route('web.payrolls.generate') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="user_id">Pilih Karyawan</label>
                <select name="user_id" id="user_id" class="form-control form-select" required>
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->department->name ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="period">Periode Penggajian</label>
                <input type="month" id="period" name="period" class="form-control" value="{{ date('Y-m') }}" required>
                <p class="form-hint">Format: Tahun-Bulan (Contoh: {{ date('Y-m') }})</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('generateModal').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-primary">Kalkulasi Payroll</button>
            </div>
        </form>
    </div>
</div>
@endsection
