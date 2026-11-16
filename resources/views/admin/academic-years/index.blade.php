@extends('layouts.app')

@section('title', 'Tahun Ajaran & Semester - CBT School')
@section('page_title', 'Tahun Ajaran & Semester')
@section('page_subtitle', 'Pengaturan periode akademik aktif untuk pelaksanaan ujian sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tahun Ajaran</li>
@endsection

@section('content')
<!-- Active Period Summary Info -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card text-bg-primary shadow-sm mb-3">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3">&#128197;</div>
                <div>
                    <small class="text-white-50 text-uppercase fw-bold">Tahun Ajaran Aktif Saat Ini</small>
                    <h3 class="mb-0 fw-bold">{{ $activeYear->name ?? 'Belum Ada Periode Aktif' }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-bg-success shadow-sm mb-3">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 me-3">&#9201;</div>
                <div>
                    <small class="text-white-50 text-uppercase fw-bold">Semester Aktif Saat Ini</small>
                    <h3 class="mb-0 fw-bold">{{ $activeSemester ? $activeSemester->name . ' (' . $activeSemester->academicYear->name . ')' : 'Belum Ada Semester Aktif' }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<x-card title="Daftar Tahun Ajaran">
    <x-slot name="tools">
        <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateAcademicYear">
            + Tambah Tahun Ajaran
        </button>
    </x-slot>

    <x-table :headers="['#', 'Tahun Ajaran', 'Rentang Tanggal', 'Semester Terkait', 'Status', 'Aksi']" :empty="$academicYears->isEmpty()" emptyMessage="Belum ada data tahun ajaran. Silakan tambahkan tahun ajaran pertama.">
        @foreach($academicYears as $index => $year)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="fw-bold fs-6">
                    {{ $year->name }}
                    @if($year->is_active)
                        <span class="badge text-bg-primary ms-1">Aktif</span>
                    @endif
                </td>
                <td>
                    @if($year->start_date && $year->end_date)
                        <small class="text-secondary">{{ $year->start_date->format('d M Y') }} s/d {{ $year->end_date->format('d M Y') }}</small>
                    @else
                        <small class="text-muted">-</small>
                    @endif
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        @foreach($year->semesters as $semester)
                            @if($semester->is_active)
                                <span class="badge text-bg-success px-2 py-1">
                                    &#10004; Semester {{ $semester->name }} (Aktif)
                                </span>
                            @else
                                <form action="{{ route('admin.semesters.activate', $semester) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="badge text-bg-light border text-secondary text-decoration-none btn-sm py-1" onclick="return confirm('Aktifkan Semester {{ $semester->name }} ({{ $year->name }})?');">
                                        Semester {{ $semester->name }}
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </td>
                <td>
                    @if($year->is_active)
                        <span class="badge text-bg-success">Periode Aktif</span>
                    @else
                        <form action="{{ route('admin.academic-years.activate', $year) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Jadikan Tahun Ajaran {{ $year->name }} sebagai periode aktif?');">
                                Jadikan Aktif
                            </button>
                        </form>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button 
                            type="button" 
                            class="btn btn-outline-secondary btn-edit-year" 
                            data-id="{{ $year->id }}"
                            data-name="{{ $year->name }}"
                            data-start="{{ $year->start_date?->format('Y-m-d') }}"
                            data-end="{{ $year->end_date?->format('Y-m-d') }}"
                            data-active="{{ $year->is_active ? '1' : '0' }}"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalEditAcademicYear"
                        >
                            Edit
                        </button>
                        @if(! $year->is_active)
                            <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran {{ $year->name }} beserta semesternya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-card>

<!-- Modal Create Academic Year -->
<x-modal id="modalCreateAcademicYear" title="Tambah Tahun Ajaran Baru">
    <form action="{{ route('admin.academic-years.store') }}" method="POST" id="formCreateYear">
        @csrf
        <x-form-input 
            name="name" 
            label="Nama Tahun Ajaran" 
            placeholder="Contoh: 2026/2027" 
            required="true"
            help="Format umum: YYYY/YYYY (misal 2026/2027)"
        />

        <div class="row">
            <div class="col-6">
                <x-form-input 
                    name="start_date" 
                    label="Tanggal Mulai" 
                    type="date" 
                />
            </div>
            <div class="col-6">
                <x-form-input 
                    name="end_date" 
                    label="Tanggal Selesai" 
                    type="date" 
                />
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive">
            <label class="form-check-label fw-semibold" for="createIsActive">
                Jadikan sebagai Tahun Ajaran Aktif
            </label>
            <div class="form-text text-muted">Jika dicentang, tahun ajaran lain otomatis dinonaktifkan.</div>
        </div>

        <div class="alert alert-light border p-2 fs-7 text-secondary mb-0">
            <small><b>Catatan:</b> Semester Ganjil dan Semester Genap akan dibuat secara otomatis.</small>
        </div>

        <div class="modal-footer border-top px-0 pb-0 mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary fw-semibold">Simpan Tahun Ajaran</button>
        </div>
    </form>
</x-modal>

<!-- Modal Edit Academic Year -->
<x-modal id="modalEditAcademicYear" title="Edit Tahun Ajaran">
    <form action="#" method="POST" id="formEditYear">
        @csrf
        @method('PUT')
        
        <x-form-input 
            name="name" 
            id="editYearName"
            label="Nama Tahun Ajaran" 
            required="true"
        />

        <div class="row">
            <div class="col-6">
                <x-form-input 
                    name="start_date" 
                    id="editYearStart"
                    label="Tanggal Mulai" 
                    type="date" 
                />
            </div>
            <div class="col-6">
                <x-form-input 
                    name="end_date" 
                    id="editYearEnd"
                    label="Tanggal Selesai" 
                    type="date" 
                />
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
            <label class="form-check-label fw-semibold" for="editIsActive">
                Jadikan sebagai Tahun Ajaran Aktif
            </label>
        </div>

        <div class="modal-footer border-top px-0 pb-0 mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary fw-semibold">Simpan Perubahan</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.btn-edit-year');
        const formEdit = document.getElementById('formEditYear');
        const editName = document.getElementById('editYearName');
        const editStart = document.getElementById('editYearStart');
        const editEnd = document.getElementById('editYearEnd');
        const editActive = document.getElementById('editIsActive');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const start = this.getAttribute('data-start') || '';
                const end = this.getAttribute('data-end') || '';
                const active = this.getAttribute('data-active') === '1';

                formEdit.action = `/admin/academic-years/${id}`;
                if (editName) editName.value = name;
                if (editStart) editStart.value = start;
                if (editEnd) editEnd.value = end;
                if (editActive) editActive.checked = active;
            });
        });
    });
</script>
@endpush

