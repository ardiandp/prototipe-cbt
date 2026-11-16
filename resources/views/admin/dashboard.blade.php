@extends('layouts.app')

@section('title', 'Dashboard Administrator - CBT School')
@section('page_title', 'Dashboard Administrator')
@section('page_subtitle', 'Pusat kendali operasional master data dan ujian sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<!-- Banner Periode Akademik Aktif -->
<div class="card bg-primary text-white shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="fs-1 me-3">&#127979;</div>
            <div>
                <h4 class="fw-bold mb-1">{{ $schoolProfile->name }}</h4>
                <p class="mb-0 text-white-50">
                    NPSN: <strong>{{ $schoolProfile->npsn ?? '-' }}</strong> &bull;
                    Kepala Sekolah: <strong>{{ $schoolProfile->principal_name ?? '-' }}</strong>
                </p>
            </div>
        </div>
        <div class="mt-3 mt-md-0 text-md-end border-start border-white-50 ps-md-4">
            <div class="small text-white-50 text-uppercase fw-semibold">Periode Akademik Aktif</div>
            <div class="fs-5 fw-bold">
                {{ $activeYear?->name ?? 'Belum Diatur' }}
                @if($activeSemester)
                    <span class="badge text-bg-warning ms-1 text-dark">{{ $activeSemester->name }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards (Real Database Data) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-bg-info text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $totalStudents }}</h2>
                    <p class="mb-0 text-white-50 fw-semibold">Total Siswa Aktif</p>
                </div>
                <div class="fs-1 opacity-75">&#127891;</div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('admin.students.index') }}" class="text-white text-decoration-none small fw-bold">
                    Kelola Siswa &rarr;
                </a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-bg-success text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $totalTeachers }}</h2>
                    <p class="mb-0 text-white-50 fw-semibold">Total Tenaga Pendidik</p>
                </div>
                <div class="fs-1 opacity-75">&#128101;</div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('admin.teachers.index') }}" class="text-white text-decoration-none small fw-bold">
                    Kelola Guru &rarr;
                </a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-bg-warning text-dark shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $totalClasses }}</h2>
                    <p class="mb-0 text-dark-50 fw-semibold">Rombongan Belajar</p>
                </div>
                <div class="fs-1 opacity-75">&#127963;</div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('admin.classes.index') }}" class="text-dark text-decoration-none small fw-bold">
                    Kelola Kelas &rarr;
                </a>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-bg-danger text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $totalSubjects }}</h2>
                    <p class="mb-0 text-white-50 fw-semibold">Mata Pelajaran</p>
                </div>
                <div class="fs-1 opacity-75">&#128218;</div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('admin.subjects.index') }}" class="text-white text-decoration-none small fw-bold">
                    Kelola Mapel &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Kolom Kiri: Rekapitulasi Rombel & Siswa Terbaru -->
    <div class="col-lg-7">
        <!-- Rekap Jurusan -->
        <x-card title="Rekapitulasi Jurusan & Rombongan Belajar">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Jurusan</th>
                            <th class="text-center">Jumlah Rombel</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                            <tr>
                                <td><span class="badge text-bg-dark font-monospace">{{ $dept->code }}</span></td>
                                <td class="fw-semibold">{{ $dept->name }}</td>
                                <td class="text-center">
                                    <span class="badge text-bg-primary fs-7">{{ $dept->classrooms_count }} Kelas</span>
                                </td>
                                <td>
                                    @if($dept->is_active)
                                        <span class="badge text-bg-success">Aktif</span>
                                    @else
                                        <span class="badge text-bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Belum ada data jurusan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Siswa Terbaru Terdaftar -->
        <x-card title="Peserta Didik Terbaru Terdaftar" class="mt-3">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Akun CBT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $st)
                            <tr>
                                <td><span class="badge text-bg-light border font-monospace">{{ $st->nis }}</span></td>
                                <td class="fw-semibold">{{ $st->name }}</td>
                                <td>{{ $st->classroom?->name ?? '-' }}</td>
                                <td><code>{{ $st->user?->username }}</code></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-2">Belum ada siswa terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Kolom Kanan: Pintasan Master Data & Tenaga Pendidik -->
    <div class="col-lg-5">
        <!-- Menu Pintasan Cepat -->
        <x-card title="Pintasan Master Data">
            <div class="d-grid gap-2">
                <a href="{{ route('admin.school.profile') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#127979; Profil Sekolah</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.academic-years.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#128197; Tahun Ajaran & Semester</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#127891; Kelola Jurusan</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#127963; Kelola Rombel & Kelas</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#128218; Mata Pelajaran</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#128101; Tenaga Pendidik (Guru)</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary text-start d-flex justify-content-between align-items-center">
                    <span>&#127891; Peserta Didik (Siswa)</span>
                    <span class="badge text-bg-primary">&rarr;</span>
                </a>
            </div>
        </x-card>

        <!-- Guru Terbaru Terdaftar -->
        <x-card title="Tenaga Pendidik" class="mt-3">
            <ul class="list-group list-group-flush">
                @forelse($recentTeachers as $tc)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">{{ $tc->name }}</div>
                            <small class="text-secondary">NIP: {{ $tc->nip ?? '-' }}</small>
                        </div>
                        <div>
                            @foreach($tc->subjects as $sb)
                                <span class="badge text-bg-info text-white">{{ $sb->code }}</span>
                            @endforeach
                        </div>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-center text-secondary">Belum ada data guru.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>
@endsection

