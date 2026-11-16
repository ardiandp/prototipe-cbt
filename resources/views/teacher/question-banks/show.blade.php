@extends('layouts.app')

@section('title', 'Detail Bank Soal: ' . $questionBank->name . ' - CBT School')
@section('page_title', $questionBank->name)
@section('page_subtitle', 'Paket Soal ' . ($questionBank->subject?->name ?? 'Mata Pelajaran'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.question-banks.index') }}">Bank Soal</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $questionBank->code }}</li>
@endsection

@section('content')
<!-- Header Card Bank Soal -->
<div class="card shadow-sm mb-4 border-start border-primary border-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <span class="badge text-bg-dark font-monospace fs-7 me-1">{{ $questionBank->code }}</span>
                <span class="badge text-bg-info text-white me-1">{{ $questionBank->subject?->name ?? '-' }}</span>
                <span class="badge text-bg-light border me-1">Tingkat {{ $questionBank->level ?? 'Semua' }}</span>
                @if($questionBank->is_active)
                    <span class="badge text-bg-success">Aktif</span>
                @else
                    <span class="badge text-bg-secondary">Nonaktif</span>
                @endif
                <h4 class="fw-bold mt-2 mb-1">{{ $questionBank->name }}</h4>
                <p class="text-secondary mb-0 small">{{ $questionBank->description ?: 'Tidak ada deskripsi ruang lingkup materi.' }}</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="{{ route('teacher.questions.create', $questionBank) }}" class="btn btn-primary fw-bold shadow-sm">
                    + Tambah Butir Soal
                </a>
                <a href="{{ route('teacher.question-banks.edit', $questionBank) }}" class="btn btn-outline-secondary">
                    &#9998; Edit Bank
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Butir Soal -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.question-banks.show', $questionBank) }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari teks soal atau kode..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="difficulty" class="form-select">
                    <option value="">Semua Kesulitan</option>
                    <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Mudah</option>
                    <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Sedang</option>
                    <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Sulit</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                @if(request()->hasAny(['search', 'difficulty', 'status']))
                    <a href="{{ route('teacher.question-banks.show', $questionBank) }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Daftar Butir Soal -->
<x-card title="Daftar Butir Soal (Total: {{ $questions->total() }} Soal)">
    @if($questions->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1 text-secondary mb-2">&#128221;</div>
            <h5 class="fw-bold">Belum Ada Butir Soal</h5>
            <p class="text-secondary small mb-3">Mulai buat butir soal pilihan ganda (A-E) untuk bank soal ini.</p>
            <a href="{{ route('teacher.questions.create', $questionBank) }}" class="btn btn-primary fw-bold">
                + Tambah Butir Soal Pertama
            </a>
        </div>
    @else
        <div class="list-group list-group-flush">
            @foreach($questions as $index => $q)
                <div class="list-group-item p-3 border rounded mb-3 shadow-xs">
                    <!-- Soal Header -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge text-bg-dark font-monospace me-1">#{{ $questions->firstItem() + $index }}</span>
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

                            @if($q->status === 'active')
                                <span class="badge text-bg-primary ms-1">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary ms-1">Draft</span>
                            @endif
                        </div>

                        <!-- Action Toolbar -->
                        <div class="btn-group btn-group-sm">
                            <!-- Preview Button -->
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPreviewQ{{ $q->id }}" title="Pratinjau Siswa">
                                &#128065; Preview
                            </button>

                            <!-- Duplicate Form -->
                            <form method="POST" action="{{ route('teacher.questions.duplicate', [$questionBank, $q]) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary" title="Duplikasi Soal" onclick="return confirm('Duplikasi butir soal ini?')">
                                    &#128203; Duplikat
                                </button>
                            </form>

                            <!-- Edit Button -->
                            <a href="{{ route('teacher.questions.edit', [$questionBank, $q]) }}" class="btn btn-outline-primary" title="Edit Soal">
                                &#9998; Edit
                            </a>

                            <!-- Delete Button -->
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteQ{{ $q->id }}" title="Hapus Soal">
                                &#128465;
                            </button>
                        </div>
                    </div>

                    <!-- Teks Pertanyaan -->
                    <div class="fs-6 fw-semibold text-body mb-3">
                        {!! nl2br(e($q->question_text)) !!}
                    </div>

                    <!-- Opsi Jawaban A - E -->
                    <div class="row g-2">
                        @foreach($q->options as $opt)
                            <div class="col-md-6">
                                <div class="p-2 border rounded small d-flex align-items-center {{ $opt->is_correct ? 'bg-success-subtle border-success text-success-emphasis fw-bold' : 'bg-body-tertiary' }}">
                                    <span class="badge {{ $opt->is_correct ? 'text-bg-success' : 'text-bg-light border' }} me-2 font-monospace">{{ $opt->label }}</span>
                                    <span>{{ $opt->option_text }}</span>
                                    @if($opt->is_correct)
                                        <span class="ms-auto badge text-bg-success fs-8">&#10004; Kunci Benar</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Modal Preview (Student View) -->
                    <x-modal id="modalPreviewQ{{ $q->id }}" title="Pratinjau Tampilan Ujian Siswa">
                        <div class="p-3 text-start">
                            <div class="alert alert-secondary small d-flex justify-content-between align-items-center mb-3">
                                <span>Tampilan antarmuka butir soal pada layar CBT siswa:</span>
                                <span class="badge text-bg-info text-white">Bobot Soal: {{ $q->weight }}</span>
                            </div>

                            <h6 class="fw-bold mb-3">{!! nl2br(e($q->question_text)) !!}</h6>

                            <div class="d-flex flex-column gap-2 mb-4">
                                @foreach($q->options as $opt)
                                    <label class="p-2 border rounded d-flex align-items-center cursor-pointer hover-bg-light">
                                        <input type="radio" name="preview_choice_{{ $q->id }}" class="form-check-input me-3" disabled>
                                        <span class="badge text-bg-light border me-2 font-monospace">{{ $opt->label }}</span>
                                        <span>{{ $opt->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <hr>
                            <div class="p-2 bg-success-subtle border border-success rounded text-success-emphasis small">
                                <strong>Kunci Jawaban Guru:</strong> Pilihan <strong>{{ $q->getCorrectAnswerLabel() }}</strong> &mdash; {{ $q->correctOption?->option_text }}
                            </div>
                        </div>
                    </x-modal>

                    <!-- Modal Delete Question -->
                    <x-modal id="modalDeleteQ{{ $q->id }}" title="Konfirmasi Hapus Butir Soal">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus butir soal <strong>#{{ $questions->firstItem() + $index }}</strong>?</p>
                            <blockquote class="blockquote small text-secondary bg-body-tertiary p-2 rounded">
                                "{{ Str::limit($q->question_text, 100) }}"
                            </blockquote>
                        </div>
                        <form method="POST" action="{{ route('teacher.questions.destroy', [$questionBank, $q]) }}">
                            @csrf
                            @method('DELETE')
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus Soal</button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            @endforeach
        </div>

        @if($questions->hasPages())
            <div class="p-3 border-top">
                {{ $questions->links() }}
            </div>
        @endif
    @endif
</x-card>
@endsection
