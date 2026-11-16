@extends('layouts.app')

@section('title', 'Data Jurusan - CBT School')
@section('page_title', 'Data Jurusan')
@section('page_subtitle', 'Manajemen jurusan / kompetensi keahlian sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Jurusan</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama jurusan..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCreateDepartment">
                    + Tambah Jurusan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Main Data Table Card -->
<x-card title="Daftar Jurusan">
    <x-table :headers="['#', 'Kode', 'Nama Jurusan', 'Deskripsi', 'Jumlah Kelas', 'Status', 'Aksi']" :empty="$departments->isEmpty()" emptyMessage="Belum ada data jurusan yang ditemukan.">
        @foreach($departments as $index => $department)
            <tr>
                <td>{{ $departments->firstItem() + $index }}</td>
                <td><span class="badge text-bg-dark font-monospace">{{ $department->code }}</span></td>
                <td class="fw-bold">{{ $department->name }}</td>
                <td>
                    <span class="text-secondary small">{{ $department->description ? Str::limit($department->description, 50) : '-' }}</span>
                </td>
                <td>
                    <span class="badge text-bg-info text-white">{{ $department->classrooms_count }} Kelas</span>
                </td>
                <td>
                    @if($department->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditDepartment{{ $department->id }}" title="Edit">
                            &#9998; Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteDepartment{{ $department->id }}" title="Hapus">
                            &#128465; Hapus
                        </button>
                    </div>

                    <!-- Modal Edit Department -->
                    <x-modal id="modalEditDepartment{{ $department->id }}" title="Edit Jurusan: {{ $department->name }}">
                        <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" value="{{ old('code', $department->code) }}" required maxlength="50">
                                <small class="text-secondary">Contoh: RPL, TKJ, IPA, IPS</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}" required maxlength="150">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $department->description) }}</textarea>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_{{ $department->id }}" {{ $department->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit_active_{{ $department->id }}">Status Aktif</label>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </x-modal>

                    <!-- Modal Delete Department -->
                    <x-modal id="modalDeleteDepartment{{ $department->id }}" title="Konfirmasi Hapus Jurusan">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus jurusan <strong>{{ $department->name }}</strong> ({{ $department->code }})?</p>
                            @if($department->classrooms_count > 0)
                                <div class="alert alert-warning small">
                                    <strong>Peringatan:</strong> Jurusan ini terhubung dengan {{ $department->classrooms_count }} kelas. Jurusan tidak dapat dihapus jika masih memiliki kelas aktif.
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.departments.destroy', $department) }}">
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

    @if($departments->hasPages())
        <div class="p-3 border-top">
            {{ $departments->links() }}
        </div>
    @endif
</x-card>

<!-- Modal Create Department -->
<x-modal id="modalCreateDepartment" title="Tambah Jurusan Baru">
    <form method="POST" action="{{ route('admin.departments.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Kode Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required maxlength="50" placeholder="Contoh: RPL">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Nama Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="150" placeholder="Contoh: Rekayasa Perangkat Lunak">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat kompetensi/jurusan">{{ old('description') }}</textarea>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active" checked>
            <label class="form-check-label" for="create_active">Status Aktif</label>
        </div>

        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Jurusan</button>
        </div>
    </form>
</x-modal>
@endsection
