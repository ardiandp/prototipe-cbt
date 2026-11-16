@extends('layouts.app')

@section('title', 'Rekap Kelas — ' . $classroom->name . ' - CBT School')
@section('page_title', 'Rekapitulasi Kinerja Kelas')
@section('page_subtitle', 'Kelas ' . $classroom->name . ' (' . ($classroom->department->name ?? '-') . ') &bull; ' . ($classroom->academicYear->name ?? '-'))

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'reports.index') }}">Laporan & Peringkat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kelas {{ $classroom->name }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route($routePrefix . 'reports.index') }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Laporan Utama
        </a>
    </div>
</div>

<!-- Classroom Info Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <span class="fs-1 me-3">&#127963;</span>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $classroom->name }}</h4>
                        <div class="text-secondary small">
                            Tingkat: <strong>{{ $classroom->level }}</strong> &bull;
                            Jurusan: <strong>{{ $classroom->department->name ?? '-' }}</strong> &bull;
                            Tahun Ajaran: <strong>{{ $classroom->academicYear->name ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="badge text-bg-primary fs-6 px-3 py-2">
                    Total Siswa: {{ $classroom->students->count() }} Orang
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Exams Taken by this Classroom -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">&#128221; Rekap Ujian yang Diikuti Kelas Ini</h5>
        <span class="badge text-bg-info">Total: {{ count($examSummaries) }} Ujian</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode & Judul Ujian</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Siswa Selesai</th>
                        <th class="text-center">Rata-Rata Nilai</th>
                        <th class="text-center">Tertinggi / Terendah</th>
                        <th class="text-center">Kelulusan KKM</th>
                        <th class="text-center" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($examSummaries as $index => $summary)
                        @php
                            $exam = $summary->exam;
                        @endphp
                        <tr>
                            <td class="text-center text-secondary small">{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route($routePrefix . 'reports.exam', ['exam' => $exam, 'classroom_id' => $classroom->id]) }}" class="fw-bold text-decoration-none text-dark d-block">
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
                                <span class="fw-bold text-primary">{{ $summary->completed_count }}</span>
                                <span class="text-secondary small">/ {{ $summary->total_students }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fs-6 fw-bold {{ $summary->avg_score >= $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($summary->avg_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-success fw-bold">{{ number_format($summary->max_score, 1) }}</span> /
                                <span class="text-danger fw-bold">{{ number_format($summary->min_score, 1) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-success-subtle text-success border border-success-subtle">
                                    {{ $summary->passed_count }} ({{ $summary->pass_percentage }}%)
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route($routePrefix . 'reports.exam', ['exam' => $exam, 'classroom_id' => $classroom->id]) }}" class="btn btn-sm btn-outline-primary">
                                    &#127942; Ranking Kelas
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <div class="fs-1 mb-2">&#127963;</div>
                                <h6 class="fw-bold mb-1">Belum ada riwayat ujian untuk kelas ini</h6>
                                <p class="small text-secondary mb-0">Siswa di kelas ini belum ditugaskan atau belum menyelesaikan sesi asesmen.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
