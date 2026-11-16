@extends('layouts.app')

@section('title', 'Data Guru - CBT School')
@section('page_title', 'Data Guru')
@section('page_subtitle', 'Manajemen pendidik, akun pengguna, dan penugasan mengajar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Guru</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, NIP, username, atau email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="subject_id" class="form-select">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->code }} - {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="classroom_id" class="form-select">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'subject_id', 'classroom_id', 'status']))
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Daftar Tenaga Pendidik (Guru)">
    <x-slot name="tools">
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-sm btn-primary shadow-sm">
            + Tambah Guru Baru
        </a>
    </x-slot>

    <x-table :headers="['#', 'Identitas Guru', 'Akun Pengguna', 'Mata Pelajaran', 'Kelas Diampu', 'Status', 'Aksi']" :empty="$teachers->isEmpty()" emptyMessage="Belum ada data guru yang ditemukan.">
        @foreach($teachers as $index => $teacher)
            <tr>
                <td>{{ $teachers->firstItem() + $index }}</td>
                <td>
                    <div class="fw-bold fs-6">{{ $teacher->name }}</div>
                    <div class="small text-secondary">
                        @if($teacher->nip)
                            <span>NIP: {{ $teacher->nip }}</span> &bull;
                        @endif
                        <span>{{ $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-') }}</span>
                        @if($teacher->phone)
                            &bull; <span>{{ $teacher->phone }}</span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($teacher->user)
                        <span class="badge text-bg-dark font-monospace">{{ $teacher->user->username }}</span>
                        <div class="small text-secondary">{{ $teacher->user->email }}</div>
                    @else
                        <span class="text-danger small">Akun tidak ditemukan</span>
                    @endif
                </td>
                <td>
                    @forelse($teacher->subjects as $subj)
                        <span class="badge text-bg-info text-white mb-1" title="{{ $subj->name }}">{{ $subj->code }}</span>
                    @empty
                        <span class="text-secondary small">Belum ada penugasan</span>
                    @endforelse
                </td>
                <td>
                    @forelse($teacher->classrooms as $cls)
                        <span class="badge text-bg-light border mb-1">{{ $cls->name }}</span>
                    @empty
                        <span class="text-secondary small">Belum ada kelas</span>
                    @endforelse
                </td>
                <td>
                    @if($teacher->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-outline-primary" title="Edit">
                            &#9998; Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteTeacher{{ $teacher->id }}" title="Hapus">
                            &#128465; Hapus
                        </button>
                    </div>

                    <!-- Modal Delete Teacher -->
                    <x-modal id="modalDeleteTeacher{{ $teacher->id }}" title="Konfirmasi Hapus Data Guru">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus data guru <strong>{{ $teacher->name }}</strong>?</p>
                            <div class="alert alert-warning small">
                                <strong>Peringatan:</strong> Menghapus data guru juga akan menghapus akun login (username: <code>{{ $teacher->user?->username }}</code>) dan seluruh penugasan mengajar terkait.
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}">
                            @csrf
                            @method('DELETE')
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus Guru</button>
                            </div>
                        </form>
                    </x-modal>
                </td>
            </tr>
        @endforeach
    </x-table>

    @if($teachers->hasPages())
        <div class="p-3 border-top">
            {{ $teachers->links() }}
        </div>
    @endif
</x-card>
@endsection

