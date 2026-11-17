@extends('layouts.app')

@section('title', 'Audit Log & Riwayat Aktivitas - CBT School')
@section('page_title', 'Audit Log & Riwayat Aktivitas')
@section('page_subtitle', 'Pencatatan dan pelacakan seluruh tindakan administratif, autentikasi, serta perubahan status ujian di sistem')

@php
    $isAdmin = auth()->user()->isAdmin() || auth()->user()->isSuperAdmin();
    $dashboardRoute = $isAdmin ? route('admin.dashboard') : route('teacher.dashboard');
    $currentRouteName = $isAdmin ? 'admin.audit-logs.index' : 'teacher.audit-logs.index';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ $dashboardRoute }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Audit Log</li>
@endsection

@section('content')
<!-- Metric Stat Cards -->
<div class="row g-2 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-secondary text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Total Log Terdata</div>
            <div class="fs-2 fw-bold text-dark my-1">{{ number_format($totalScopeCount) }}</div>
            <div class="small text-secondary">Dalam cakupan akses Anda</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-primary text-center p-3 h-100 bg-primary-subtle">
            <div class="text-primary small fw-bold text-uppercase">Aktivitas Hari Ini</div>
            <div class="fs-2 fw-bold text-primary my-1">{{ number_format($todayCount) }}</div>
            <div class="small text-secondary">{{ now()->translatedFormat('d F Y') }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-info text-center p-3 h-100 bg-info-subtle">
            <div class="text-info-emphasis small fw-bold text-uppercase">Hasil Filter</div>
            <div class="fs-2 fw-bold text-info-emphasis my-1">{{ number_format($logs->total()) }}</div>
            <div class="small text-secondary">Log sesuai kriteria pencarian</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-dark text-center p-3 h-100 bg-light">
            <div class="text-dark small fw-bold text-uppercase">Cakupan Pengawasan</div>
            <div class="fs-6 fw-bold text-dark my-1">
                @if($isAdmin)
                    <span class="badge bg-success">Seluruh Sistem (Admin)</span>
                @else
                    <span class="badge bg-info text-dark">Aktivitas Saya & Ujian Saya</span>
                @endif
            </div>
            <div class="small text-secondary">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</div>
        </div>
    </div>
</div>

<!-- Filter Toolbar Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark">&#128269; Filter & Pencarian Audit Log</h6>
            @if(request()->anyFilled(['action', 'role', 'user_id', 'start_date', 'end_date', 'search']))
                <a href="{{ route($currentRouteName) }}" class="btn btn-sm btn-outline-secondary">
                    &#10006; Reset Filter
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route($currentRouteName) }}">
            <div class="row g-2">
                <!-- Action Type Filter -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Jenis Tindakan / Aksi</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">-- Semua Tindakan --</option>
                        @foreach($actionList as $key => $label)
                            <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>
                                {{ $label }} ({{ $key }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Filter (Admin only) or Role -->
                @if($isAdmin)
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">Peran (Role)</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">-- Semua Role --</option>
                            <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Guru</option>
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Siswa</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">Pengguna</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">-- Semua Pengguna --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->username }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Date Range Filters -->
                <div class="{{ $isAdmin ? 'col-md-2' : 'col-md-3' }}">
                    <label class="form-label small fw-semibold text-secondary">Mulai Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>

                <div class="{{ $isAdmin ? 'col-md-2' : 'col-md-3' }}">
                    <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <!-- Search Keyword -->
                <div class="{{ $isAdmin ? 'col-md-9' : 'col-md-9' }}">
                    <label class="form-label small fw-semibold text-secondary">Kata Kunci Pencarian</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Cari alasan, IP address, nama pengguna, atau username..."
                           value="{{ request('search') }}">
                </div>

                <!-- Submit Button -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        &#128269; Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark">&#128220; Rekam Jejak Audit & Keamanan</h6>
        <span class="badge bg-secondary">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Waktu (WIB)</th>
                    <th>Pelaku / Pengguna</th>
                    <th>Aksi</th>
                    <th>Objek Target</th>
                    <th>Keterangan / Alasan</th>
                    <th>IP Address & Info</th>
                    <th class="text-center" style="width: 90px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                    @php
                        // Badge color mapping for action
                        $badgeClass = match($log->action) {
                            'login' => 'bg-info text-dark',
                            'logout' => 'bg-secondary text-white',
                            'publish_exam' => 'bg-success text-white',
                            'unpublish_exam' => 'bg-warning text-dark',
                            'cancel_exam' => 'bg-danger text-white',
                            'force_finish', 'admin_finalize' => 'bg-danger text-white',
                            'retake_authorization', 'retake_authorized' => 'bg-primary text-white',
                            'publish_results' => 'bg-success text-white',
                            'unpublish_results' => 'bg-warning text-dark',
                            default => 'bg-dark text-white',
                        };

                        $actionLabel = $actionList[$log->action] ?? strtoupper(str_replace('_', ' ', $log->action));

                        // Morph type short representation
                        $targetDisplay = '-';
                        if ($log->auditable_type) {
                            $modelName = class_basename($log->auditable_type);
                            $targetDisplay = "{$modelName} #{$log->auditable_id}";
                        }
                    @endphp
                    <tr>
                        <td class="text-center text-muted small">{{ $logs->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark small">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                            <div class="text-muted smaller" style="font-size: 0.75rem;">{{ $log->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-secondary-subtle text-dark fw-bold d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark small">{{ $log->user->name }}</div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border">{{ $log->user->role->label() }}</span>
                                            <span class="font-monospace text-secondary" style="font-size: 0.75rem;">({{ $log->user->username }})</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted fst-italic small">Sistem / Pengguna Terhapus</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $actionLabel }}
                            </span>
                            <div class="font-monospace text-muted mt-1" style="font-size: 0.7rem;">{{ $log->action }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">
                                {{ $targetDisplay }}
                            </span>
                        </td>
                        <td style="max-width: 280px; white-space: normal;">
                            <span class="small text-dark">{{ $log->reason }}</span>
                        </td>
                        <td>
                            <div class="font-monospace small text-secondary">{{ $log->ip_address ?? '127.0.0.1' }}</div>
                            <div class="text-muted text-truncate" style="max-width: 140px; font-size: 0.7rem;" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-view-detail px-2 py-1"
                                    data-id="{{ $log->id }}"
                                    data-action="{{ $actionLabel }}"
                                    data-action-key="{{ $log->action }}"
                                    data-time="{{ $log->created_at->format('d/m/Y H:i:s') }} ({{ $log->created_at->diffForHumans() }})"
                                    data-user="{{ $log->user ? $log->user->name . ' (' . $log->user->role->label() . ' - ' . $log->user->username . ')' : 'Sistem' }}"
                                    data-target="{{ $targetDisplay }}"
                                    data-reason="{{ $log->reason }}"
                                    data-ip="{{ $log->ip_address ?? '-' }}"
                                    data-agent="{{ $log->user_agent ?? '-' }}"
                                    data-details="{{ json_encode($log->details ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}">
                                &#128065; Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="fs-4 mb-2">&#128220;</div>
                            <div class="fw-semibold">Tidak Ada Rekam Jejak Audit</div>
                            <div class="small">Tidak ada data audit log yang cocok dengan kriteria filter saat ini.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="small text-secondary mb-2 mb-md-0">
                    Menampilkan {{ $logs->firstItem() }} sampai {{ $logs->lastItem() }} dari {{ $logs->total() }} entri log
                </div>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Detail Audit Log -->
<div class="modal fade" id="modalAuditDetail" tabindex="-1" aria-labelledby="modalAuditDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalAuditDetailLabel">&#128220; Rincian Audit Log #<span id="detailLogId">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">Waktu Kejadian</div>
                        <div class="fw-semibold text-dark" id="detailTime">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">Tindakan / Aksi</div>
                        <div>
                            <span class="badge bg-primary" id="detailActionBadge">-</span>
                            <span class="font-monospace text-secondary small ms-1" id="detailActionKey"></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">Pengguna / Pelaku</div>
                        <div class="fw-semibold text-dark" id="detailUser">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">Objek Target</div>
                        <div class="font-monospace text-dark" id="detailTarget">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">Alamat IP</div>
                        <div class="font-monospace text-dark" id="detailIp">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase">User Agent / Perangkat</div>
                        <div class="small text-muted font-monospace text-break" id="detailAgent">-</div>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary small fw-bold text-uppercase">Alasan / Catatan Tindakan</div>
                        <div class="p-2 bg-light border rounded text-dark" id="detailReason">-</div>
                    </div>
                </div>

                <div>
                    <div class="text-secondary small fw-bold text-uppercase mb-1">Payload / Parameter Detail (JSON)</div>
                    <pre class="bg-dark text-light p-3 rounded mb-0" style="max-height: 250px; overflow-y: auto;"><code id="detailPayload">{}</code></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailButtons = document.querySelectorAll('.btn-view-detail');
    const modalEl = document.getElementById('modalAuditDetail');
    if (!modalEl) return;

    const modalInstance = new bootstrap.Modal(modalEl);

    detailButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('detailLogId').textContent = this.dataset.id;
            document.getElementById('detailTime').textContent = this.dataset.time;
            document.getElementById('detailActionBadge').textContent = this.dataset.action;
            document.getElementById('detailActionKey').textContent = `(${this.dataset.actionKey})`;
            document.getElementById('detailUser').textContent = this.dataset.user;
            document.getElementById('detailTarget').textContent = this.dataset.target;
            document.getElementById('detailIp').textContent = this.dataset.ip;
            document.getElementById('detailAgent').textContent = this.dataset.agent;
            document.getElementById('detailReason').textContent = this.dataset.reason;

            let detailsJson = '{}';
            try {
                const parsed = JSON.parse(this.dataset.details || '{}');
                detailsJson = JSON.stringify(parsed, null, 2);
            } catch (e) {
                detailsJson = this.dataset.details || '{}';
            }
            document.getElementById('detailPayload').textContent = detailsJson;

            modalInstance.show();
        });
    });
});
</script>
@endpush
