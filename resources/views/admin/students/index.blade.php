@extends('layouts.app')

@section('title', 'Data Siswa - CBT School')
@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Manajemen data peserta didik dan akun login ujian CBT')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Siswa</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.students.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">&#128269;</span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, NIS, NISN, username..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="classroom_id" class="form-select">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} {{ $cls->department ? '(' . $cls->department->code . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="gender" class="form-select">
                    <option value="">Semua Gender</option>
                    <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                @if(request()->hasAny(['search', 'classroom_id', 'gender', 'status']))
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
                <a href="{{ route('admin.students.create') }}" class="btn btn-primary ms-auto">
                    + Tambah Siswa
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Daftar Siswa Peserta CBT">
    <x-table :headers="['#', 'Identitas Siswa', 'Kelas & Jurusan', 'Akun CBT', 'Kontak / Alamat', 'Status', 'Aksi']" :empty="$students->isEmpty()" emptyMessage="Belum ada data siswa yang ditemukan.">
        @foreach($students as $index => $student)
            <tr>
                <td>{{ $students->firstItem() + $index }}</td>
                <td>
                    <div class="fw-bold fs-6">{{ $student->name }}</div>
                    <div class="small text-secondary">
                        <span class="badge text-bg-dark font-monospace">NIS: {{ $student->nis }}</span>
                        @if($student->nisn)
                            <span class="badge text-bg-light border font-monospace ms-1">NISN: {{ $student->nisn }}</span>
                        @endif
                        <span class="ms-1">{{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                </td>
                <td>
                    @if($student->classroom)
                        <span class="badge text-bg-primary fs-7">{{ $student->classroom->name }}</span>
                        @if($student->classroom->department)
                            <small class="text-secondary d-block">{{ $student->classroom->department->name }}</small>
                        @endif
                    @else
                        <span class="badge text-bg-warning">Belum Ada Kelas</span>
                    @endif
                </td>
                <td>
                    @if($student->user)
                        <span class="badge text-bg-secondary font-monospace">{{ $student->user->username }}</span>
                        <div class="small text-secondary">{{ $student->user->email }}</div>
                    @else
                        <span class="text-danger small">Akun tidak ada</span>
                    @endif
                </td>
                <td>
                    <div class="small">
                        @if($student->phone)
                            <div>&#128222; {{ $student->phone }}</div>
                        @endif
                        @if($student->birth_date)
                            <div>&#127874; {{ $student->birth_date->format('d/m/Y') }}</div>
                        @endif
                        @if(! $student->phone && ! $student->birth_date)
                            <span class="text-secondary">-</span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($student->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                    @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.students.raport', $student) }}" target="_blank" class="btn btn-outline-info" title="Cetak Rapor Nilai">
                            &#128424; Rapor
                        </a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-primary" title="Edit">
                            &#9998; Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteStudent{{ $student->id }}" title="Hapus">
                            &#128465; Hapus
                        </button>
                    </div>

                    <!-- Modal Delete Student -->
                    <x-modal id="modalDeleteStudent{{ $student->id }}" title="Konfirmasi Hapus Data Siswa">
                        <div class="text-start">
                            <p>Apakah Anda yakin ingin menghapus data siswa <strong>{{ $student->name }}</strong> (NIS: {{ $student->nis }})?</p>
                            <div class="alert alert-warning small">
                                <strong>Peringatan:</strong> Menghapus data siswa juga akan menghapus akun login CBT (username: <code>{{ $student->user?->username }}</code>).
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}">
                            @csrf
                            @method('DELETE')
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Ya, Hapus Siswa</button>
                            </div>
                        </form>
                    </x-modal>
                </td>
            </tr>
        @endforeach
    </x-table>

    @if($students->hasPages())
        <div class="p-3 border-top">
            {{ $students->links() }}
        </div>
    @endif
</x-card>
@endsection

