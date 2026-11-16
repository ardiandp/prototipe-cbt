@extends('layouts.app')

@section('title', 'Data Mata Pelajaran - CBT School')
@section('page_title', 'Data Mata Pelajaran')
@section('page_subtitle', 'Manajemen mata pelajaran kurikulum sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Mata Pelajaran</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.subjects.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama mapel..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select">
                    <option value="">Semua Tingkat</option>
                    <option value="Semua" {{ request('level') === 'Semua' ? 'selected' : '' }}>Semua Tingkat (Umum)</option>
                    @foreach(['10', '11', '12'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>Tingkat {{ $lvl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="group" class="form-select">
                    <option value="">Semua Kelompok</option>
                    @foreach($groups as $grp)
                        <option value="{{ $grp }}" {{ request('group') === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'level', 'group', 'status']))
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreateSubject">
                    + Tambah Mapel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Daftar Mata Pelajaran">
    <x-table :headers="['#', 'Kode', 'Nama Mata Pelajaran', 'Tingkat', 'Kelompok / Kategori', 'Status', 'Aksi']" :empty="$subjects->isEmpty()" emptyMessage="Belum ada data mata pelajaran yang ditemukan.">
        @foreach($subjects as $index => $subject)
            <tr>
                <td>{{ $subjects->firstItem() + $index }}</td>
                <td><span class="badge text-bg-dark font-monospace">{{ $subject->code }}</span></td>
                <td class="fw-bold fs-6">{{ $subject->name }}</td>
                <td>
                    <span class="badge text-bg-light border">{{ $subject->level ?? 'Semua' }}</span>
                </td>
                <td>
                    @if($subject->group)
                        <span class="badge text-bg-info text-white">{{ $subject->group }}</span>
                    @else
                        <span class="text-secondary">-</span>
                    @endif
                </td>
                <td>
                    @if($subject->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditSubject{{ $subject->id }}" title="Edit">
                            &#9998; Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteSubject{{ $subject->id }}" title="Hapus">
                            &#128465; Hapus
                        </button>
                    </div>

                    <!-- Modal Edit Subject -->
                    <x-modal id="modalEditSubject{{ $subject->id }}" title="Edit Mata Pelajaran: {{ $subject->name }}">
                        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Mapel <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $subject->code) }}" required maxlength="50">
                                <small class="text-secondary">Contoh: MTK, BIN, BIG, PW</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required maxlength="150">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tingkat</label>
                                    <select name="level" class="form-select">
                                        <option value="">Semua Tingkat</option>
                                        @foreach(['Semua', '10', '11', '12'] as $lvl)
                                            <option value="{{ $lvl }}" {{ old('level', $subject->level) === $lvl ? 'selected' : '' }}>
                                                {{ $lvl === 'Semua' ? 'Semua Tingkat (Umum)' : 'Tingkat ' . $lvl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kelompok / Kategori</label>
                                    <input type="text" name="group" class="form-control" value="{{ old('group', $subject->group) }}" maxlength="100" placeholder="Contoh: Muatan Nasional">
                                </div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_subj_{{ $subject->id }}" {{ $subject->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit_active_subj_{{ $subject->id }}">Status Aktif</label>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </x-modal>

                    <!-- Modal Delete Subject -->
                    <x-modal id="modalDeleteSubject{{ $subject->id }}" title="Konfirmasi Hapus Mata Pelajaran">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus mata pelajaran <strong>{{ $subject->name }}</strong> ({{ $subject->code }})?</p>
                        </div>
                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}">
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

    @if($subjects->hasPages())
        <div class="p-3 border-top">
            {{ $subjects->links() }}
        </div>
    @endif
</x-card>

<!-- Modal Create Subject -->
<x-modal id="modalCreateSubject" title="Tambah Mata Pelajaran Baru">
    <form method="POST" action="{{ route('admin.subjects.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Kode Mapel <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required maxlength="50" placeholder="Contoh: MTK">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="150" placeholder="Contoh: Matematika">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tingkat</label>
                <select name="level" class="form-select @error('level') is-invalid @enderror">
                    <option value="">Semua Tingkat</option>
                    @foreach(['Semua', '10', '11', '12'] as $lvl)
                        <option value="{{ $lvl }}" {{ old('level') === $lvl ? 'selected' : '' }}>
                            {{ $lvl === 'Semua' ? 'Semua Tingkat (Umum)' : 'Tingkat ' . $lvl }}
                        </option>
                    @endforeach
                </select>
                @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Kelompok / Kategori</label>
                <input type="text" name="group" class="form-control @error('group') is-invalid @enderror" value="{{ old('group') }}" maxlength="100" placeholder="Contoh: Muatan Nasional">
                @error('group')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active_subj" checked>
            <label class="form-check-label" for="create_active_subj">Status Aktif</label>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Mata Pelajaran</button>
        </div>
    </form>
</x-modal>
@endsection

