@extends('layouts.app')

@section('title', 'Tambah Peserta - ' . $exam->title . ' - CBT School')
@section('page_title', 'Penugasan Peserta Ujian')
@section('page_subtitle', $exam->title . ' (' . $exam->code . ')')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.index') }}">Jadwal Ujian</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.show', $exam) }}">{{ $exam->code }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('teacher.exams.participants.index', $exam) }}">Peserta</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="p-3 border rounded bg-white shadow-xs d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge text-bg-{{ $exam->computed_status_badge_class }} me-2 fs-7">
                    {{ $exam->computed_status_label }}
                </span>
                <span class="fw-bold fs-6 me-3">{{ $exam->title }}</span>
                <span class="text-secondary small">Mapel: <strong>{{ $exam->subject?->name }}</strong></span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light border px-3 py-2 fs-7">
                    Peserta Terdaftar Saat Ini: <strong class="text-primary">{{ count($alreadyAssignedIds) }}</strong>
                </span>
                <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Kembali ke Daftar Peserta
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs Mode Penugasan -->
<div class="card card-primary card-outline card-outline-tabs shadow-sm mb-4">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="assignTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ !request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'active fw-bold' : '' }}" id="classroom-tab" data-bs-toggle="tab" data-bs-target="#classroom-pane" type="button" role="tab" aria-controls="classroom-pane" aria-selected="{{ !request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'true' : 'false' }}">
                    &#128101; 1. Berdasarkan Rombongan Belajar (Kelas)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'active fw-bold' : '' }}" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual-pane" type="button" role="tab" aria-controls="individual-pane" aria-selected="{{ request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'true' : 'false' }}">
                    &#128100; 2. Pilih Peserta Individual
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="assignTabContent">
            <!-- PANE 1: Penugasan Berdasarkan Rombel/Kelas -->
            <div class="tab-pane fade {{ !request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'show active' : '' }}" id="classroom-pane" role="tabpanel" aria-labelledby="classroom-tab" tabindex="0">
                <div class="alert alert-light border d-flex align-items-center mb-3">
                    <span class="fs-4 me-3">&#128712;</span>
                    <div class="small">
                        <strong>Metode Kolektif:</strong> Pilih satu atau lebih rombongan belajar (kelas). Seluruh peserta didik aktif di dalam kelas tersebut akan otomatis didaftarkan ke jadwal ujian ini. Peserta yang sudah terdaftar sebelumnya tidak akan diduplikasi.
                    </div>
                </div>

                @if($allClassrooms->isEmpty())
                    <div class="alert alert-warning">
                        Belum ada data rombongan belajar (kelas) yang aktif di sistem sekolah.
                    </div>
                @else
                    <form method="POST" action="{{ route('teacher.exams.participants.store', $exam) }}">
                        @csrf
                        <input type="hidden" name="assign_mode" value="classroom">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllClassrooms">
                                <label class="form-check-label fw-bold" for="selectAllClassrooms">
                                    Pilih Semua Kelas
                                </label>
                            </div>
                            <span class="small text-secondary">Tersedia {{ $allClassrooms->count() }} rombel aktif</span>
                        </div>

                        <div class="row g-3 mb-4">
                            @foreach($allClassrooms as $c)
                                <div class="col-md-4 col-sm-6">
                                    <div class="border rounded p-3 h-100 bg-white classroom-card">
                                        <div class="form-check">
                                            <input class="form-check-input classroom-checkbox" type="checkbox" name="classroom_ids[]" value="{{ $c->id }}" id="classroom_{{ $c->id }}">
                                            <label class="form-check-label w-100" for="classroom_{{ $c->id }}">
                                                <div class="fw-bold text-dark fs-6">{{ $c->name }}</div>
                                                <div class="small text-secondary">Tingkat: Kelas {{ $c->level }}</div>
                                                <div class="badge text-bg-light border text-primary mt-1">
                                                    {{ $c->students_count }} Siswa Aktif
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('classroom_ids')
                            <div class="alert alert-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                &#43; Tambahkan Siswa dari Kelas Terpilih
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- PANE 2: Penugasan Individual -->
            <div class="tab-pane fade {{ request()->hasAny(['student_search', 'student_classroom_id', 'page']) ? 'show active' : '' }}" id="individual-pane" role="tabpanel" aria-labelledby="individual-tab" tabindex="0">
                <div class="alert alert-light border d-flex align-items-center mb-3">
                    <span class="fs-4 me-3">&#128712;</span>
                    <div class="small">
                        <strong>Metode Individual:</strong> Pilih siswa tertentu secara spesifik. Siswa yang telah terdaftar pada jadwal ujian ini otomatis disembunyikan dari daftar berikut.
                    </div>
                </div>

                <!-- Filter Form untuk Individual -->
                <form method="GET" action="{{ route('teacher.exams.participants.create', $exam) }}" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light">&#128269;</span>
                            <input type="text" name="student_search" class="form-control" placeholder="Cari nama atau NIS siswa..." value="{{ request('student_search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="student_classroom_id" class="form-select">
                            <option value="">-- Filter Berdasarkan Kelas --</option>
                            @foreach($allClassrooms as $c)
                                <option value="{{ $c->id }}" {{ request('student_classroom_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">Cari</button>
                        @if(request()->hasAny(['student_search', 'student_classroom_id']))
                            <a href="{{ route('teacher.exams.participants.create', $exam) }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>

                @if($availableStudents->isEmpty())
                    <div class="text-center py-5 text-secondary">
                        <div class="fs-1 mb-2">&#10003;</div>
                        <h6 class="fw-bold">Tidak ada peserta didik yang dapat dipilih</h6>
                        <p class="small mb-0">Seluruh siswa yang cocok mungkin sudah terdaftar pada ujian ini, atau tidak ada data yang memenuhi kriteria pencarian.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('teacher.exams.participants.store', $exam) }}">
                        @csrf
                        <input type="hidden" name="assign_mode" value="individual">

                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input class="form-check-input" type="checkbox" id="selectAllStudents">
                                        </th>
                                        <th style="width: 140px;">NIS</th>
                                        <th>Nama Siswa</th>
                                        <th style="width: 160px;">Rombel / Kelas</th>
                                        <th style="width: 120px;">Jenis Kelamin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availableStudents as $student)
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" value="{{ $student->id }}" id="student_{{ $student->id }}">
                                            </td>
                                            <td>
                                                <label class="form-check-label font-monospace fw-bold" for="student_{{ $student->id }}">
                                                    {{ $student->nis ?? '-' }}
                                                </label>
                                            </td>
                                            <td>
                                                <label class="form-check-label fw-semibold" for="student_{{ $student->id }}">
                                                    {{ $student->name }}
                                                </label>
                                                <div class="small text-secondary font-monospace">{{ $student->user?->username ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-light border text-dark">
                                                    {{ $student->classroom?->name ?? 'Tanpa Kelas' }}
                                                </span>
                                            </td>
                                            <td class="small">
                                                {{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @error('student_ids')
                            <div class="alert alert-danger small mt-3 mb-0">{{ $message }}</div>
                        @enderror

                        <div class="mt-3">
                            {{ $availableStudents->links() }}
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-3">
                            <a href="{{ route('teacher.exams.participants.index', $exam) }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                &#43; Tambahkan Siswa Tercentang
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select All Classrooms toggle
    const selectAllClassrooms = document.getElementById('selectAllClassrooms');
    if (selectAllClassrooms) {
        selectAllClassrooms.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.classroom-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAllClassrooms.checked);
        });
    }

    // Select All Students toggle
    const selectAllStudents = document.getElementById('selectAllStudents');
    if (selectAllStudents) {
        selectAllStudents.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAllStudents.checked);
        });
    }
});
</script>
@endsection
