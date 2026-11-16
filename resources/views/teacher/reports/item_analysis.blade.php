@extends('layouts.app')

@section('title', 'Analisis Butir Soal — ' . $exam->title . ' - CBT School')
@section('page_title', 'Analisis Butir Soal (Item Analysis)')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ') &bull; ' . ($exam->subject->name ?? '-'))

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'reports.index') }}">Laporan & Peringkat</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'reports.exam', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Analisis Butir Soal</li>
@endsection

@section('content')
<!-- Header Action Bar -->
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <a href="{{ route($routePrefix . 'reports.exam', $exam) }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Laporan Ujian
        </a>
        <a href="{{ route($routePrefix . 'results.show', $exam) }}" class="btn btn-outline-info btn-sm ms-1">
            &#128221; Rekap Hasil Siswa
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

<!-- Item Analysis Summary Info Boxes -->
<div class="row g-2 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Total Butir Soal</div>
            <div class="fs-2 fw-bold text-dark my-1">{{ $summary['total_questions'] }}</div>
            <div class="small text-secondary">
                Peserta Dianalisis: <strong>{{ $summary['total_participants'] }}</strong> Siswa
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-primary text-center p-3 h-100 bg-primary-subtle">
            <div class="text-primary small fw-bold text-uppercase">Daya Serap Rata-Rata</div>
            <div class="fs-2 fw-bold text-primary my-1">{{ $summary['overall_accuracy'] }}%</div>
            <div class="small text-secondary">Tingkat Ketepatan Jawaban</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border text-center p-3 h-100">
            <div class="text-secondary small fw-bold text-uppercase">Komposisi Tingkat Kesukaran</div>
            <div class="fs-4 fw-bold text-dark my-1">
                <span class="text-success">{{ $summary['easy_count'] }} Mudah</span> &bull;
                <span class="text-primary">{{ $summary['medium_count'] }} Sedang</span> &bull;
                <span class="text-danger">{{ $summary['hard_count'] }} Sukar</span>
            </div>
            <div class="small text-secondary">Berdasarkan Daya Serap Jawaban</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border border-info text-center p-3 h-100 bg-info-subtle">
            <div class="text-info-emphasis small fw-bold text-uppercase">Ekstrem Soal</div>
            <div class="small fw-bold text-dark my-1">
                Paling Mudah: <span class="badge text-bg-success">#{{ $summary['easiest_question']?->question->order ?? '-' }} ({{ $summary['easiest_question']?->correct_percentage ?? 0 }}%)</span><br>
                Paling Sukar: <span class="badge text-bg-danger">#{{ $summary['hardest_question']?->question->order ?? '-' }} ({{ $summary['hardest_question']?->correct_percentage ?? 0 }}%)</span>
            </div>
            <div class="small text-secondary">Performa Butir Soal</div>
        </div>
    </div>
</div>

