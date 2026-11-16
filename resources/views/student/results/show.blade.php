@extends('layouts.app')

@section('title', 'Rincian Hasil — ' . $exam->title . ' - CBT School')
@section('page_title', 'Rincian Hasil & Pembahasan Soal')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student.results.index') }}">Riwayat & Nilai</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $exam->code }}</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('student.results.index') }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Riwayat Nilai
        </a>
    </div>
</div>

<!-- Score Summary Banner -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-5 border-end">
                <div class="d-flex align-items-center">
                    <span class="fs-1 me-3">&#127942;</span>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $exam->title }}</h4>
                        <div class="text-secondary small">
                            {{ $exam->subject->name ?? '-' }} &bull;
                            Guru: {{ $exam->teacher->name ?? ($exam->teacher->user->name ?? '-') }}
                        </div>
                    </div>
                </div>
                <div class="text-secondary small mt-3">
                    <span>KKM: <strong>{{ number_format($exam->passing_grade, 0) }}</strong></span> &bull;
                    <span>Diselesaikan: <strong>{{ $attempt->completed_at?->format('d/m/Y H:i') ?? '-' }}</strong></span>
                </div>
            </div>
            <div class="col-md-7 mt-3 mt-md-0">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="text-secondary small fw-bold text-uppercase">Nilai Akhir</div>
                        <div class="fs-2 fw-bold {{ (float) $attempt->score >= (float) $exam->passing_grade ? 'text-success' : 'text-danger' }}">
                            {{ number_format($attempt->score, 1) }}
                        </div>
                        <div>
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

<!-- Detailed Question Breakdown -->
<div class="row">
    <div class="col-12">
        <h5 class="fw-bold mb-3">&#128221; Pembahasan Lembar Jawaban</h5>

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
                                        <span class="badge text-bg-success">&#10003; Jawaban Anda (Benar)</span>
                                    @elseif($isSelected && ! $isKey)
                                        <span class="badge text-bg-danger">&#10007; Jawaban Anda (Salah)</span>
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
