@extends('layouts.app')

@section('title', 'Butir Soal Ujian - ' . $exam->title . ' - CBT School')
@section('page_title', 'Snapshot Butir Soal Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Butir Soal</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="p-3 border rounded bg-white shadow-xs d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge text-bg-{{ $exam->computed_status_badge_class }} me-2 fs-7">
                    {{ $exam->computed_status_label }}
                </span>
                <span class="fw-bold fs-6 me-3">{{ $exam->title }}</span>
                <span class="text-secondary small">Mapel: <strong>{{ $exam->subject?->name }}</strong></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light border px-3 py-2 fs-7">
                    Total Soal: <strong class="text-primary">{{ $questions->total() }}</strong>
                </span>
                <span class="badge text-bg-light border px-3 py-2 fs-7">
                    Total Bobot: <strong class="text-success">{{ number_format($exam->total_weight, 2) }}</strong>
                </span>
                <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                    &#43; Pilih Soal dari Bank Soal
                </a>
                <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Detail Ujian
                </a>
            </div>
        </div>
    </div>
</div>

<x-card title="Daftar Butir Soal Terpilih (Snapshot)">
    @if($questions->isEmpty())
        <div class="text-center py-5">
            <div class="text-secondary mb-3 fs-1">&#128221;</div>
            <h5 class="fw-bold text-secondary">Belum ada butir soal yang dipilih ke ujian ini</h5>
            <p class="text-secondary small mb-3">Silakan pilih butir soal dari bank soal yang sesuai dengan mata pelajaran {{ $exam->subject?->name }}.</p>
            <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-primary btn-sm fw-bold">
                Pilih Soal dari Bank Soal Sekarang
            </a>
        </div>
    @else
        <div class="alert alert-info small d-flex align-items-center mb-3">
            <span class="me-2 fs-5">&#8505;</span>
            <span>
                Seluruh butir soal dan opsi jawaban di bawah ini tersimpan secara <strong>immutable snapshot</strong>. Perubahan atau penghapusan soal di bank soal tidak akan mempengaruhi materi pelaksanaan ujian ini.
            </span>
        </div>

        <div class="list-group list-group-flush">
            @foreach($questions as $index => $q)
                <div class="list-group-item p-3 border rounded mb-3 shadow-xs">
                    <!-- Header Soal Snapshot -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge text-bg-dark font-monospace me-1">#{{ $q->order }}</span>
                            @if($q->code)
                                <span class="badge text-bg-light border font-monospace me-1">{{ $q->code }}</span>
                            @endif

                            @if($q->difficulty === 'easy')
                                <span class="badge text-bg-success">Mudah</span>
                            @elseif($q->difficulty === 'hard')
                                <span class="badge text-bg-danger">Sulit</span>
                            @else
                                <span class="badge text-bg-warning text-dark">Sedang</span>
                            @endif

                            <span class="badge text-bg-info text-white ms-1">Bobot: {{ $q->weight }}</span>

                            @if($q->hasImage())
                                <span class="badge text-bg-secondary ms-1">&#128444; Bergambar</span>
                            @endif
                        </div>

                        <!-- Action Toolbar -->
                        <form method="POST" action="{{ route('teacher.exams.questions.destroy', [$exam, $q]) }}" class="d-inline" onsubmit="return confirm('Hapus butir soal #{{ $q->order }} dari jadwal ujian ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Soal dari Ujian">
                                &#128465; Hapus dari Ujian
                            </button>
                        </form>
                    </div>

                    <!-- Teks Pertanyaan -->
                    <div class="fs-6 fw-semibold text-body mb-3">
                        {!! nl2br(e($q->question_text)) !!}
                    </div>

                    @if($q->hasImage())
                        <div class="mb-3">
                            <img src="{{ $q->image_url }}" alt="Lampiran Soal" class="img-thumbnail" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                        </div>
                    @endif

                    <!-- Pilihan Jawaban A - E -->
                    <div class="row g-2">
                        @foreach($q->options as $opt)
                            <div class="col-md-6">
                                <div class="p-2 border rounded small d-flex align-items-center {{ $opt->is_correct ? 'bg-success-subtle border-success text-success-emphasis fw-bold' : 'bg-body-tertiary' }}">
                                    <span class="badge {{ $opt->is_correct ? 'text-bg-success' : 'text-bg-light border' }} me-2 font-monospace">{{ $opt->label }}</span>
                                    <span>{{ $opt->option_text }}</span>
                                    @if($opt->hasImage())
                                        <img src="{{ $opt->image_url }}" alt="Opsi {{ $opt->label }}" class="rounded border ms-2" style="max-height: 40px; max-width: 60px; object-fit: contain;">
                                    @endif
                                    @if($opt->is_correct)
                                        <span class="ms-auto badge text-bg-success fs-8">&#10004; Kunci Benar</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if($questions->hasPages())
            <div class="mt-4">
                {{ $questions->links() }}
            </div>
        @endif
    @endif
</x-card>
@endsection