<!-- Quick Overview Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">&#128203; Tabel Ringkasan Analisis Butir Soal</h5>
        <span class="badge text-bg-info">Total: {{ count($analysis) }} Soal</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kunci</th>
                        <th>Teks Soal</th>
                        <th class="text-center">Bobot</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Benar</th>
                        <th class="text-center">Salah</th>
                        <th class="text-center">Kosong</th>
                        <th class="text-center">Persentase Benar</th>
                        <th class="text-center">Tingkat Kesukaran</th>
                        <th class="text-center" style="width: 90px;">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analysis as $item)
                        @php
                            $q = $item->question;
                            $keyLabel = $q->getCorrectAnswerLabel() ?? '-';
                        @endphp
                        <tr>
                            <td class="text-center font-monospace fw-bold text-secondary">#{{ $q->order }}</td>
                            <td class="text-center">
                                <span class="badge text-bg-success font-monospace px-2 py-1">{{ $keyLabel }}</span>
                            </td>
                            <td>
                                <span class="text-dark small d-inline-block text-truncate" style="max-width: 320px;">
                                    {{ Str::limit(strip_tags($q->question_text), 75) }}
                                </span>
                            </td>
                            <td class="text-center font-monospace">{{ number_format($q->weight, 1) }}</td>
                            <td class="text-center text-secondary small">{{ $item->total_participants }}</td>
                            <td class="text-center text-success fw-bold">{{ $item->correct_count }}</td>
                            <td class="text-center text-danger fw-bold">{{ $item->incorrect_count }}</td>
                            <td class="text-center text-secondary">{{ $item->unanswered_count }}</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $item->correct_percentage >= 70 ? 'text-success' : ($item->correct_percentage < 30 ? 'text-danger' : 'text-primary') }}">
                                    {{ $item->correct_percentage }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-{{ $item->difficulty_badge }}">
                                    {{ $item->difficulty_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="#soal-{{ $q->order }}" class="btn btn-sm btn-outline-secondary">
                                    Lihat &darr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-secondary">
                                Tidak ada butir soal pada ujian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Question Breakdown with Option Distribution -->
<div class="mb-4">
    <h5 class="fw-bold mb-3">&#128269; Rincian Analisis Daya Pembeda & Distribusi Distraktor Pilihan A–E</h5>

    @foreach($analysis as $item)
        @php
            $q = $item->question;
        @endphp
        <div class="card shadow-sm border-0 mb-4" id="soal-{{ $q->order }}">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-dark font-monospace fs-6 px-3 py-1">Soal #{{ $q->order }}</span>
                    <span class="badge text-bg-{{ $item->difficulty_badge }} fs-7">
                        Tingkat Kesukaran: {{ $item->difficulty_label }}
                    </span>
                    <span class="text-secondary small ms-2">Bobot: <strong>{{ number_format($q->weight, 1) }}</strong></span>
                </div>
                <div>
                    <span class="badge text-bg-light border text-dark fs-7">
                        Kunci: <strong class="text-success font-monospace">{{ $q->getCorrectAnswerLabel() ?? '-' }}</strong>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <!-- Question Text & Image -->
                <div class="mb-3 fs-6 text-dark">
                    {!! nl2br(e($q->question_text)) !!}
                </div>
                @if($q->hasImage())
                    <div class="mb-3">
                        <img src="{{ $q->image_url }}" alt="Gambar Soal #{{ $q->order }}" class="img-fluid rounded border shadow-sm" style="max-height: 250px;">
                    </div>
                @endif

                <!-- Participant Answer Accuracy Bar -->
                <div class="row g-2 mb-3 bg-light p-3 rounded border">
                    <div class="col-md-3 text-center">
                        <div class="text-secondary small fw-bold text-uppercase">Peserta Menjawab</div>
                        <div class="fs-4 fw-bold text-dark">{{ $item->answered_count }} <span class="fs-7 text-secondary fw-normal">/ {{ $item->total_participants }}</span></div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-success small fw-bold text-uppercase">&#10003; Benar</div>
                        <div class="fs-4 fw-bold text-success">{{ $item->correct_count }} <span class="fs-7 fw-normal">({{ $item->correct_percentage }}%)</span></div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-danger small fw-bold text-uppercase">&#10007; Salah</div>
                        <div class="fs-4 fw-bold text-danger">{{ $item->incorrect_count }} <span class="fs-7 fw-normal">({{ $item->incorrect_percentage }}%)</span></div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="text-secondary small fw-bold text-uppercase">Kosong</div>
                        <div class="fs-4 fw-bold text-secondary">{{ $item->unanswered_count }} <span class="fs-7 fw-normal">({{ $item->unanswered_percentage }}%)</span></div>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $item->correct_percentage }}%;" title="Benar: {{ $item->correct_percentage }}%"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $item->incorrect_percentage }}%;" title="Salah: {{ $item->incorrect_percentage }}%"></div>
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $item->unanswered_percentage }}%;" title="Kosong: {{ $item->unanswered_percentage }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Distractor / Options Distribution Table -->
                <h6 class="fw-bold mb-2">&#128202; Distribusi Pemilihan Opsi Jawaban (Analisis Distraktor):</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">Opsi</th>
                                <th>Teks Jawaban</th>
                                <th class="text-center" style="width: 170px;">Status Pengecoh</th>
                                <th class="text-center" style="width: 100px;">Pemilih</th>
                                <th class="text-center" style="width: 110px;">Persentase</th>
                                <th style="width: 180px;">Visualisasi Sebaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->options_distribution as $opt)
                                <tr class="{{ $opt->is_correct ? 'table-success' : '' }}">
                                    <td class="text-center font-monospace fw-bold">
                                        <span class="badge {{ $opt->is_correct ? 'text-bg-success' : 'text-bg-light border text-dark' }} px-2 py-1">
                                            {{ $opt->label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $opt->is_correct ? 'fw-bold text-dark' : 'text-secondary' }}">
                                            {{ $opt->option_text }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-{{ $opt->distractor_badge }} fs-7">
                                            {{ $opt->distractor_status }}
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace fw-bold">
                                        {{ $opt->count }} Siswa
                                    </td>
                                    <td class="text-center font-monospace fw-bold">
                                        {{ $opt->percentage }}%
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 12px;">
                                            <div class="progress-bar bg-{{ $opt->is_correct ? 'success' : 'info' }}" role="progressbar" style="width: {{ $opt->percentage }}%;" aria-valuenow="{{ $opt->percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
