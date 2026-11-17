@extends('layouts.app')

@section('title', 'AdminLTE 3 Integration Demo - CBT School')
@section('page_title', 'AdminLTE 3 Integration Demo')
@section('page_subtitle', 'Verifikasi kesiapan asset tema lokal, komponen Blade reusable, dan layout responsif CBT School')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Theme Demo</li>
@endsection

@section('content')
<!-- Hero / Architecture Callout -->
<div class="callout callout-primary bg-white shadow-sm mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-primary">Computer Based Test (CBT School) — Local AdminLTE Engine</h5>
            <p class="mb-0 text-secondary fs-7">
                Seluruh asset tema, stylesheet, script, dan visual dimuat langsung secara offline dari direktori <code>public/theme/adminLTE/dist</code> tanpa ketergantungan CDN eksternal. Siap digunakan untuk jaringan LAN sekolah.
            </p>
        </div>
        <div>
            <span class="badge text-bg-success px-3 py-2 fw-semibold">
                100% Offline Ready
            </span>
        </div>
    </div>
</div>

<!-- AdminLTE Native Small Boxes Row -->
<div class="row g-3 mb-4">
    <!-- Small Box 1 -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="small-box text-bg-primary shadow-sm mb-0">
            <div class="inner">
                <h3>1,250</h3>
                <p>Total Siswa Terdaftar</p>
            </div>
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <a href="{{ route('admin.students.index') }}" class="small-box-footer link-light">
                <span>Kelola Data Siswa</span>
                <svg class="ms-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>

    <!-- Small Box 2 -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="small-box text-bg-success shadow-sm mb-0">
            <div class="inner">
                <h3>84</h3>
                <p>Bank Soal Siap Uji</p>
            </div>
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>
            <a href="{{ route('teacher.question-banks.index') }}" class="small-box-footer link-light">
                <span>Lihat Bank Soal</span>
                <svg class="ms-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>

    <!-- Small Box 3 -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="small-box text-bg-warning shadow-sm mb-0">
            <div class="inner">
                <h3>12</h3>
                <p>Jadwal Ujian Aktif</p>
            </div>
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
            </svg>
            <a href="{{ route('teacher.exams.index') }}" class="small-box-footer link-dark">
                <span>Pantau Jadwal Ujian</span>
                <svg class="ms-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>

    <!-- Small Box 4 -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="small-box text-bg-danger shadow-sm mb-0">
            <div class="inner">
                <h3>45</h3>
                <p>Peserta Sedang Ujian</p>
            </div>
            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
            </svg>
            <a href="{{ route('admin.monitoring.index') }}" class="small-box-footer link-light">
                <span>Monitoring Realtime</span>
                <svg class="ms-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</div>

<!-- Info Boxes Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon text-bg-info text-white shadow-sm">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Status Jaringan</span>
                <span class="info-box-number text-success fs-6">LAN Server (Stabil)</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon text-bg-primary text-white shadow-sm">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Penyimpanan Basis Data</span>
                <span class="info-box-number fs-6">SQLite Prototyping</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon text-bg-warning text-dark shadow-sm">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Keamanan Token</span>
                <span class="info-box-number fs-6">Kunci Sesi Terkunci</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon text-bg-success text-white shadow-sm">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Waktu Server (Truth)</span>
                <span class="info-box-number fs-6">{{ now()->format('H:i') }} WIB</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Demo Content Grid -->
