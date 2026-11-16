@extends('layouts.app')

@section('title', 'Hasil & Rekap Nilai Ujian - CBT School')
@section('page_title', 'Hasil & Rekap Nilai Ujian')
@section('page_subtitle', 'Analisis capaian nilai peserta ujian dan status publikasi hasil')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Hasil & Rekap Nilai</li>
@endsection

@section('content')
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
                        <select name="published" class="form-select">
                            <option value="">Semua Status Publikasi</option>
                            <option value="1" {{ request('published') === '1' ? 'selected' : '' }}>Dipublikasikan ke Siswa</option>
                            <option value="0" {{ request('published') === '0' ? 'selected' : '' }}>Disembunyikan / Belum Diumumkan</option>
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

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Daftar Hasil Pelaksanaan Asesmen</h5>
        <span class="badge text-bg-info">Total: {{ $exams->total() }} Ujian</span>
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
                        <th class="text-center">Rata-Rata Nilai</th>
                        <th class="text-center">Nilai Tertinggi</th>
                        <th class="text-center">Tuntas KKM</th>
                        <th class="text-center">Publikasi Hasil</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $index => $exam)
                        @php
                            $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
                        @endphp
                        <tr>
                            <td class="text-center text-secondary small">{{ $exams->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route($routePrefix . 'results.show', $exam) }}" class="fw-bold text-decoration-none text-dark d-block">
                                    {{ $exam->title }}
                                </a>
                                <span class="badge text-bg-light border font-monospace text-secondary">{{ $exam->code }}</span>
                                <span class="text-secondary small ms-1">&bull; KKM: {{ number_format($exam->passing_grade, 0) }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $exam->subject->name ?? '-' }}</span>
                                <div class="text-secondary small">{{ $exam->academicYear->name ?? '' }} &bull; {{ $exam->semester->name ?? '' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-primary">{{ $exam->completed_attempts_count }}</span>
                                <span class="text-secondary small">/ {{ $exam->participants_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fs-6 fw-bold {{ $exam->avg_score >= $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($exam->avg_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-light border text-dark fw-bold">
                                    {{ number_format($exam->max_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-success-subtle text-success border border-success-subtle">
                                    {{ $exam->passed_count }} Siswa
                                </span>
                            </td>
                            <td class="text-center">
                                @if($exam->is_results_published)
                                    <span class="badge text-bg-success">
                                        &#10003; Dipublikasikan
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary">
                                        &#128274; Disembunyikan
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route($routePrefix . 'results.show', $exam) }}" class="btn btn-sm btn-outline-primary">
                                    &#128200; Lihat Rekap
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                <div class="fs-1 mb-2">&#128202;</div>
                                <h6 class="fw-bold mb-1">Tidak ada data hasil ujian</h6>
                                <p class="small text-secondary mb-0">Belum ada asesmen yang sesuai dengan filter pencarian.</p>
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
@endsection
