@extends('layouts.app')

@section('title', 'Dashboard Siswa - CBT School')
@section('page_title', 'Dashboard Siswa')
@section('page_subtitle', 'Selamat datang di portal Computer Based Test sekolah')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Dashboard Siswa</li>
@endsection

@section('content')
<!-- Banner Profil Siswa -->
<div class="card bg-info text-white shadow-sm mb-4">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="fs-1 me-3">&#127891;</div>
            <div>
                <h4 class="fw-bold mb-1">{{ $student?->name ?? $user->name }}</h4>
                <p class="mb-0 text-white-50">
                    NIS: <strong>{{ $student?->nis ?? '-' }}</strong> &bull;
                    Kelas: <strong>{{ $student?->classroom?->name ?? 'Belum Ditentukan' }}</strong>
                    @if($student?->classroom?->department)
                        ({{ $student->classroom->department->name }})
                    @endif
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

<div class="row g-3">
    <!-- Kolom Kiri: Rincian Biodata Peserta -->
    <div class="col-md-5">
        <x-card title="Informasi Peserta Didik">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">Nomor Induk Siswa (NIS)</span>
                    <strong class="font-monospace">{{ $student?->nis ?? '-' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">NISN</span>
                    <strong class="font-monospace">{{ $student?->nisn ?? '-' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">Kelas / Rombel</span>
                    <strong class="badge text-bg-primary fs-7">{{ $student?->classroom?->name ?? '-' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">Kompetensi Keahlian / Jurusan</span>
                    <strong>{{ $student?->classroom?->department?->name ?? '-' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">Jenis Kelamin</span>
                    <span>{{ $student?->gender === 'L' ? 'Laki-laki' : ($student?->gender === 'P' ? 'Perempuan' : '-') }}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-secondary">Username CBT</span>
                    <code>{{ $user->username }}</code>
                </li>
            </ul>
        </x-card>
    </div>

    <!-- Kolom Kanan: Panduan Pelaksanaan Ujian -->
    <div class="col-md-7">
        <x-card title="Petunjuk & Tata Tertib Ujian CBT">
            <div class="alert alert-info d-flex align-items-center mb-3">
                <span class="fs-2 me-3">&#9888;</span>
                <div>
                    <strong>Penting:</strong> Ujian menggunakan waktu server sekolah sebagai sumber kebenaran (server time).
                </div>
            </div>

            <ol class="ps-3 mb-0 text-secondary">
                <li class="mb-2">Pastikan komputer atau laptop di laboratorium sekolah terhubung ke jaringan LAN lokal.</li>
                <li class="mb-2">Gunakan username (NIS) dan password yang telah diberikan oleh pihak sekolah.</li>
                <li class="mb-2">Pilih ujian yang sesuai dengan jadwal yang telah ditentukan oleh guru pengampu.</li>
                <li class="mb-2">Jawaban akan disimpan otomatis secara berkala (autosave) ke server CBT.</li>
                <li class="mb-2">Setelah selesai, klik tombol <strong>Selesai Ujian</strong> untuk mengirimkan seluruh lembar jawaban.</li>
            </ol>
        </x-card>
    </div>
</div>
@endsection

