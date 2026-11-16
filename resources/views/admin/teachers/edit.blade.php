@extends('layouts.app')

@section('title', 'Edit Guru: ' . $teacher->name . ' - CBT School')
@section('page_title', 'Edit Data Guru')
@section('page_subtitle', 'Pembaruan biodata tenaga pendidik, akun pengguna, dan penugasan mengajar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.teachers.index') }}">Guru</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit: {{ $teacher->name }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.teachers.update', $teacher) }}">
    @csrf
    @method('PUT')

    @php
        $assignedSubjectIds = old('subject_ids', $teacher->subjects->pluck('id')->toArray());
        $assignedClassroomIds = old('classroom_ids', $teacher->classrooms->pluck('id')->toArray());
    @endphp

    <div class="row">
        <!-- Kolom Kiri: Biodata Guru & Akun Pengguna -->
        <div class="col-md-7">
            <x-card title="Informasi Guru & Akun Pengguna">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap Guru <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $teacher->name) }}" required maxlength="150">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $teacher->nip) }}" maxlength="50">
                        @error('nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('gender', $teacher->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender', $teacher->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $teacher->phone) }}" maxlength="30">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $teacher->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <h6 class="fw-bold text-primary mb-3">&#128272; Akun Pengguna CBT</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $teacher->user?->username) }}" required maxlength="50">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $teacher->user?->email) }}" required maxlength="150">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password Login</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small class="text-secondary">Hanya isi bidang ini jika ingin mengatur ulang kata sandi login guru.</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="teacher_active" {{ old('is_active', $teacher->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="teacher_active">Status Akun Aktif</label>
                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Penugasan Mengajar (Mata Pelajaran & Kelas) -->
        <div class="col-md-5">
            <!-- Penugasan Mapel -->
            <x-card title="Mata Pelajaran yang Diampu">
                <p class="small text-secondary mb-2">Centang mata pelajaran yang diampu oleh guru ini:</p>
                <div style="max-height: 220px; overflow-y: auto;" class="border rounded p-2 bg-body-tertiary">
                    @forelse($subjects as $subj)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="subject_ids[]" value="{{ $subj->id }}" id="subj_{{ $subj->id }}" {{ in_array($subj->id, $assignedSubjectIds) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="subj_{{ $subj->id }}">
                                <strong>{{ $subj->code }}</strong> - {{ $subj->name }}
                                <span class="badge text-bg-light border ms-1">{{ $subj->level ?? 'Semua' }}</span>
                            </label>
                        </div>
                    @empty
                        <span class="text-secondary small">Belum ada mata pelajaran terdaftar.</span>
                    @endforelse
                </div>
            </x-card>

            <!-- Penugasan Kelas -->
            <x-card title="Kelas yang Diajar" class="mt-3">
                <p class="small text-secondary mb-2">Centang kelas rombongan belajar yang diajar oleh guru ini:</p>
                <div style="max-height: 220px; overflow-y: auto;" class="border rounded p-2 bg-body-tertiary">
                    @forelse($classrooms as $cls)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}" id="cls_{{ $cls->id }}" {{ in_array($cls->id, $assignedClassroomIds) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="cls_{{ $cls->id }}">
                                <strong>{{ $cls->name }}</strong>
                                @if($cls->department)
                                    <span class="badge text-bg-info text-white ms-1">{{ $cls->department->code }}</span>
                                @endif
                            </label>
                        </div>
                    @empty
                        <span class="text-secondary small">Belum ada kelas terdaftar.</span>
                    @endforelse
                </div>
            </x-card>

            <!-- Action Buttons Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
