@extends('layouts.app')

@section('title', 'Data Kelas - CBT School')
@section('page_title', 'Data Kelas')
@section('page_subtitle', 'Manajemen rombongan belajar dan kelas siswa')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kelas</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama kelas..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select">
                    <option value="">Semua Tingkat</option>
                    @foreach(['10', '11', '12'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>Tingkat {{ $lvl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="department_id" class="form-select">
                    <option value="">Semua Jurusan</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->code }} - {{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="academic_year_id" class="form-select">
                    <option value="">Semua Thn Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'level', 'department_id', 'academic_year_id', 'status']))
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreateClassroom">
                    + Tambah Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Daftar Kelas">
    <x-slot name="tools">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-secondary">
            &#127891; Kelola Jurusan
        </a>
    </x-slot>

    <x-table :headers="['#', 'Nama Kelas', 'Tingkat', 'Jurusan', 'Tahun Ajaran', 'Status', 'Aksi']" :empty="$classrooms->isEmpty()" emptyMessage="Belum ada data kelas yang ditemukan.">
        @foreach($classrooms as $index => $classroom)
            <tr>
                <td>{{ $classrooms->firstItem() + $index }}</td>
                <td class="fw-bold fs-6">{{ $classroom->name }}</td>
                <td>
                    <span class="badge text-bg-light border">Kelas {{ $classroom->level }}</span>
                </td>
                <td>
                    @if($classroom->department)
                        <span class="badge text-bg-info text-white">{{ $classroom->department->code }}</span>
                        <small class="text-secondary d-block">{{ $classroom->department->name }}</small>
                    @else
                        <span class="text-secondary">-</span>
                    @endif
                </td>
                <td>
                    @if($classroom->academicYear)
                        {{ $classroom->academicYear->name }}
                        @if($classroom->academicYear->is_active)
                            <span class="badge text-bg-primary ms-1">Aktif</span>
                        @endif
                    @else
                        <span class="text-secondary">-</span>
                    @endif
                </td>
                <td>
                    @if($classroom->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditClassroom{{ $classroom->id }}" title="Edit">
                            &#9998; Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteClassroom{{ $classroom->id }}" title="Hapus">
                            &#128465; Hapus
                        </button>
                    </div>

                    <!-- Modal Edit Classroom -->
                    <x-modal id="modalEditClassroom{{ $classroom->id }}" title="Edit Kelas: {{ $classroom->name }}">
                        <form method="POST" action="{{ route('admin.classes.update', $classroom) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $classroom->name) }}" required maxlength="100">
                                <small class="text-secondary">Contoh: X RPL 1, XI TKJ 2, XII IPA 3</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tingkat <span class="text-danger">*</span></label>
                                    <select name="level" class="form-select" required>
                                        @foreach(['10', '11', '12'] as $lvl)
                                            <option value="{{ $lvl }}" {{ old('level', $classroom->level) == $lvl ? 'selected' : '' }}>Tingkat {{ $lvl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Jurusan</label>
                                    <select name="department_id" class="form-select">
                                        <option value="">-- Pilih Jurusan (Opsional) --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $classroom->department_id) == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->code }} - {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="academic_year_id" class="form-select">
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $classroom->academic_year_id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_class_{{ $classroom->id }}" {{ $classroom->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit_active_class_{{ $classroom->id }}">Status Aktif</label>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </x-modal>

                    <!-- Modal Delete Classroom -->
                    <x-modal id="modalDeleteClassroom{{ $classroom->id }}" title="Konfirmasi Hapus Kelas">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus kelas <strong>{{ $classroom->name }}</strong>?</p>
                        </div>
                        <form method="POST" action="{{ route('admin.classes.destroy', $classroom) }}">
                            @csrf
                            @method('DELETE')
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                            </div>
                        </form>
                    </x-modal>
                </td>
            </tr>
        @endforeach
    </x-table>

    @if($classrooms->hasPages())
        <div class="p-3 border-top">
            {{ $classrooms->links() }}
        </div>
    @endif
</x-card>

<!-- Modal Create Classroom -->
<x-modal id="modalCreateClassroom" title="Tambah Kelas Baru">
    <form method="POST" action="{{ route('admin.classes.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Nama Kelas <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="100" placeholder="Contoh: X RPL 1">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tingkat <span class="text-danger">*</span></label>
                <select name="level" class="form-select @error('level') is-invalid @enderror" required>
                    <option value="">-- Pilih Tingkat --</option>
                    @foreach(['10', '11', '12'] as $lvl)
                        <option value="{{ $lvl }}" {{ old('level') == $lvl ? 'selected' : '' }}>Tingkat {{ $lvl }}</option>
                    @endforeach
                </select>
                @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jurusan</label>
                <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                    <option value="">-- Pilih Jurusan (Opsional) --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->code }} - {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Tahun Ajaran</label>
            <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror">
                <option value="">-- Default: Tahun Ajaran Aktif --</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('academic_year_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active_class" checked>
            <label class="form-check-label" for="create_active_class">Status Aktif</label>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Kelas</button>
        </div>
    </form>
</x-modal>
@endsection

