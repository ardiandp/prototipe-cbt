@extends('layouts.app')

@section('title', 'Edit Butir Soal - ' . $questionBank->name . ' - CBT School')
@section('page_title', 'Edit Butir Soal')
@section('page_subtitle', 'Paket Bank Soal: ' . $questionBank->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.question-banks.index') }}">Bank Soal</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.question-banks.show', $questionBank) }}">{{ $questionBank->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Soal</li>
@endsection

@section('content')
<form method="POST" action="{{ route('teacher.questions.update', [$questionBank, $question]) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Kolom Kiri: Teks Pertanyaan & Opsi Jawaban -->
        <div class="col-md-8">
            <x-card title="Pertanyaan & Opsi Jawaban Pilihan Ganda (A - E)">
                <div class="mb-3">
                    <label class="form-label fw-bold">Teks Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror" rows="5" required>{{ old('question_text', $question->question_text) }}</textarea>
                    @error('question_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-primary mb-0">&#128221; Pilihan Jawaban (A &mdash; E)</h6>
                    <small class="text-secondary">Pilih satu radio button pada opsi yang menjadi <strong>kunci jawaban benar</strong>.</small>
                </div>

                @error('correct_option')
                    <div class="alert alert-danger small py-2 mb-3">Silakan tentukan kunci jawaban benar (A, B, C, D, atau E).</div>
                @enderror

                @foreach(['A', 'B', 'C', 'D', 'E'] as $label)
                    @php
                        $existingText = $optionsMap->get($label)?->option_text ?? '';
                    @endphp
                    <div class="mb-3 p-3 border rounded bg-body-tertiary">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge text-bg-dark font-monospace me-2 fs-6">{{ $label }}</span>
                            <div class="form-check form-check-inline ms-auto mb-0">
                                <input class="form-check-input" type="radio" name="correct_option" value="{{ $label }}" id="correct_{{ $label }}" {{ old('correct_option', $correctOption) === $label ? 'checked' : '' }} required>
                                <label class="form-check-label fw-bold text-success" for="correct_{{ $label }}">
                                    &#10004; Kunci Jawaban Benar ({{ $label }})
                                </label>
                            </div>
                        </div>
                        <textarea name="options[{{ $label }}]" class="form-control @error("options.{$label}") is-invalid @enderror" rows="2" required>{{ old("options.{$label}", $existingText) }}</textarea>
                        @error("options.{$label}")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </x-card>
        </div>

        <!-- Kolom Kanan: Pengaturan Bobot & Status Soal -->
        <div class="col-md-4">
            <x-card title="Parameter Soal">
                <div class="mb-3">
                    <label class="form-label">Kode / Nomor Identifikasi Butir Soal</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $question->code) }}" maxlength="50">
                    <small class="text-secondary">Opsional. Untuk penandaan butir soal internal.</small>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tingkat Kesulitan <span class="text-danger">*</span></label>
                    <select name="difficulty" class="form-select @error('difficulty') is-invalid @enderror" required>
                        <option value="easy" {{ old('difficulty', $question->difficulty) === 'easy' ? 'selected' : '' }}>Mudah (Easy)</option>
                        <option value="medium" {{ old('difficulty', $question->difficulty) === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                        <option value="hard" {{ old('difficulty', $question->difficulty) === 'hard' ? 'selected' : '' }}>Sulit (Hard)</option>
                    </select>
                    @error('difficulty')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Bobot Nilai <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight', $question->weight) }}" required>
                    <small class="text-secondary">Poin yang diperoleh jika siswa menjawab benar.</small>
                    @error('weight')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status Publikasi <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status', $question->status) === 'active' ? 'selected' : '' }}>Aktif (Siap Masuk Ujian)</option>
                        <option value="draft" {{ old('status', $question->status) === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                        <option value="inactive" {{ old('status', $question->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold py-2">Simpan Perubahan Soal</button>
                    <a href="{{ route('teacher.question-banks.show', $questionBank) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </x-card>
        </div>
    </div>
</form>
@endsection
