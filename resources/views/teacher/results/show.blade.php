@extends('layouts.app')

@section('title', 'Rekap Hasil — ' . $exam->title . ' - CBT School')
@section('page_title', 'Rekap Hasil & Nilai Siswa')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'results.index') }}">Hasil & Rekap Nilai</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $exam->code }}</li>
@endsection

@section('content')
<!-- Header Action Bar -->
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <a href="{{ route($routePrefix . 'results.index') }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Daftar Hasil
        </a>
        <a href="{{ route($routePrefix . 'monitoring.show', $exam) }}" class="btn btn-outline-primary btn-sm ms-1">
            &#128065; Buka Live Monitoring
        </a>
        <a href="{{ route($routePrefix . 'reports.item-analysis', $exam) }}" class="btn btn-outline-success btn-sm ms-1">
            &#128269; Analisis Butir Soal
        </a>
        <a href="{{ route($routePrefix . 'reports.export-csv', ['exam' => $exam, 'classroom_id' => request('classroom_id')]) }}" class="btn btn-outline-secondary btn-sm ms-1">
            &#128190; CSV
        </a>
        <a href="{{ route($routePrefix . 'reports.print', ['exam' => $exam, 'classroom_id' => request('classroom_id')]) }}" target="_blank" class="btn btn-outline-secondary btn-sm ms-1">
            &#128424; Cetak
        </a>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        @if($exam->is_results_published)
            <span class="badge text-bg-success fs-7 px-3 py-2 me-2">
                &#10003; Hasil Telah Dipublikasikan ke Siswa
            </span>
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalUnpublishResults">
                &#128274; Tarik / Sembunyikan Hasil
            </button>
        @else
            <span class="badge text-bg-secondary fs-7 px-3 py-2 me-2">
                &#128274; Hasil Masih Disembunyikan dari Siswa
            </span>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalPublishResults">
                &#128227; Publikasikan Hasil Sekarang
            </button>
        @endif
    </div>
</div>

