@extends('layouts.app')

@section('title', 'Lembar Jawaban — ' . $attempt->student->name . ' - CBT School')
@section('page_title', 'Rincian Lembar Jawaban Peserta')
@section('page_subtitle', $attempt->student->name . ' (' . ($attempt->student->nis ?? '-') . ') &bull; ' . $exam->title)

@php
    $routePrefix = auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? 'teacher.' : 'admin.';
@endphp

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ auth()->user()->isTeacher() && !auth()->user()->isAdmin() ? route('teacher.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'results.index') }}">Hasil & Rekap Nilai</a></li>
    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'results.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jawaban {{ $attempt->student->name }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route($routePrefix . 'results.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Rekap Nilai
        </a>
    </div>
</div>

<!-- Student Attempt Summary Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6 border-end">
                <div class="d-flex align-items-center mb-2">
                    <span class="fs-1 me-3">&#127891;</span>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $attempt->student->name }}</h4>
                        <div class="text-secondary small">
                            NIS: <span class="font-monospace fw-bold">{{ $attempt->student->nis ?? '-' }}</span> &bull;
                            Kelas: <span class="badge text-bg-light border">{{ $attempt->student->classroom->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-secondary small mt-2">
                    <span>Sesi Attempt: <strong>Ke-{{ $attempt->attempt_number }}</strong></span> &bull;
                    <span>Mulai: <strong>{{ $attempt->started_at?->format('d/m/Y H:i:s') ?? '-' }}</strong></span> &bull;
                    <span>Selesai: <strong>{{ $attempt->completed_at?->format('d/m/Y H:i:s') ?? '-' }}</strong></span>
                </div>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="text-secondary small fw-bold text-uppercase">Nilai Akhir</div>
                        <div class="fs-3 fw-bold {{ (float) $attempt->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                            {{ number_format($attempt->score, 1) }}
                        </div>
                        <div class="small">
                            @if((float) $attempt->score >= (float) $exam->passing_grade)
                                <span class="badge text-bg-success">&#10003; Tuntas KKM</span>
                            @else
                                <span class="badge text-bg-danger">&#9888; Remedial</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-secondary small fw-bold text-uppercase">Benar</div>
                        <div class="fs-3 fw-bold text-success">{{ $attempt->total_correct }}</div>
                        <div class="small text-secondary">Soal</div>
                    </div>
                    <div class="col-3">
                        <div class="text-secondary small fw-bold text-uppercase">Salah</div>
                        <div class="fs-3 fw-bold text-danger">{{ $attempt->total_incorrect }}</div>
                        <div class="small text-secondary">Soal</div>
                    </div>
                    <div class="col-3">
                        <div class="text-secondary small fw-bold text-uppercase">Kosong</div>
                        <div class="fs-3 fw-bold text-secondary">{{ $attempt->total_unanswered }}</div>
                        <div class="small text-secondary">Soal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question by Question Breakdown -->
<div class="row">
    <div class="col-12">
        <h5 class="fw-bold mb-3">&#128221; Rincian Jawaban Soal</h5>

        @foreach($attempt->attemptQuestions as $index => $aq)
            @php
                $q = $aq->question;
                $isAnswered = $aq->isAnswered();
                $isCorrect = (bool) $aq->is_correct;
            @endphp
            <div class="card shadow-sm border-0 mb-3 {{ $isAnswered ? ($isCorrect ? 'border-start border-success border-4' : 'border-start border-danger border-4') : 'border-start border-secondary border-4' }}">
                <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge text-bg-dark font-monospace me-2">Soal #{{ $aq->order }}</span>
                        <span class="text-secondary small">Bobot: <strong>{{ number_format($q->weight ?? 1, 1) }}</strong></span>
                    </div>
                    <div>
                        @if(! $isAnswered)
                            <span class="badge text-bg-secondary">Tidak Dijawab</span>
                            <span class="badge text-bg-light border text-dark ms-1">Poin: 0.0</span>
                        @elseif($isCorrect)
                            <span class="badge text-bg-success">&#10003; Benar</span>
                            <span class="badge text-bg-light border text-success fw-bold ms-1">Poin: +{{ number_format($aq->earned_weight, 1) }}</span>
                        @else
                            <span class="badge text-bg-danger">&#10007; Salah</span>
                            <span class="badge text-bg-light border text-danger fw-bold ms-1">Poin: 0.0</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Question Text -->
                    <div class="mb-3 fs-6">
                        {!! nl2br(e($q->question_text ?? '')) !!}
                    </div>

                    <!-- Options List -->
                    <div class="list-group">
                        @foreach($aq->options as $optOrder => $attemptOpt)
                            @php
                                $opt = $attemptOpt->questionOption;
                                $isSelected = $aq->selected_exam_question_option_id === $opt?->id;
                                $isKey = (bool) $opt?->is_correct;
                                
                                $optClass = '';
                                if ($isSelected && $isKey) {
                                    $optClass = 'list-group-item-success border-success';
                                } elseif ($isSelected && ! $isKey) {
                                    $optClass = 'list-group-item-danger border-danger';
                                } elseif (! $isSelected && $isKey) {
                                    $optClass = 'list-group-item-success border-success opacity-75';
                                }
                            @endphp
                            <div class="list-group-item d-flex align-items-center {{ $optClass }}">
                                <span class="badge {{ $isSelected ? 'text-bg-primary' : 'text-bg-light border text-dark' }} me-2 font-monospace px-2 py-1">
                                    {{ $attemptOpt->display_label ?? $opt?->label }}
                                </span>
                                <div class="flex-grow-1">
                                    {{ $attemptOpt->option_text }}
                                </div>
                                <div class="ms-2">
                                    @if($isSelected && $isKey)
                                        <span class="badge text-bg-success">&#10003; Jawaban Siswa (Benar)</span>
                                    @elseif($isSelected && ! $isKey)
                                        <span class="badge text-bg-danger">&#10007; Jawaban Siswa (Salah)</span>
                                    @elseif(! $isSelected && $isKey)
                                        <span class="badge text-bg-outline-success border border-success text-success">&#10003; Kunci Jawaban</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
