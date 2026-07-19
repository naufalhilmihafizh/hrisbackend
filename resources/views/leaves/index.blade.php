@extends('layouts.app')

@section('title', 'Cuti')
@section('header-title', 'Manajemen Cuti')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Pengajuan Cuti</h2>
        <p class="page-subtitle">Persetujuan dan riwayat cuti karyawan.</p>
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form action="{{ route('web.leaves.index') }}" method="GET" class="flex gap-4 w-full">
            <div class="form-group" style="margin-bottom:0;">
                <select name="status" class="form-control form-select">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('web.leaves.index') }}" class="btn btn-ghost">Reset</a>
        </form>
    </div>

    @if($leaves->isEmpty())
        <div class="empty-state">
            <i data-lucide="calendar-off"></i>
            <p>Tidak ada data pengajuan cuti.</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal Pengajuan</th>
                        <th>Karyawan</th>
                        <th>Tipe Cuti</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Disetujui Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                    <tr>
                        <td>{{ $leave->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="font-medium">{{ $leave->user->name }}</div>
                            <div class="text-muted text-sm">{{ $leave->user->department->name ?? '-' }}</div>
                        </td>
                        <td>{{ ucfirst($leave->type) }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </td>
                        <td>
                            @if($leave->status == 'approved')
                                <span class="badge badge-approved">Disetujui</span>
                            @elseif($leave->status == 'pending')
                                <span class="badge badge-pending">Menunggu</span>
                            @elseif($leave->status == 'rejected')
                                <span class="badge badge-rejected">Ditolak</span>
                            @endif
                        </td>
                        <td>{{ $leave->approver->name ?? '-' }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="openLeaveModal({{ json_encode([
                                'id' => $leave->id,
                                'user' => $leave->user->name,
                                'type' => $leave->type,
                                'reason' => $leave->reason,
                                'status' => $leave->status,
                                'manager_notes' => $leave->manager_notes,
                                'url' => route('web.leaves.update', $leave->id)
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
            {{ $leaves->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

<!-- Leave Modal -->
<div class="modal-overlay" id="leaveModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Review Pengajuan Cuti</h3>
            <button class="modal-close" onclick="closeLeaveModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form id="leaveForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Karyawan</label>
                <div class="form-control" id="modalUser" style="background:var(--bg-secondary);"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Tipe & Alasan</label>
                <div class="form-control" id="modalReason" style="background:var(--bg-secondary); min-height:80px;"></div>
            </div>
            
            <div class="form-group mt-4">
                <label class="form-label" for="leaveStatus">Aksi Persetujuan</label>
                <select name="status" id="leaveStatus" class="form-control form-select" required>
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
                <button type="button" class="btn btn-ghost" onclick="closeLeaveModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openLeaveModal(data) {
        document.getElementById('modalUser').innerText = data.user || '';
        const leaveType = (data.type || 'Cuti').toUpperCase();
        document.getElementById('modalReason').innerHTML = `<strong>${leaveType}</strong><br>${data.reason || ''}`;
        document.getElementById('leaveStatus').value = data.status;
        document.getElementById('managerNotes').value = data.manager_notes || '';
        document.getElementById('leaveForm').action = data.url;
        
        document.getElementById('leaveModal').classList.add('show');
    }
    function closeLeaveModal() {
        document.getElementById('leaveModal').classList.remove('show');
    }
</script>
@endpush
@endsection
