@extends('layouts.app')

@section('title', 'Edit Bank Soal: ' . $questionBank->name . ' - CBT School')
@section('page_title', 'Edit Bank Soal')
@section('page_subtitle', 'Pembaruan informasi paket bank soal')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.question-banks.index') }}">Bank Soal</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <x-card title="Edit Informasi Bank Soal">
            <form method="POST" action="{{ route('teacher.question-banks.update', $questionBank) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Kode Bank Soal <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $questionBank->code) }}" required maxlength="50">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Bank Soal <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $questionBank->name) }}" required maxlength="150">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $sb)
                                <option value="{{ $sb->id }}" {{ old('subject_id', $questionBank->subject_id) == $sb->id ? 'selected' : '' }}>
                                    {{ $sb->code }} - {{ $sb->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tingkat Kelas Sasaran</label>
                        <select name="level" class="form-select @error('level') is-invalid @enderror">
                            <option value="">Semua Tingkat</option>
                            @foreach(['Semua', '10', '11', '12'] as $lvl)
                                <option value="{{ $lvl }}" {{ old('level', $questionBank->level) === $lvl ? 'selected' : '' }}>
                                    {{ $lvl === 'Semua' ? 'Semua Tingkat (Umum)' : 'Tingkat ' . $lvl }}
                                </option>
                            @endforeach
                        </select>
                        @error('level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi / Ruang Lingkup Materi</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $questionBank->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="bank_active" {{ old('is_active', $questionBank->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="bank_active">Status Aktif</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('teacher.question-banks.show', $questionBank) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
