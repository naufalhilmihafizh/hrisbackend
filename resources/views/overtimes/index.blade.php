@extends('layouts.app')

@section('title', 'Lembur')
@section('header-title', 'Manajemen Lembur')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Pengajuan Lembur</h2>
        <p class="page-subtitle">Persetujuan dan riwayat lembur (overtime) karyawan.</p>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form action="{{ route('web.overtimes.index') }}" method="GET" class="flex gap-4 w-full">
            <div class="form-group" style="margin-bottom:0;">
                <select name="status" class="form-control form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('web.overtimes.index') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>

    @if($overtimes->isEmpty())
        <div class="empty-state">
            <i data-lucide="timer"></i>
            <p>Tidak ada data pengajuan lembur.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Karyawan</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Disetujui Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overtimes as $overtime)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($overtime->overtime_date)->format('d M Y') }}</td>
                        <td>
                            <div class="font-medium">{{ $overtime->user->name }}</div>
                            <div class="text-muted text-sm">{{ $overtime->user->department->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-medium">{{ $overtime->duration_hours }} Jam</div>
                        </td>
                        <td>
                            @if($overtime->status == 'approved')
                                <span class="badge badge-approved">Disetujui</span>
                            @elseif($overtime->status == 'pending')
                                <span class="badge badge-pending">Menunggu</span>
                            @elseif($overtime->status == 'rejected')
                                <span class="badge badge-rejected">Ditolak</span>
                            @endif
                        </td>
                        <td>{{ $overtime->approver->name ?? '-' }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="openOvertimeModal({{ json_encode([
                                'id' => $overtime->id,
                                'user' => $overtime->user->name,
                                'date' => \Carbon\Carbon::parse($overtime->overtime_date)->format('d M Y'),
                                'time' => $overtime->duration_hours . ' Jam',
                                'reason' => $overtime->reason,
                                'status' => $overtime->status,
                                'manager_notes' => $overtime->manager_notes ?? '',
                                'url' => route('web.overtimes.update', $overtime->id)
                            ]) }})">
                                Review
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-end">
            {{ $overtimes->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

<!-- Overtime Modal -->
<div class="modal-overlay" id="overtimeModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Review Pengajuan Lembur</h3>
            <button class="modal-close" onclick="closeOvertimeModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form id="overtimeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Karyawan</label>
                <div class="form-control" id="modalUser" style="background:var(--bg-secondary);"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal</label>
                    <div class="form-control" id="modalDate" style="background:var(--bg-secondary);"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Waktu & Durasi</label>
                    <div class="form-control" id="modalTime" style="background:var(--bg-secondary);"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alasan Pekerjaan</label>
                <div class="form-control" id="modalReason" style="background:var(--bg-secondary); min-height:60px;"></div>
            </div>
            
            <div class="form-group mt-4">
                <label class="form-label" for="overtimeStatus">Aksi Persetujuan</label>
                <select name="status" id="overtimeStatus" class="form-control form-select" required>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="approved">Setujui (Approve)</option>
                    <option value="rejected">Tolak (Reject)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="managerNotes">Catatan Atasan (Opsional)</label>
                <textarea name="manager_notes" id="managerNotes" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeOvertimeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openOvertimeModal(data) {
        document.getElementById('modalUser').innerText = data.user;
        document.getElementById('modalDate').innerText = data.date;
        document.getElementById('modalTime').innerText = data.time;
        document.getElementById('modalReason').innerText = data.reason;
        document.getElementById('overtimeStatus').value = data.status;
        document.getElementById('managerNotes').value = data.manager_notes || '';
        document.getElementById('overtimeForm').action = data.url;
        
        document.getElementById('overtimeModal').classList.add('show');
    }
    function closeOvertimeModal() {
        document.getElementById('overtimeModal').classList.remove('show');
    }
</script>
@endpush
@endsection
