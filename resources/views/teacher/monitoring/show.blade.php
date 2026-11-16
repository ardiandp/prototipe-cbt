@extends('layouts.app')

@section('title', 'Live Monitoring — ' . $exam->title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 fw-bold fs-4">&#128065; Live Monitoring Pelaksanaan Ujian</h1>
                <div class="text-secondary small mt-1">
                    <span class="badge text-bg-light border text-primary font-monospace fw-bold">{{ $exam->code }}</span>
                    &bull; {{ $exam->title }} ({{ $exam->subject->name ?? '-' }})
                </div>
            </div>
            <div class="col-sm-6 text-end">
                <a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.monitoring.index') : route('admin.monitoring.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    &larr; Kembali ke Daftar
                </a>
                <span class="badge text-bg-primary fs-7 px-3 py-2">
                    Waktu Server: <span id="clockDisplay">{{ now()->format('H:i:s') }}</span> WIB
                </span>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Summary Info Boxes -->
        <div class="row g-2 mb-4">
            <div class="col-sm-6 col-lg-2">
                <div class="card shadow-sm border border-secondary text-center p-2 h-100">
                    <div class="text-secondary small fw-bold text-uppercase">Total Peserta</div>
                    <div class="fs-3 fw-bold text-dark my-1" id="summaryTotalAssigned">{{ $summary['total_assigned'] }}</div>
                    <div class="small text-secondary">Terdaftar</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="card shadow-sm border border-secondary text-center p-2 h-100">
                    <div class="text-secondary small fw-bold text-uppercase">Belum Mulai</div>
                    <div class="fs-3 fw-bold text-secondary my-1" id="summaryNotStarted">{{ $summary['not_started'] }}</div>
                    <div class="small text-secondary">Peserta</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border border-warning text-center p-2 h-100 bg-warning-subtle">
                    <div class="text-warning-emphasis small fw-bold text-uppercase">&#9201; Sedang Mengerjakan</div>
                    <div class="fs-3 fw-bold text-warning-emphasis my-1" id="summaryInProgress">{{ $summary['in_progress'] }}</div>
                    <div class="small text-secondary">Sesi Aktif</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card shadow-sm border border-success text-center p-2 h-100 bg-success-subtle">
                    <div class="text-success small fw-bold text-uppercase">&#10003; Selesai / Dikumpulkan</div>
                    <div class="fs-3 fw-bold text-success my-1" id="summaryCompleted">{{ $summary['completed'] }}</div>
                    <div class="small text-secondary">Attempt Final</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="card shadow-sm border border-danger text-center p-2 h-100 bg-danger-subtle">
                    <div class="text-danger small fw-bold text-uppercase">&#9888; Waktu Habis</div>
                    <div class="fs-3 fw-bold text-danger my-1" id="summaryExpired">{{ $summary['expired'] }}</div>
                    <div class="small text-secondary">Kedaluwarsa</div>
                </div>
            </div>
        </div>

        <!-- Filter & Realtime Controls Toolbar -->
        <div class="card shadow-sm border mb-3">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">&#128269;</span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama Siswa / NIS / Kelas...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select form-select-sm">
                            <option value="">Semua Status Peserta</option>
                            <option value="in_progress">Sedang Mengerjakan</option>
                            <option value="completed">Selesai / Dikumpulkan</option>
                            <option value="not_started">Belum Mulai</option>
                            <option value="expired">Waktu Habis</option>
                        </select>
                    </div>
                    <div class="col-md-5 d-flex justify-content-md-end align-items-center gap-2">
                        <!-- Auto-refresh switch -->
                        <div class="form-check form-switch mb-0 small">
                            <input class="form-check-input" type="checkbox" role="switch" id="autoRefreshSwitch" checked>
                            <label class="form-check-label text-secondary" for="autoRefreshSwitch">Auto-Sync (5s)</label>
                        </div>

                        <!-- Pulse Status Indicator -->
                        <span class="badge text-bg-light border text-success small d-flex align-items-center gap-1" id="syncStatusBadge">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                            <span id="syncStatusText">Tersinkronisasi</span>
                        </span>

                        <!-- Manual Refresh Button -->
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="manualRefreshBtn" title="Segarkan Data Sekarang">
                            &#8635; Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants Table -->
        <div class="card shadow-sm border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold small text-secondary">&#128101; DAFTAR PESERTA UJIAN</span>
                <span class="badge text-bg-light border text-secondary small" id="renderedCountBadge">
                    Menampilkan: {{ count($participants) }} Peserta
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="participantsTable">
                        <thead class="table-light text-secondary small text-uppercase">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Identitas Siswa</th>
                                <th>Kelas</th>
                                <th>Status Pengerjaan</th>
                                <th style="min-width: 180px;">Progres Jawaban</th>
                                <th>Waktu Mulai & Sisa</th>
                                <th>Aktivitas Terakhir</th>
                                <th style="width: 120px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="participantsTableBody">
                            @forelse($participants as $idx => $p)
                                <tr data-student-id="{{ $p['student_id'] }}" data-status="{{ $p['status'] }}" data-search="{{ strtolower($p['name'] . ' ' . $p['nis'] . ' ' . $p['classroom']) }}">
                                    <td class="text-center font-monospace text-secondary row-number">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $p['name'] }}</div>
                                        <div class="small font-monospace text-secondary">NIS: {{ $p['nis'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border text-secondary">{{ $p['classroom'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $p['status_badge'] }} px-2 py-1 status-badge">
                                            {{ $p['status_label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-bold progress-count">{{ $p['answered_count'] }} / {{ $p['total_questions'] }} Terjawab</span>
                                            <span class="text-secondary progress-percent">{{ $p['progress_percent'] }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $p['status'] === 'completed' ? 'success' : ($p['status'] === 'in_progress' ? 'warning' : 'secondary') }}" style="width: {{ $p['progress_percent'] }}%"></div>
                                        </div>
                                        @if($p['doubtful_count'] > 0)
                                            <div class="small text-warning mt-1 doubtful-text">
                                                &#9873; {{ $p['doubtful_count'] }} Soal Ragu-ragu
                                            </div>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($p['started_at'] !== '-')
                                            <div>Mulai: <strong>{{ $p['started_at'] }}</strong></div>
                                            @if($p['status'] === 'in_progress')
                                                <div class="text-danger fw-bold remaining-time">Sisa: {{ $p['remaining_formatted'] }}</div>
                                            @elseif($p['status'] === 'completed')
                                                <div class="text-success">Selesai: {{ $p['completed_at'] }}</div>
                                            @elseif($p['status'] === 'expired')
                                                <div class="text-secondary">Batas: {{ $p['expires_at'] }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary last-activity">
                                        {{ $p['last_activity'] }}
                                    </td>
                                    <td class="text-center">
                                        @if($p['attempt_id'])
                                            <button type="button" class="btn btn-outline-info btn-sm view-detail-btn" data-attempt-id="{{ $p['attempt_id'] }}" data-student-name="{{ $p['name'] }}">
                                                &#128065; Detail
                                            </button>
                                        @else
                                            <span class="text-muted small">Belum Ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyTableRow">
                                    <td colspan="8" class="text-center py-4 text-secondary">
                                        Tidak ada peserta yang terdaftar pada ujian ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Attempt Siswa -->
<div class="modal fade" id="modalAttemptDetail" tabindex="-1" aria-labelledby="modalAttemptDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalAttemptDetailLabel">&#128221; Detail Attempt Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="modalDetailBody">
                <div class="text-center py-4" id="modalDetailLoading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-secondary small">Memuat rincian pengerjaan...</div>
                </div>

                <div id="modalDetailContent" style="display: none;">
                    <!-- Profil Singkat & Status Attempt -->
                    <div class="row g-2 border rounded p-3 bg-light mb-3">
                        <div class="col-md-6">
                            <div class="small text-secondary">Nama Siswa:</div>
                            <div class="fw-bold fs-6 text-dark" id="modalStudentName">-</div>
                            <div class="small text-secondary mt-1">NIS: <span id="modalStudentNis" class="font-monospace">-</span> | Kelas: <span id="modalStudentClass">-</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-secondary">Status Attempt:</div>
                            <div class="mb-1"><span class="badge text-bg-warning px-2 py-1" id="modalAttemptStatus">-</span></div>
                            <div class="small text-secondary">
                                Mulai: <span id="modalAttemptStarted" class="fw-semibold">-</span> | 
                                Selesai: <span id="modalAttemptCompleted" class="fw-semibold">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Statistik Soal -->
                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span class="fw-bold small text-secondary">&#128196; RINCIAN JAWABAN SOAL:</span>
                        <span class="badge text-bg-primary small" id="modalTotalAnsweredBadge">0 / 0 Terjawab</span>
                    </div>

                    <!-- Tabel Lembar Jawaban -->
                    <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light sticky-top small text-secondary">
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Isi Pertanyaan</th>
                                    <th style="width: 100px;" class="text-center">Pilihan</th>
                                    <th style="width: 110px;" class="text-center">Status</th>
                                    <th style="width: 110px;" class="text-center">Waktu Simpan</th>
                                </tr>
                            </thead>
                            <tbody id="modalQuestionsTableBody">
                                <!-- Generated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const liveDataUrl = "{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.monitoring.live-data', $exam) : route('admin.monitoring.live-data', $exam) }}";
    const attemptDetailBaseUrl = "{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? url('/teacher/monitoring/' . $exam->id . '/attempts') : url('/admin/monitoring/' . $exam->id . '/attempts') }}";

    let participantsData = @json($participants);
    let isAutoRefresh = true;
    let pollInterval = null;

    const autoRefreshSwitch = document.getElementById('autoRefreshSwitch');
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const syncStatusBadge = document.getElementById('syncStatusBadge');
    const syncStatusText = document.getElementById('syncStatusText');

    // Filter and search logic
    function filterRows() {
        const query = (searchInput?.value || '').toLowerCase().trim();
        const selectedStatus = (statusFilter?.value || '').trim();
        const rows = document.querySelectorAll('#participantsTableBody tr[data-student-id]');

        let visibleCount = 0;
        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchesSearch = !query || rowSearch.includes(query);
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const badge = document.getElementById('renderedCountBadge');
        if (badge) badge.textContent = `Menampilkan: ${visibleCount} Peserta`;
    }

    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);

    // Fetch live data from backend
    function fetchLiveData() {
        if (syncStatusBadge) {
            syncStatusBadge.className = 'badge text-bg-light border text-warning small d-flex align-items-center gap-1';
            syncStatusText.textContent = 'Menyinkronkan...';
        }

        fetch(liveDataUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update summary numbers
                document.getElementById('summaryTotalAssigned').textContent = data.summary.total_assigned;
                document.getElementById('summaryNotStarted').textContent = data.summary.not_started;
                document.getElementById('summaryInProgress').textContent = data.summary.in_progress;
                document.getElementById('summaryCompleted').textContent = data.summary.completed;
                document.getElementById('summaryExpired').textContent = data.summary.expired;

                // Update participants table data
                participantsData = data.participants;
                renderTable(participantsData);
                filterRows();

                if (syncStatusBadge) {
                    syncStatusBadge.className = 'badge text-bg-light border text-success small d-flex align-items-center gap-1';
                    syncStatusText.textContent = 'Tersinkronisasi';
                }
            }
        })
        .catch(err => {
            console.warn('Live monitoring sync error:', err);
            if (syncStatusBadge) {
                syncStatusBadge.className = 'badge text-bg-light border text-danger small d-flex align-items-center gap-1';
                syncStatusText.textContent = 'Koneksi Terputus';
            }
        });
    }

    // Render table rows
    function renderTable(list) {
        const tbody = document.getElementById('participantsTableBody');
        if (!tbody) return;

        if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-secondary">Tidak ada peserta yang terdaftar pada ujian ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((p, idx) => {
            const hasStarted = p.started_at !== '-';
            let timingHtml = `<span class="text-muted">-</span>`;

            if (hasStarted) {
                timingHtml = `<div>Mulai: <strong>${p.started_at}</strong></div>`;
                if (p.status === 'in_progress') {
                    timingHtml += `<div class="text-danger fw-bold remaining-time">Sisa: ${p.remaining_formatted}</div>`;
                } else if (p.status === 'completed') {
                    timingHtml += `<div class="text-success">Selesai: ${p.completed_at}</div>`;
                } else if (p.status === 'expired') {
                    timingHtml += `<div class="text-secondary">Batas: ${p.expires_at}</div>`;
                }
            }

            const doubtfulHtml = p.doubtful_count > 0 
                ? `<div class="small text-warning mt-1 doubtful-text">&#9873; ${p.doubtful_count} Soal Ragu-ragu</div>` 
                : '';

            const actionHtml = p.attempt_id 
                ? `<button type="button" class="btn btn-outline-info btn-sm view-detail-btn" data-attempt-id="${p.attempt_id}" data-student-name="${p.name}">&#128065; Detail</button>` 
                : `<span class="text-muted small">Belum Ada</span>`;

            const searchAttr = `${p.name} ${p.nis} ${p.classroom}`.toLowerCase();

            return `
                <tr data-student-id="${p.student_id}" data-status="${p.status}" data-search="${searchAttr}">
                    <td class="text-center font-monospace text-secondary row-number">${idx + 1}</td>
                    <td>
                        <div class="fw-bold text-dark">${p.name}</div>
                        <div class="small font-monospace text-secondary">NIS: ${p.nis}</div>
                    </td>
                    <td><span class="badge text-bg-light border text-secondary">${p.classroom}</span></td>
                    <td><span class="badge text-bg-${p.status_badge} px-2 py-1 status-badge">${p.status_label}</span></td>
                    <td>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold progress-count">${p.answered_count} / ${p.total_questions} Terjawab</span>
                            <span class="text-secondary progress-percent">${p.progress_percent}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-${p.status === 'completed' ? 'success' : (p.status === 'in_progress' ? 'warning' : 'secondary')}" style="width: ${p.progress_percent}%"></div>
                        </div>
                        ${doubtfulHtml}
                    </td>
                    <td class="small">${timingHtml}</td>
                    <td class="small text-secondary last-activity">${p.last_activity}</td>
                    <td class="text-center">${actionHtml}</td>
                </tr>
            `;
        }).join('');

        attachDetailListeners();
    }

    // Attach listeners for Detail Modal
    function attachDetailListeners() {
        document.querySelectorAll('.view-detail-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const attemptId = this.getAttribute('data-attempt-id');
                openAttemptDetail(attemptId);
            });
        });
    }

    // Open and fetch attempt detail modal
    function openAttemptDetail(attemptId) {
        const modalEl = document.getElementById('modalAttemptDetail');
        const loadingEl = document.getElementById('modalDetailLoading');
        const contentEl = document.getElementById('modalDetailContent');

        if (!modalEl) return;

        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';

        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();

        fetch(`${attemptDetailBaseUrl}/${attemptId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';

                document.getElementById('modalStudentName').textContent = data.student.name;
                document.getElementById('modalStudentNis').textContent = data.student.nis;
                document.getElementById('modalStudentClass').textContent = data.student.classroom;

                const statusBadgeEl = document.getElementById('modalAttemptStatus');
                statusBadgeEl.textContent = data.attempt.status === 'completed' ? 'Selesai Dikumpulkan' : (data.attempt.status === 'in_progress' ? 'Sedang Mengerjakan' : 'Waktu Habis');
                statusBadgeEl.className = 'badge text-bg-' + (data.attempt.status === 'completed' ? 'success' : (data.attempt.status === 'in_progress' ? 'warning' : 'danger')) + ' px-2 py-1';

                document.getElementById('modalAttemptStarted').textContent = data.attempt.started_at;
                document.getElementById('modalAttemptCompleted').textContent = data.attempt.completed_at;
                document.getElementById('modalTotalAnsweredBadge').textContent = `${data.attempt.total_answered} / ${data.attempt.total_questions} Terjawab`;

                const tbody = document.getElementById('modalQuestionsTableBody');
                tbody.innerHTML = data.questions.map(q => {
                    const statusText = q.is_doubtful 
                        ? `<span class="badge text-bg-warning">&#9873; Ragu-ragu</span>` 
                        : (q.is_answered ? `<span class="badge text-bg-success">&#10003; Terjawab</span>` : `<span class="badge text-bg-light border text-danger">&#9675; Kosong</span>`);

                    const textSnippet = q.question_text.length > 50 
                        ? q.question_text.substring(0, 50) + '...' 
                        : q.question_text;

                    return `
                        <tr>
                            <td class="text-center font-monospace text-secondary">${q.order}</td>
                            <td class="small text-dark">${textSnippet}</td>
                            <td class="text-center font-monospace fw-bold text-primary">${q.selected_option}</td>
                            <td class="text-center">${statusText}</td>
                            <td class="text-center small text-secondary">${q.answered_at}</td>
                        </tr>
                    `;
                }).join('');
            }
        })
        .catch(err => {
            console.error('Error fetching attempt detail:', err);
            loadingEl.innerHTML = `<div class="text-danger small">Gagal memuat rincian attempt.</div>`;
        });
    }

    attachDetailListeners();

    // Setup periodic polling (every 5 seconds)
    function startPolling() {
        if (!pollInterval) {
            pollInterval = setInterval(fetchLiveData, 5000);
        }
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    if (autoRefreshSwitch) {
        autoRefreshSwitch.addEventListener('change', function () {
            if (this.checked) {
                startPolling();
                if (syncStatusBadge) {
                    syncStatusBadge.className = 'badge text-bg-light border text-success small d-flex align-items-center gap-1';
                    syncStatusText.textContent = 'Tersinkronisasi';
                }
            } else {
                stopPolling();
                if (syncStatusBadge) {
                    syncStatusBadge.className = 'badge text-bg-light border text-secondary small d-flex align-items-center gap-1';
                    syncStatusText.textContent = 'Auto-Sync Jeda';
                }
            }
        });
    }

    if (manualRefreshBtn) {
        manualRefreshBtn.addEventListener('click', fetchLiveData);
    }

    // Start auto polling
    startPolling();
});
</script>
@endsection
