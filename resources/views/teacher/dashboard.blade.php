@extends('layouts.app')

@section('title', 'Dashboard Guru - CBT School')
@section('page_title', 'Dashboard Guru Pengampu')
@section('page_subtitle', 'Selamat datang di panel guru CBT School')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard Guru</li>
@endsection

@section('content')
<!-- Banner Profil Guru & Periode Aktif -->
<div class="card bg-success text-white shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="fs-1 me-3">&#128101;</div>
            <div>
                <h4 class="fw-bold mb-1">{{ $teacher?->name ?? $user->name }}</h4>
                <p class="mb-0 text-white-50">
                    NIP: <strong>{{ $teacher?->nip ?? '-' }}</strong> &bull;
                    Akun: <code>{{ $user->username }}</code> ({{ $user->email }})
                </p>
            </div>
        </div>
        <div class="mt-3 mt-md-0 text-md-end border-start border-white-50 ps-md-4">
            <div class="small text-white-50 text-uppercase fw-semibold">Tahun Ajaran & Semester Aktif</div>
            <div class="fs-5 fw-bold">
                {{ $activeYear?->name ?? 'Belum Diatur' }}
                @if($activeSemester)
                    <span class="badge text-bg-warning ms-1 text-dark">{{ $activeSemester->name }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card text-bg-primary text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $subjects->count() }}</h2>
                    <p class="mb-0 text-white-50 fw-semibold">Mata Pelajaran Diampu</p>
                </div>
                <div class="fs-1 opacity-75">&#128218;</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-bg-info text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-0">{{ $classrooms->count() }}</h2>
                    <p class="mb-0 text-white-50 fw-semibold">Kelas / Rombel Diajar</p>
                </div>
                <div class="fs-1 opacity-75">&#127963;</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Kolom Kiri: Mata Pelajaran yang Diampu -->
    <div class="col-md-6">
        <x-card title="Mata Pelajaran yang Diampu">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mapel</th>
                            <th>Tingkat</th>
                            <th>Kelompok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $sb)
                            <tr>
                                <td><span class="badge text-bg-dark font-monospace">{{ $sb->code }}</span></td>
                                <td class="fw-semibold">{{ $sb->name }}</td>
                                <td><span class="badge text-bg-light border">{{ $sb->level ?? 'Semua' }}</span></td>
                                <td><span class="text-secondary small">{{ $sb->group ?? '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Belum ada penugasan mata pelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <!-- Kolom Kanan: Kelas yang Diajar -->
    <div class="col-md-6">
        <x-card title="Rombongan Belajar (Kelas) yang Diajar">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $cls)
                            <tr>
                                <td class="fw-bold">{{ $cls->name }}</td>
                                <td><span class="badge text-bg-light border">Kelas {{ $cls->level }}</span></td>
                                <td>
                                    @if($cls->department)
                                        <span class="badge text-bg-info text-white">{{ $cls->department->code }}</span>
                                    @else
                                        <span class="text-secondary">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-3">Belum ada penugasan kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>
@endsection

