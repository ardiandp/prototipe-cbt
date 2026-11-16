@extends('layouts.app')

@section('title', 'Pilih Soal dari Bank Soal - ' . $exam->title . ' - CBT School')
@section('page_title', 'Pilih Butir Soal dari Bank Soal')
@section('page_subtitle', 'Mata Pelajaran: ' . $exam->subject?->name . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.questions.index', $exam) }}">Butir Soal</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pilih dari Bank</li>
@endsection

@section('content')
<x-card title="Koleksi Bank Soal Tersedia">
    <x-slot:tools>
        <a href="{{ route('teacher.exams.questions.index', $exam) }}" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Daftar Soal Ujian
        </a>
    </x-slot:tools>

    <!-- Filter Bank Soal -->
    <form method="GET" action="{{ route('teacher.exams.questions.create', $exam) }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari teks soal atau kode..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori / Paket --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->code }} - {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="difficulty" class="form-select">
                    <option value="">-- Tingkat Kesulitan --</option>
                    <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                    <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                    <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Sulit (Hard)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100">&#128269; Saring</button>
                @if(request()->hasAny(['search', 'category_id', 'difficulty']))
                    <a href="{{ route('teacher.exams.questions.create', $exam) }}" class="btn btn-outline-secondary" title="Reset">&#10006;</a>
                @endif
            </div>
        </div>
    </form>

    @if($availableQuestions->isEmpty())
        <div class="text-center py-5">
            <div class="text-secondary mb-3 fs-1">&#128269;</div>
            <h5 class="fw-bold text-secondary">Tidak ada butir soal baru yang tersedia</h5>
            <p class="text-secondary small mb-3">Semua soal pada mata pelajaran ini mungkin sudah terpilih ke dalam ujian, atau belum ada soal aktif di bank soal.</p>
            <a href="{{ route('teacher.question-banks.index') }}" class="btn btn-outline-primary btn-sm">
                Lihat Modul Bank Soal
            </a>
        </div>
    @else
        <!-- Form Pemilihan Soal -->
        <form method="POST" action="{{ route('teacher.exams.questions.store', $exam) }}">
            @csrf

            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light border rounded">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                    <label class="form-check-label fw-bold cursor-pointer" for="selectAllCheckbox">
                        Pilih Semua Soal di Halaman Ini (<span id="selectedCountBadge">0</span> terpilih)
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                    &#43; Tambahkan Soal Terpilih ke Ujian
                </button>
            </div>

            @error('question_ids')
                <div class="alert alert-danger small py-2 mb-3">{{ $message }}</div>
            @enderror

            <div class="list-group list-group-flush mb-4">
                @foreach($availableQuestions as $index => $q)
                    <div class="list-group-item p-3 border rounded mb-3 bg-body-tertiary">
                        <div class="d-flex align-items-start gap-3">
                            <div class="pt-1">
                                <input class="form-check-input question-checkbox" type="checkbox" name="question_ids[]" value="{{ $q->id }}" id="q_{{ $q->id }}" onchange="updateSelectedCount()">
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label for="q_{{ $q->id }}" class="fw-bold cursor-pointer mb-0">
                                            @if($q->code)
                                                <span class="badge text-bg-light border font-monospace me-1">{{ $q->code }}</span>
                                            @endif
                                            <span class="badge text-bg-info text-white me-1">{{ $q->category?->name }}</span>

                                            @if($q->difficulty === 'easy')
                                                <span class="badge text-bg-success">Mudah</span>
                                            @elseif($q->difficulty === 'hard')
                                                <span class="badge text-bg-danger">Sulit</span>
                                            @else
                                                <span class="badge text-bg-warning text-dark">Sedang</span>
                                            @endif

                                            <span class="badge text-bg-secondary ms-1">Bobot: {{ $q->weight }}</span>
                                        </label>
                                    </div>
                                </div>

                                <label for="q_{{ $q->id }}" class="cursor-pointer text-body mb-2 d-block">
                                    {!! nl2br(e($q->question_text)) !!}
                                </label>

                                @if($q->hasImage())
                                    <div class="mb-2">
                                        <img src="{{ $q->image_url }}" alt="Gambar" class="img-thumbnail" style="max-height: 140px; object-fit: contain;">
                                    </div>
                                @endif

                                <!-- Opsi Jawaban Preview -->
                                <div class="row g-1 small text-secondary">
                                    @foreach($q->options as $opt)
                                        <div class="col-md-6">
                                            <div class="p-1 px-2 border rounded {{ $opt->is_correct ? 'bg-success-subtle text-success-emphasis fw-bold border-success' : 'bg-white' }}">
                                                <span class="badge {{ $opt->is_correct ? 'text-bg-success' : 'text-bg-light border' }} me-1">{{ $opt->label }}</span>
                                                <span>{{ Str::limit($opt->option_text, 80) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2">
                <button type="submit" class="btn btn-primary fw-bold py-2 px-4">
                    &#43; Tambahkan Soal Terpilih ke Ujian
                </button>

                @if($availableQuestions->hasPages())
                    <div>
                        {{ $availableQuestions->links() }}
                    </div>
                @endif
            </div>
        </form>
    @endif
</x-card>

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.question-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkedCount = document.querySelectorAll('.question-checkbox:checked').length;
    const badge = document.getElementById('selectedCountBadge');
    if (badge) {
        badge.textContent = checkedCount;
    }
}
</script>
@endsection

