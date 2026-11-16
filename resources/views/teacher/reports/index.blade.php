@extends('layouts.app')

@section('title', 'Laporan & Peringkat Ujian - CBT School')
@section('page_title', 'Laporan & Peringkat Nilai')
@section('page_subtitle', 'Rekapitulasi nilai komprehensif, analisis statistik, peringkat siswa, dan rekap per kelas')

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Laporan & Peringkat</li>
@endsection

@section('content')
<!-- Overall Performance Stat Cards -->
<div class="row g-2 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-secondary text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Total Asesmen Ujian</div>
            <div class="fs-2 fw-bold text-dark my-1">{{ $overallStats['total_exams'] }}</div>
            <div class="small text-secondary">Jadwal Asesmen</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-primary text-center p-3 h-100 bg-primary-subtle">
            <div class="text-primary small fw-bold text-uppercase">Total Sesi Selesai</div>
            <div class="fs-2 fw-bold text-primary my-1">{{ $overallStats['total_attempts'] }}</div>
            <div class="small text-secondary">Pengerjaan Siswa</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-info text-center p-3 h-100 bg-info-subtle">
            <div class="text-info-emphasis small fw-bold text-uppercase">Rata-Rata Nilai Keseluruhan</div>
            <div class="fs-2 fw-bold text-info-emphasis my-1">{{ number_format($overallStats['overall_avg'], 1) }}</div>
            <div class="small text-secondary">Skala 0 - 100</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-success text-center p-3 h-100 bg-success-subtle">
            <div class="text-success small fw-bold text-uppercase">&#10003; Persentase Kelulusan KKM</div>
            <div class="fs-2 fw-bold text-success my-1">{{ $overallStats['overall_pass_rate'] }}%</div>
            <div class="small text-secondary">Tuntas Asesmen</div>
        </div>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <form action="{{ request()->url() }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128269;</span>
                            <input type="text" name="search" class="form-control" placeholder="Cari judul atau kode ujian..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="subject_id" class="form-select">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="academic_year_id" class="form-select">
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Exams Report Listing -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">&#128202; Rekapitulasi Laporan per Ujian</h5>
        <span class="badge text-bg-info">Total: {{ $exams->total() }} Asesmen</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode & Judul Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Peserta Selesai</th>
                        <th class="text-center">Rata-Rata</th>
                        <th class="text-center">Tertinggi / Terendah</th>
                        <th class="text-center">Tuntas KKM</th>
                        <th class="text-center" style="width: 170px;">Aksi Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $index => $exam)
                        <tr>
                            <td class="text-center text-secondary small">{{ $exams->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route($routePrefix . 'reports.exam', $exam) }}" class="fw-bold text-decoration-none text-dark d-block">
                                    {{ $exam->title }}
                                </a>
                                <span class="badge text-bg-light border font-monospace text-secondary">{{ $exam->code }}</span>
                                <span class="text-secondary small ms-1">&bull; KKM: {{ number_format($exam->passing_grade, 0) }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $exam->subject->name ?? '-' }}</span>
                                <div class="text-secondary small">{{ $exam->teacher->name ?? ($exam->teacher->user->name ?? '-') }}</div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-primary">{{ $exam->completed_count }}</span>
                                <span class="text-secondary small">/ {{ $exam->participants_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fs-6 fw-bold {{ $exam->avg_score >= $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($exam->avg_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ number_format($exam->max_score, 1) }}</span> /
                                <span class="text-danger fw-bold">{{ number_format($exam->min_score, 1) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-success-subtle text-success border border-success-subtle">
                                    {{ $exam->passed_count }} ({{ $exam->pass_percentage }}%)
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route($routePrefix . 'reports.exam', $exam) }}" class="btn btn-sm btn-outline-primary">
                                    &#127942; Laporan & Ranking
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <div class="fs-1 mb-2">&#128200;</div>
                                <h6 class="fw-bold mb-1">Belum ada data laporan</h6>
                                <p class="small text-secondary mb-0">Belum ada ujian yang sesuai kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($exams->hasPages())
        <div class="card-footer bg-white border-top py-2">
            <div class="d-flex justify-content-end">
                {{ $exams->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Class Breakdown Hub -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">&#127963; Rekapitulasi Berbasis Rombongan Belajar (Kelas)</h5>
        <span class="badge text-bg-secondary">{{ count($classrooms) }} Kelas Terdaftar</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($classrooms as $cls)
                <div class="col-md-4 col-sm-6">
                    <div class="card h-100 border shadow-none bg-light-subtle">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 text-dark">{{ $cls->name }}</h6>
                                <span class="badge text-bg-primary font-monospace">{{ $cls->level }}</span>
                            </div>
                            <div class="text-secondary small mb-3">
                                <span>Jumlah Siswa: <strong>{{ $cls->students_count }}</strong> Siswa</span>
                            </div>
                            <a href="{{ route($routePrefix . 'reports.class', $cls) }}" class="btn btn-sm btn-outline-secondary w-100">
                                &#128203; Lihat Rekap Kelas
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-secondary py-4">
                    Tidak ada rombongan belajar yang terdaftar.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
