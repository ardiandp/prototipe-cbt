@extends('layouts.app')

@section('title', 'Edit Jadwal Ujian - ' . $exam->title . ' - CBT School')
@section('page_title', 'Edit Jadwal Ujian')
@section('page_subtitle', 'Perbarui parameter, jadwal waktu, atau durasi pelaksanaan ujian: ' . $exam->code)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<form method="POST" action="{{ route('teacher.exams.update', $exam) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Kolom Kiri: Informasi Dasar & Jadwal Waktu -->
        <div class="col-md-8">
            <x-card title="Informasi Utama & Pengaturan Jadwal">
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Ujian <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $exam->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}" {{ old('subject_id', $exam->subject_id) == $subj->id ? 'selected' : '' }}>
                                    {{ $subj->code }} - {{ $subj->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Periode Akademik Terdaftar</label>
                        <input type="text" class="form-control bg-light" value="{{ $exam->academicYear?->name ?? '-' }} - Semester {{ $exam->semester?->semester ?? '-' }}" disabled readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Petunjuk & Deskripsi Ujian <small class="text-secondary">(Opsional)</small></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $exam->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <h6 class="fw-bold text-primary mb-3">&#9201; Rentang Waktu & Batas Pelaksanaan</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Waktu Mulai Ujian <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror" value="{{ old('start_at', $exam->start_at->format('Y-m-d\TH:i')) }}" required>
                        <small class="text-secondary">Siswa hanya dapat memulai ujian sejak waktu ini tiba.</small>
                        @error('start_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Waktu Selesai Ujian <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror" value="{{ old('end_at', $exam->end_at->format('Y-m-d\TH:i')) }}" required>
                        <small class="text-secondary">Akses pengerjaan akan tertutup tepat pada waktu ini.</small>
                        @error('end_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Durasi Pengerjaan (Menit) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="duration" min="1" max="1440" class="form-control @error('duration') is-invalid @enderror" value="{{ old('duration', $exam->duration) }}" required>
                            <span class="input-group-text">Menit</span>
                        </div>
                        <small class="text-secondary">Alokasi waktu timer individual siswa.</small>
                        @error('duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nilai KKM / Passing Grade <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="passing_grade" class="form-control @error('passing_grade') is-invalid @enderror" value="{{ old('passing_grade', $exam->passing_grade) }}" required>
                        <small class="text-secondary">Batas nilai ketuntasan minimal.</small>
                        @error('passing_grade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Parameter Teknis, Randomisasi & Publikasi -->
        <div class="col-md-4">
            <x-card title="Parameter & Pengaturan Ujian">
                <div class="mb-3">
                    <label class="form-label">Kode Unik Ujian</label>
                    <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror" value="{{ old('code', $exam->code) }}">
                    <small class="text-secondary">Kode unik identifikasi pelaksanaan ujian.</small>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Token Akses Ujian <small class="text-secondary">(Opsional)</small></label>
                    <input type="text" name="token" maxlength="10" class="form-control font-monospace text-uppercase @error('token') is-invalid @enderror" value="{{ old('token', $exam->token) }}" placeholder="Contoh: CBT2026">
                    <small class="text-secondary">Biarkan kosong jika ujian tidak membutuhkan token.</small>
                    @error('token')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-3">
                <h6 class="fw-bold text-secondary fs-7 text-uppercase mb-3">Acak & Keamanan</h6>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="random_question" value="1" id="random_question" {{ old('random_question', $exam->random_question) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="random_question">
                        Acak Urutan Soal
                    </label>
                    <small class="text-secondary d-block">Urutan butir soal akan diacak per attempt siswa.</small>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="random_answer" value="1" id="random_answer" {{ old('random_answer', $exam->random_answer) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="random_answer">
                        Acak Urutan Pilihan Jawaban
                    </label>
                    <small class="text-secondary d-block">Posisi opsi A, B, C, D, E akan diacak secara aman.</small>
                </div>

                <hr class="my-3">

                <div class="mb-4">
                    <label class="form-label fw-bold">Status Ujian <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="draft" {{ old('status', $exam->status) === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                        <option value="published" {{ old('status', $exam->status) === 'published' ? 'selected' : '' }}>Published (Terbit Sesuai Jadwal)</option>
                        <option value="cancelled" {{ old('status', $exam->status) === 'cancelled' ? 'selected' : '' }}>Cancelled (Batalkan Pelaksanaan)</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold py-2">
                        Simpan Perubahan Jadwal
                    </button>
                    <a href="{{ route('teacher.exams.show', $exam) }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</form>
@endsection