<!-- Exam Summary Cards -->
<div class="row g-2 mb-4">
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border text-center p-2 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Peserta Terdaftar</div>
            <div class="fs-3 fw-bold text-dark my-1">{{ $summary['total_participants'] }}</div>
            <div class="small text-secondary">Siswa</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border border-primary text-center p-2 h-100 bg-primary-subtle">
            <div class="text-primary small fw-bold text-uppercase">Telah Selesai</div>
            <div class="fs-3 fw-bold text-primary my-1">{{ $summary['completed_participants'] }}</div>
            <div class="small text-secondary">Attempt Final</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border text-center p-2 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Rata-Rata Nilai</div>
            <div class="fs-3 fw-bold {{ $summary['avg_score'] >= $exam->passing_grade ? 'text-success' : 'text-danger' }} my-1">
                {{ number_format($summary['avg_score'], 1) }}
            </div>
            <div class="small text-secondary">KKM: {{ number_format($exam->passing_grade, 0) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border text-center p-2 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Tertinggi / Terendah</div>
            <div class="fs-4 fw-bold text-dark my-1">
                <span class="text-success">{{ number_format($summary['max_score'], 1) }}</span> /
                <span class="text-danger">{{ number_format($summary['min_score'], 1) }}</span>
            </div>
            <div class="small text-secondary">Skala 0 - 100</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border border-success text-center p-2 h-100 bg-success-subtle">
            <div class="text-success small fw-bold text-uppercase">&#10003; Tuntas KKM</div>
            <div class="fs-3 fw-bold text-success my-1">{{ $summary['passed_count'] }}</div>
            <div class="small text-secondary">{{ $summary['pass_percentage'] }}% Kelulusan</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card shadow-sm border border-danger text-center p-2 h-100 bg-danger-subtle">
            <div class="text-danger small fw-bold text-uppercase">&#9888; Remedial / Gagal</div>
            <div class="fs-3 fw-bold text-danger my-1">{{ $summary['failed_count'] }}</div>
            <div class="small text-secondary">Di Bawah KKM</div>
        </div>
    </div>
</div>

<!-- Participant Filter Toolbar -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form action="{{ request()->url() }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari NIS atau nama siswa..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="classroom_id" class="form-select">
                    <option value="">Semua Rombel / Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status Kelulusan</option>
                    <option value="passed" {{ request('status') === 'passed' ? 'selected' : '' }}>Tuntas KKM</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Remedial / Di Bawah KKM</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Mengerjakan</option>
                    <option value="not_taken" {{ request('status') === 'not_taken' ? 'selected' : '' }}>Belum Mengerjakan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                <a href="{{ request()->url() }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Participants Result Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Rincian Hasil Siswa ({{ count($results) }} Peserta)</h5>
        <div>
            <span class="badge text-bg-light border text-secondary">KKM: {{ number_format($exam->passing_grade, 0) }}</span>
            <span class="badge text-bg-light border text-secondary ms-1">Soal: {{ $exam->total_questions }} Butir</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 45px;">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">Attempt</th>
                        <th class="text-center">Benar</th>
                        <th class="text-center">Salah</th>
                        <th class="text-center">Kosong</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center">Status KKM</th>
                        <th class="text-center" style="width: 140px;">Lembar Jawaban</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $index => $res)
                        @php
                            $att = $res->latest_attempt;
                        @endphp
                        <tr>
                            <td class="text-center text-secondary small">{{ $index + 1 }}</td>
                            <td class="font-monospace text-secondary small">{{ $res->student->nis ?? '-' }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $res->student->name }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-light border">{{ $res->student->classroom->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($att)
                                    <span class="badge text-bg-light border font-monospace">Ke-{{ $att->attempt_number }}</span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($att && in_array($att->status, ['completed', 'expired']))
                                    <span class="text-success fw-bold">{{ $att->total_correct }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($att && in_array($att->status, ['completed', 'expired']))
                                    <span class="text-danger fw-bold">{{ $att->total_incorrect }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($att && in_array($att->status, ['completed', 'expired']))
                                    <span class="text-secondary">{{ $att->total_unanswered }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($att && in_array($att->status, ['completed', 'expired']))
                                    <span class="fs-6 fw-bold {{ (float) $att->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($att->score, 1) }}
                                    </span>
                                @elseif($att && $att->isInProgress())
                                    <span class="badge text-bg-warning">Sedang Mengerjakan</span>
                                @else
                                    <span class="text-secondary small">Belum Ada Sesi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($res->status_kkm === 'passed')
                                    <span class="badge text-bg-success">&#10003; Tuntas KKM</span>
                                @elseif($res->status_kkm === 'failed')
                                    <span class="badge text-bg-danger">&#9888; Remedial</span>
                                @elseif($res->status_kkm === 'in_progress')
                                    <span class="badge text-bg-warning">&#9201; Berjalan</span>
                                @else
                                    <span class="badge text-bg-secondary">Belum Mengikuti</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($att && in_array($att->status, ['completed', 'expired']))
                                    <a href="{{ route($routePrefix . 'results.attempts.detail', ['exam' => $exam, 'attempt' => $att]) }}" class="btn btn-sm btn-outline-info">
                                        &#128221; Lihat Jawaban
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        Tidak Ada
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-secondary">
                                <div class="fs-1 mb-2">&#128101;</div>
                                <h6 class="fw-bold mb-1">Tidak ada data peserta</h6>
                                <p class="small text-secondary mb-0">Belum ada peserta yang sesuai kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Publish Results -->
<div class="modal fade" id="modalPublishResults" tabindex="-1" aria-labelledby="modalPublishResultsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route($routePrefix . 'exams.publish-results', $exam) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalPublishResultsLabel">&#128227; Publikasikan Hasil Ujian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Apakah Anda yakin ingin mempublikasikan nilai ujian <strong>{{ $exam->title }}</strong> kepada peserta ujian?
                    </p>
                    <div class="alert alert-info py-2 small mb-3">
                        &#9432; Setelah dipublikasikan, seluruh siswa peserta akan dapat melihat skor total, status kelulusan KKM, dan rincian pembahasan jawaban di akun mereka.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan Publikasi (Opsional):</label>
                        <input type="text" name="reason" class="form-control" placeholder="Contoh: Evaluasi resmi penilaian tengah semester" value="Publikasi hasil ujian resmi kepada siswa.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Ya, Publikasikan Hasil</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Unpublish Results -->
<div class="modal fade" id="modalUnpublishResults" tabindex="-1" aria-labelledby="modalUnpublishResultsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route($routePrefix . 'exams.unpublish-results', $exam) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalUnpublishResultsLabel">&#128274; Sembunyikan Hasil Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Apakah Anda yakin ingin menyembunyikan hasil ujian <strong>{{ $exam->title }}</strong> dari siswa?
                    </p>
                    <div class="alert alert-warning py-2 small mb-3">
                        &#9888; Siswa tidak akan dapat melihat nilai dan lembar jawaban lagi sampai Anda mempublikasikannya kembali.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alasan Penarikan (Opsional):</label>
                        <input type="text" name="reason" class="form-control" placeholder="Contoh: Koreksi bobot soal atau verifikasi susulan" value="Penarikan sementara publikasi hasil untuk rekapitulasi.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold">Ya, Sembunyikan Nilai</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
