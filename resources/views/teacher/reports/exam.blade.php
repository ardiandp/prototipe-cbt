@extends('layouts.app')

@section('title', 'Laporan & Peringkat — ' . $exam->title . ' - CBT School')
@section('page_title', 'Laporan Analisis & Peringkat Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'reports.index') }}">Laporan & Peringkat</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $exam->code }}</li>
@endsection

@section('content')
<!-- Header Action Bar -->
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <a href="{{ route($routePrefix . 'reports.index') }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Laporan Utama
        </a>
        <a href="{{ route($routePrefix . 'results.show', $exam) }}" class="btn btn-outline-info btn-sm ms-1">
            &#128221; Lembar Hasil Siswa
        </a>
        <a href="{{ route($routePrefix . 'monitoring.show', $exam) }}" class="btn btn-outline-primary btn-sm ms-1">
            &#128065; Live Monitoring
        </a>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <form action="{{ request()->url() }}" method="GET" class="d-inline-flex align-items-center gap-2">
            <label class="small text-secondary mb-0">Filter Kelas:</label>
            <select name="classroom_id" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">Semua Kelas</option>
                @foreach($classrooms as $cls)
                    <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                        {{ $cls->name }}
                    </option>
                @endforeach
            </select>
            @if(request('classroom_id'))
                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Statistical Summary Cards -->
<div class="row g-2 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Peserta Terdaftar</div>
            <div class="fs-2 fw-bold text-dark my-1">{{ $summary['total_enrolled'] }}</div>
            <div class="small text-secondary">
                Selesai: <strong>{{ $summary['total_completed'] }}</strong> ({{ $summary['completion_rate'] }}%) &bull; Absen: <strong>{{ $summary['total_absent'] }}</strong>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-primary text-center p-3 h-100 bg-primary-subtle">
            <div class="text-primary small fw-bold text-uppercase">Rata-Rata Nilai</div>
            <div class="fs-2 fw-bold text-primary my-1">{{ number_format($summary['avg_score'], 1) }}</div>
            <div class="small text-secondary">
                KKM: <strong>{{ number_format($exam->passing_grade, 0) }}</strong> &bull; Median: <strong>{{ number_format($summary['median_score'], 1) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Tertinggi / Terendah</div>
            <div class="fs-2 fw-bold text-dark my-1">
                <span class="text-success">{{ number_format($summary['max_score'], 1) }}</span> /
                <span class="text-danger">{{ number_format($summary['min_score'], 1) }}</span>
            </div>
            <div class="small text-secondary">
                Standar Deviasi: <strong>{{ number_format($summary['std_dev'], 2) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-success text-center p-3 h-100 bg-success-subtle">
            <div class="text-success small fw-bold text-uppercase">&#10003; Tingkat Kelulusan</div>
            <div class="fs-2 fw-bold text-success my-1">{{ $summary['pass_percentage'] }}%</div>
            <div class="small text-secondary">
                Tuntas: <strong>{{ $summary['passed_count'] }}</strong> &bull; Remedial: <strong>{{ $summary['failed_count'] }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Score Distribution Histogram -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">&#128202; Distribusi Frekuensi Nilai</h5>
                <div class="text-secondary small">Pengelompokan rentang skor peserta ujian</div>
            </div>
            <div class="card-body">
                @if($summary['total_completed'] > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($distribution as $dist)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold font-monospace text-dark">
                                        {{ $dist['label'] }} <span class="text-secondary fw-normal">({{ $dist['description'] }})</span>
                                    </span>
                                    <span class="small fw-bold text-secondary">
                                        {{ $dist['count'] }} Siswa ({{ $dist['percentage'] }}%)
                                    </span>
                                </div>
                                <div class="progress" style="height: 14px;">
                                    <div class="progress-bar bg-{{ $dist['badge'] }}" role="progressbar" style="width: {{ $dist['percentage'] }}%;" aria-valuenow="{{ $dist['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-secondary">
                        Belum ada peserta yang menyelesaikan ujian untuk membentuk distribusi nilai.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Class-by-Class Breakdown -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">&#127963; Rekapitulasi per Rombongan Belajar (Kelas)</h5>
                <div class="text-secondary small">Perbandingan rata-rata capaian dan kelulusan antarkelas</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th class="text-center">Selesai/Total</th>
                                <th class="text-center">Rata-Rata</th>
                                <th class="text-center">Tertinggi</th>
                                <th class="text-center">Kelulusan KKM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classBreakdown as $cls)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $cls->classroom_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-semibold text-primary">{{ $cls->total_completed }}</span> / {{ $cls->total_enrolled }}
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $cls->avg_score >= $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($cls->avg_score, 1) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-light border text-dark">{{ number_format($cls->max_score, 1) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-success-subtle text-success border border-success-subtle">
                                            {{ $cls->passed_count }} ({{ $cls->pass_percentage }}%)
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">
                                        Tidak ada data kelas yang terasosiasi.
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

<!-- Student Rankings Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title fw-bold mb-0">&#127942; Papan Peringkat / Ranking Peserta Ujian</h5>
            <div class="text-secondary small">Urutan berdasarkan nilai akhir tertinggi dan durasi pengerjaan tercepat</div>
        </div>
        <span class="badge text-bg-info">Total: {{ count($rankings) }} Siswa Dinilai</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 70px;">Peringkat</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">Benar</th>
                        <th class="text-center">Salah</th>
                        <th class="text-center">Kosong</th>
                        <th class="text-center">Durasi Pengerjaan</th>
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center" style="width: 130px;">Status KKM</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankings as $att)
                        @php
                            $minutes = floor($att->duration_seconds / 60);
                            $seconds = $att->duration_seconds % 60;
                            $durationText = sprintf('%dm %02ds', $minutes, $seconds);
                        @endphp
                        <tr class="{{ $att->rank <= 3 ? 'table-light fw-semibold' : '' }}">
                            <td class="text-center">
                                @if($att->rank === 1)
                                    <span class="badge text-bg-warning text-dark fs-6 px-2 py-1 shadow-sm">&#129351; 1</span>
                                @elseif($att->rank === 2)
                                    <span class="badge text-bg-secondary text-white fs-6 px-2 py-1 shadow-sm">&#129352; 2</span>
                                @elseif($att->rank === 3)
                                    <span class="badge text-bg-danger text-white fs-6 px-2 py-1 shadow-sm">&#129353; 3</span>
                                @else
                                    <span class="badge text-bg-light border font-monospace text-secondary">#{{ $att->rank }}</span>
                                @endif
                            </td>
                            <td class="font-monospace text-secondary small">{{ $att->student->nis ?? '-' }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $att->student->name }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-light border">{{ $att->student->classroom->name ?? '-' }}</span>
                            </td>
                            <td class="text-center text-success fw-bold">{{ $att->total_correct }}</td>
                            <td class="text-center text-danger fw-bold">{{ $att->total_incorrect }}</td>
                            <td class="text-center text-secondary">{{ $att->total_unanswered }}</td>
                            <td class="text-center font-monospace text-secondary small">{{ $durationText }}</td>
                            <td class="text-center">
                                <span class="fs-5 fw-bold {{ (float) $att->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($att->score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if((float) $att->score >= (float) $exam->passing_grade)
                                    <span class="badge text-bg-success">&#10003; Tuntas KKM</span>
                                @else
                                    <span class="badge text-bg-danger">&#9888; Remedial</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-secondary">
                                <div class="fs-1 mb-2">&#127942;</div>
                                <h6 class="fw-bold mb-1">Belum ada data pengerjaan</h6>
                                <p class="small text-secondary mb-0">Peserta belum menyelesaikan sesi ujian untuk dilakukan perankingan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
