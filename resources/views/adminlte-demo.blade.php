@extends('layouts.app')

@section('title', 'AdminLTE 3 Integration Demo - CBT School')
@section('page_title', 'AdminLTE 3 Integration Demo')
@section('page_subtitle', 'Verifikasi kesiapan asset tema lokal dan komponen reusable Blade')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Theme Demo</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <!-- Small Box Prototype 1 -->
        <div class="card text-bg-primary shadow-sm mb-3">
            <div class="card-body">
                <h3 class="fw-bold mb-1">1,250</h3>
                <p class="mb-0">Total Siswa</p>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fs-7">Data Siswa Aktif</span>
                <span>&#128101;</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- Small Box Prototype 2 -->
        <div class="card text-bg-success shadow-sm mb-3">
            <div class="card-body">
                <h3 class="fw-bold mb-1">84</h3>
                <p class="mb-0">Bank Soal</p>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fs-7">Pilihan Ganda A–E</span>
                <span>&#128221;</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- Small Box Prototype 3 -->
        <div class="card text-bg-warning shadow-sm mb-3">
            <div class="card-body">
                <h3 class="fw-bold mb-1">12</h3>
                <p class="mb-0">Jadwal Ujian Aktif</p>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fs-7">Mode Scheduled</span>
                <span>&#9201;</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <!-- Small Box Prototype 4 -->
        <div class="card text-bg-danger shadow-sm mb-3">
            <div class="card-body">
                <h3 class="fw-bold mb-1">45</h3>
                <p class="mb-0">Peserta Sedang Ujian</p>
            </div>
            <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center">
                <span class="fs-7">Realtime Attempt</span>
                <span>&#128065;</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Card & Table -->
    <div class="col-lg-8">
        <x-card title="Daftar Jadwal Ujian Aktif (Reusable Table Demo)">
            <x-slot name="tools">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal">
                    + Tambah Ujian (Modal Demo)
                </button>
            </x-slot>

            <x-table :headers="['#', 'Nama Ujian', 'Mata Pelajaran', 'Durasi', 'Status', 'Aksi']">
                <tr>
                    <td>1</td>
                    <td class="fw-semibold">Penilaian Tengah Semester (PTS) Ganjil</td>
                    <td>Matematika Wajib</td>
                    <td>90 Menit</td>
                    <td><span class="badge text-bg-success">Aktif</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-info">Detail</button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td class="fw-semibold">Simulasi Ujian Sekolah</td>
                    <td>Bahasa Indonesia</td>
                    <td>60 Menit</td>
                    <td><span class="badge text-bg-secondary">Mendatang</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-info">Detail</button>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td class="fw-semibold">Kuis Harian Bab 1</td>
                    <td>Biologi</td>
                    <td>45 Menit</td>
                    <td><span class="badge text-bg-warning">Selesai</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Rekap</button>
                    </td>
                </tr>
            </x-table>
        </x-card>

        <!-- Empty State Demo -->
        <x-card title="Empty State Table Demo" class="mt-4">
            <x-table :headers="['#', 'Nama Bank Soal', 'Guru', 'Jumlah Soal', 'Aksi']" :empty="true" emptyMessage="Belum ada bank soal baru yang ditambahkan." />
        </x-card>
    </div>

    <!-- Kolom Kanan: Form Component & Modal Trigger -->
    <div class="col-lg-4">
        <x-card title="Form Input Component Demo" type="primary" :outline="true">
            <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Demo form submitted.');">
                <x-form-input 
                    name="exam_name" 
                    label="Nama Ujian" 
                    placeholder="Contoh: Ujian Akhir Semester" 
                    required="true"
                    help="Gunakan nama yang jelas untuk siswa"
                />

                <x-form-input 
                    name="duration" 
                    label="Durasi (Menit)" 
                    type="number" 
                    placeholder="Contoh: 90" 
                    required="true"
                />

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    Simpan Simulasi
                </button>
            </form>
        </x-card>

        <!-- Alert Notification Demo -->
        <x-card title="Informasi Server CBT" class="mt-4">
            <div class="alert alert-info mb-0">
                <small class="d-block fw-bold mb-1">&#8505; Lingkungan Server LAN</small>
                <small>Asset AdminLTE dimuat langsung secara lokal dari <code>public/theme/adminLTE/dist</code> tanpa memerlukan koneksi internet.</small>
            </div>
        </x-card>
    </div>
</div>

<!-- Modal Component Demo -->
<x-modal id="demoModal" title="Tambah Jadwal Ujian Baru (Demo)">
    <p>Ini adalah modal dialog reusable berbasis komponen Blade AdminLTE 3.</p>
    <x-form-input 
        name="modal_exam_title" 
        label="Judul Ujian" 
        placeholder="Masukkan judul ujian" 
    />
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="alert('Ujian berhasil disimpan!');">Simpan</button>
    </x-slot>
</x-modal>
@endsection
