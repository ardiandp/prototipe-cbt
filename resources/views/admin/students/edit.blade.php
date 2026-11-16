@extends('layouts.app')

@section('title', 'Edit Siswa: ' . $student->name . ' - CBT School')
@section('page_title', 'Edit Data Siswa')
@section('page_subtitle', 'Pembaruan data peserta didik dan kredensial akun login CBT')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Siswa</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit: {{ $student->name }}</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.students.update', $student) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Kolom Kiri: Biodata Siswa -->
        <div class="col-md-7">
            <x-card title="Biodata Peserta Didik">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" required maxlength="150">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">NIS (Nomor Induk Siswa) <span class="text-danger">*</span></label>
                        <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $student->nis) }}" required maxlength="50">
                        @error('nis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NISN (Nasional)</label>
                        <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $student->nisn) }}" maxlength="50">
                        @error('nisn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Rombongan Belajar (Kelas) <span class="text-danger">*</span></label>
                        <select name="classroom_id" class="form-select @error('classroom_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}" {{ old('classroom_id', $student->classroom_id) == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }} {{ $cls->department ? '(' . $cls->department->code . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('classroom_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('gender', $student->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender', $student->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}">
                        @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}" maxlength="30">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Tempat Tinggal</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $student->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Akun Pengguna CBT -->
        <div class="col-md-5">
            <x-card title="Akun Pengguna CBT">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $student->user?->username) }}" required maxlength="50">
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $student->user?->email) }}" required maxlength="150">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password Login</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small class="text-secondary">Hanya isi jika ingin mereset password siswa.</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="student_active" {{ old('is_active', $student->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="student_active">Status Akun Aktif</label>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </x-card>
        </div>
    </div>
</form>
@endsection