<div class="row g-4">
    <!-- Left Column: Table & Tabs -->
    <div class="col-lg-8">
        <!-- Reusable Table Card -->
        <x-card title="Jadwal Ujian Aktif & Monitoring Peserta (Reusable Table Demo)" class="shadow-sm">
            <x-slot name="tools">
                <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#demoModal">
                    <span class="me-1">+</span> Tambah Ujian (Modal Demo)
                </button>
            </x-slot>

            <x-table :headers="['#', 'Nama Ujian', 'Mata Pelajaran', 'Durasi', 'Progress Peserta', 'Status', 'Aksi']">
                <tr>
                    <td>1</td>
                    <td>
                        <span class="fw-semibold text-primary d-block">Penilaian Tengah Semester (PTS) Ganjil</span>
                        <small class="text-secondary">Kelas X & XI Semua Jurusan</small>
                    </td>
                    <td><span class="badge text-bg-light border text-dark">Matematika Wajib</span></td>
                    <td>90 Menit</td>
                    <td style="min-width: 140px;">
                        <div class="d-flex justify-content-between fs-8 mb-1">
                            <span>85% Selesai</span>
                            <span class="text-muted">34/40</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                        </div>
                    </td>
                    <td><span class="badge text-bg-success">Aktif</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary">Pantau</button>
                            <button type="button" class="btn btn-outline-secondary">Rincian</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>
                        <span class="fw-semibold text-primary d-block">Simulasi Ujian Sekolah Berbasis Komputer</span>
                        <small class="text-secondary">Kelas XII IPA & IPS</small>
                    </td>
                    <td><span class="badge text-bg-light border text-dark">Bahasa Indonesia</span></td>
                    <td>60 Menit</td>
                    <td style="min-width: 140px;">
                        <div class="d-flex justify-content-between fs-8 mb-1">
                            <span>40% Selesai</span>
                            <span class="text-muted">18/45</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 40%"></div>
                        </div>
                    </td>
                    <td><span class="badge text-bg-info text-white">Berlangsung</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary">Pantau</button>
                            <button type="button" class="btn btn-outline-secondary">Rincian</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>
                        <span class="fw-semibold text-primary d-block">Kuis Harian Bab 1 Genetika</span>
                        <small class="text-secondary">Kelas XII IPA 1</small>
                    </td>
                    <td><span class="badge text-bg-light border text-dark">Biologi</span></td>
                    <td>45 Menit</td>
                    <td style="min-width: 140px;">
                        <div class="d-flex justify-content-between fs-8 mb-1">
                            <span>100% Selesai</span>
                            <span class="text-muted">32/32</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%"></div>
                        </div>
                    </td>
                    <td><span class="badge text-bg-secondary">Selesai</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-dark">Rekap</button>
                        </div>
                    </td>
                </tr>
            </x-table>
        </x-card>

        <!-- Interactive Tabs Card -->
        <div class="card shadow-sm mt-4">
            <div class="card-header p-2">
                <ul class="nav nav-pills" id="cbt-demo-tabs">
                    <li class="nav-item">
                        <a class="nav-link active py-1 px-3 fs-7" href="#tab-lan" data-bs-toggle="tab">Arsitektur LAN & Offline</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 fs-7" href="#tab-security" data-bs-toggle="tab">Keamanan & Anti-Cheating</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 fs-7" href="#tab-specs" data-bs-toggle="tab">Spesifikasi Sistem</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active show" id="tab-lan">
                        <h6 class="fw-bold text-primary mb-2">Pilar Utama Server LAN CBT School</h6>
                        <p class="text-secondary fs-7 mb-2">
                            Aplikasi dibangun dengan prinsip zero-external-dependency. Tidak memerlukan sambungan internet saat ujian berlangsung:
                        </p>
                        <ul class="fs-7 text-secondary mb-0 ps-3">
                            <li><strong>Local AdminLTE Bundle:</strong> Semua file CSS, JavaScript, font glyph, dan gambar disimpan di <code>public/theme/adminLTE</code>.</li>
                            <li><strong>Server Time Truth:</strong> Timer hitung mundur siswa dihitung mutlak dari <code>started_at</code> dan <code>expires_at</code> di database server.</li>
                            <li><strong>Autosave Idempoten:</strong> Jawaban siswa dikirim berkala tanpa memblokir interaksi antarmuka ujian.</li>
                        </ul>
                    </div>
                    <div class="tab-pane" id="tab-security">
                        <h6 class="fw-bold text-primary mb-2">Keamanan Sesi & Kunci Soal</h6>
                        <p class="text-secondary fs-7 mb-2">
                            Pencegahan kecurangan dan perlindungan integritas soal:
                        </p>
                        <ul class="fs-7 text-secondary mb-0 ps-3">
                            <li><strong>Snapshot Soal & Opsi:</strong> Soal dan bobot dikunci pada saat attempt dimulai; perubahan bank soal setelahnya tidak mempengaruhi ujian yang berjalan.</li>
                            <li><strong>Kunci Jawaban Terlindungi:</strong> Kunci jawaban tidak pernah dikirimkan ke browser client selama attempt masih aktif.</li>
                            <li><strong>Audit Log Komprehensif:</strong> Setiap aksi admin finalisasi, perpanjangan waktu, atau retake tersimpan dalam audit log.</li>
                        </ul>
                    </div>
                    <div class="tab-pane" id="tab-specs">
                        <div class="row g-2 fs-7">
                            <div class="col-6"><strong>Versi PHP:</strong> {{ PHP_VERSION }}</div>
                            <div class="col-6"><strong>Framework:</strong> Laravel {{ app()->version() }}</div>
                            <div class="col-6"><strong>Basis Data:</strong> SQLite 3 (WAL Mode)</div>
                            <div class="col-6"><strong>Admin Template:</strong> AdminLTE 3 / 4 (Bootstrap 5)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State Demo -->
        <x-card title="Empty State Table Demo (Kondisi Tanpa Data)" class="shadow-sm mt-4">
            <x-table :headers="['#', 'Nama Bank Soal', 'Guru Pengampu', 'Jumlah Soal', 'Aksi']" :empty="true" emptyMessage="Belum ada bank soal baru yang ditambahkan ke sistem." />
        </x-card>
    </div>

    <!-- Right Column: Form Component & Status Alerts -->
    <div class="col-lg-4">
        <!-- Reusable Form Card -->
        <x-card title="Form Input Component Demo" type="primary" :outline="true" class="shadow-sm">
            <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Demo form submission simulated.');">
                <x-form-input 
                    name="exam_name" 
                    label="Nama Ujian" 
                    placeholder="Contoh: Ujian Akhir Semester" 
                    required="true"
                    help="Gunakan nama resmi yang mudah dikenali siswa"
                />

                <x-form-input 
                    name="duration" 
                    label="Durasi (Menit)" 
                    type="number" 
                    placeholder="Contoh: 90" 
                    required="true"
                />

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mata Pelajaran</label>
                    <select class="form-select" name="subject_id">
                        <option value="1">Matematika Wajib (Kelas X)</option>
                        <option value="2">Bahasa Indonesia (Kelas XI)</option>
                        <option value="3">Bahasa Inggris (Kelas XII)</option>
                        <option value="4">Biologi Peminatan (Kelas XII)</option>
                    </select>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="shuffle_questions" checked>
                    <label class="form-check-label fs-7" for="shuffle_questions">Acak urutan butir soal per siswa</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 shadow-sm">
                    Simpan Simulasi Ujian
                </button>
            </form>
        </x-card>

        <!-- System Alerts Demo Card -->
        <x-card title="Pemberitahuan Sistem (Alert Demo)" class="shadow-sm mt-4">
            <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3 fs-7" role="alert">
                <svg class="me-2 flex-shrink-0" style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><strong>Berhasil:</strong> Koneksi database lokal SQLite siap.</div>
            </div>

            <div class="alert alert-warning d-flex align-items-center mb-3 py-2 px-3 fs-7" role="alert">
                <svg class="me-2 flex-shrink-0" style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div><strong>Perhatian:</strong> Ujian PTS Ganjil akan berakhir dalam 15 menit.</div>
            </div>

            <div class="alert alert-info d-flex align-items-center mb-0 py-2 px-3 fs-7" role="alert">
                <svg class="me-2 flex-shrink-0" style="width: 1.15rem; height: 1.15rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><strong>Info Server:</strong> Waktu server disinkronkan secara realtime.</div>
            </div>
        </x-card>
    </div>
</div>

<!-- Modal Component Demo -->
<x-modal id="demoModal" title="Tambah Jadwal Ujian Baru (Demo)">
    <div class="alert alert-primary py-2 px-3 fs-7 mb-3">
        Dialog modal ini memvalidasi komponen modal reusable berbasis Blade dan AdminLTE tanpa CDN eksternal.
    </div>

    <x-form-input 
        name="modal_exam_title" 
        label="Judul Ujian" 
        placeholder="Masukkan judul ujian" 
        value="Penilaian Akhir Semester Ganjil"
        required="true"
    />

    <x-form-input 
        name="modal_exam_duration" 
        label="Durasi (Menit)" 
        type="number"
        placeholder="Contoh: 90" 
        value="90"
        required="true"
    />

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="alert('Ujian berhasil disimulasikan!');">Simpan Ujian</button>
    </x-slot>
</x-modal>
@endsection
