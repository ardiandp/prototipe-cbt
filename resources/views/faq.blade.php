@extends('layouts.app')

@section('title', 'Pusat Bantuan & FAQ - CBT School')
@section('page_title', 'Pusat Bantuan & FAQ')
@section('page_subtitle', 'Panduan operasional dan tanya-jawab penggunaan sistem untuk Seluruh Role (Admin, Guru, Siswa, Pengawas)')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">FAQ</li>
@endsection

@section('content')
<!-- Hero Header Callout -->
<div class="callout callout-primary bg-white shadow-sm mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-primary">Panduan Penggunaan Sistem Ujian CBT School</h5>
            <p class="mb-0 text-secondary fs-7">
                Temukan panduan langkah demi langkah dan solusi kendala teknis operasional ujian berbasis komputer dalam jaringan lokal (LAN) sekolah.
            </p>
        </div>
        <div>
            <a href="{{ asset('FAQ.md') }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm" onclick="alert('Dokumentasi lengkap juga tersedia di file FAQ.md pada root project.');">
                <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh / Lihat FAQ.md
            </a>
        </div>
    </div>
</div>

<!-- Category Tabs -->
<div class="card shadow-sm mb-4">
    <div class="card-header p-2 bg-body">
        <ul class="nav nav-pills" id="faqTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active py-2 px-3 fs-7" href="#faq-general" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Umum & LAN
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-7" href="#faq-admin" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Admin Sekolah
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-7" href="#faq-teacher" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Guru Pengampu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-7" href="#faq-student" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Siswa Peserta
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-7" href="#faq-proctor" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Pengawas / Proktor
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 fs-7" href="#faq-tech" data-bs-toggle="tab">
                    <svg class="me-1" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Troubleshooting LAN
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Contents -->
<div class="tab-content" id="faqTabContent">
    <!-- 1. GENERAL / UMUM -->
    <div class="tab-pane fade show active" id="faq-general">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Apa itu sistem CBT School?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        CBT School adalah aplikasi Computer Based Test berbasis web yang dirancang untuk sekolah (SMP/SMA/SMK) dengan arsitektur server lokal LAN (*Local Area Network*). Sistem dapat beroperasi mandiri 100% tanpa sambungan internet publik, menjamin kelancaran ujian meski jaringan luar terganggu.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Mengapa asset AdminLTE dimuat secara offline?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Semua file tema (CSS, JS, gambar, avatar) disimpan di folder <code>public/theme/adminLTE/dist</code>. Ini memastikan antarmuka tetap tampil sempurna saat laptop server dan klien siswa hanya terhubung via router switch LAN sekolah tanpa akses CDN internet.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Berapa spesifikasi komputer server yang disarankan?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Untuk kapasitas 30–50 siswa serentak (1 ruang laboratorium):
                        <ul class="mb-0 mt-1 ps-3">
                            <li><strong>Prosesor:</strong> Intel Core i3 / Ryzen 3 setara atau lebih tinggi.</li>
                            <li><strong>RAM:</strong> Minimal 4 GB (Disarankan 8 GB).</li>
                            <li><strong>Penyimpanan:</strong> SSD (Solid State Drive) untuk penulisan SQLite yang cepat.</li>
                            <li><strong>Jaringan:</strong> Kabel LAN Gigabit atau Router WiFi 5 GHz tanpa AP Isolation.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. ADMIN SEKOLAH -->
    <div class="tab-pane fade" id="faq-admin">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana alur setup awal data master sekolah?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Urutan setup yang disarankan:
                        <ol class="mb-0 mt-1 ps-3">
                            <li><strong>Profil Sekolah:</strong> Lengkapi nama sekolah, NPSN, nama kepala sekolah, dan alamat di menu Profil Sekolah.</li>
                            <li><strong>Tahun Ajaran & Semester:</strong> Pastikan periode aktif sudah dicentang aktif.</li>
                            <li><strong>Jurusan & Kelas:</strong> Buat tingkatan rombel (misal: XII RPL 1, XII TKJ 1).</li>
                            <li><strong>Mata Pelajaran:</strong> Daftarkan mapel yang akan diujikan.</li>
                            <li><strong>Akun Guru & Siswa:</strong> Daftarkan akun pengampu dan peserta ujian.</li>
                        </ol>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana cara mereset dataset ujian demo ke kondisi awal?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Cukup jalankan satu baris perintah Artisan di terminal server:
                        <div class="bg-dark text-white p-2 rounded mt-1 mb-2 font-monospace fs-8">
                            php artisan cbt:reset-demo --force
                        </div>
                        Perintah ini akan menyiapkan data demo siap pakai: ujian aktif (EXAM-PWPB-01), ujian dinilai (EXAM-BD-01), bank soal lengkap, dan akun seluruh peran.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Apa fungsi menu Audit Log & Aktivitas?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Audit Log mencatat jejak digital dari setiap tindakan krusial: riwayat login pengguna, publikasi atau pembatalan jadwal ujian, tindakan pengawas (finalisasi paksa dan retake), serta publikasi nilai hasil ujian lengkap dengan waktu, IP, dan pelakunya.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. GURU PENGAMPU -->
    <div class="tab-pane fade" id="faq-teacher">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana cara membuat butir soal pilihan ganda?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Masuk ke menu <strong>Bank Soal</strong> &rarr; klik <strong>Kelola Butir Soal</strong> &rarr; klik <strong>Tambah Soal</strong>. Setiap butir soal memiliki editor teks pertanyaan, opsi unggah gambar lokal opsional, 5 pilihan jawaban (A, B, C, D, E), penentuan kunci jawaban benar, dan bobot skor positif.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Apa itu "Snapshot Soal" saat jadwal ujian dibuat?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Demi menjaga objektivitas dan integritas penilaian, saat jadwal ujian dibuat sistem membuat salinan snapshot soal dan opsi. Jika sewaktu-waktu guru mengubah atau menghapus soal di Bank Soal induk, <strong>soal pada ujian yang sedang atau telah berjalan tidak akan berubah</strong>.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Kapan siswa dapat melihat nilai ujian mereka?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Nilai ujian siswa tidak akan langsung terlihat setelah submit. Guru atau Admin harus meninjau rekapitulasi terlebih dahulu, kemudian menekan tombol <strong>Terbitkan Hasil (Publish Results)</strong> di halaman Hasil Ujian.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. SISWA PESERTA -->
    <div class="tab-pane fade" id="faq-student">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana cara mulai mengerjakan ujian?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Login menggunakan Username (NIS) dan Password yang diberikan sekolah. Buka menu <strong>Ujian Tersedia</strong>, pilih ujian yang berstatus <em>Siap Dikerjakan</em>, masukkan Token Ujian (jika diminta proktor), dan klik <strong>Mulai Ujian</strong>.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Apakah jawaban saya hilang jika komputer restart atau mati listrik?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        <strong>Tidak hilang.</strong> Sistem menggunakan fitur <em>Realtime Autosave</em>. Setiap kali Anda memilih opsi jawaban (A–E), jawaban langsung tersimpan di server dengan status hijau "Tersimpan". Jika komputer mati, Anda cukup menyalakan komputer dan login kembali untuk melanjutkan sisa waktu ujian.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Mengapa waktu ujian tetap berkurang saat halaman di-refresh?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Waktu ujian dihitung mutlak oleh jam server (*Server Time Truth*), bukan jam lokal laptop. Me-refresh halaman atau menutup browser tidak akan menambah waktu atau mengulang timer dari awal.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Apa kegunaan tombol "Ragu-Ragu"?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Tombol ragu-ragu memberi warna kuning pada kotak nomor soal di palet navigasi, memudahkan Anda untuk meninjau kembali soal tersebut sebelum menekan tombol Selesai Ujian.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. PENGAWAS / PROKTOR -->
    <div class="tab-pane fade" id="faq-proctor">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana cara memantau peserta di ruang ujian?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Buka menu <strong>Monitoring Ujian</strong> pada dashboard Guru atau Admin. Halaman ini memperbarui progres siswa secara berkala dan menampilkan status: <em>Belum Mulai</em>, <em>Sedang Mengerjakan</em>, <em>Selesai</em>, atau <em>Kedaluwarsa</em>.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Kapan pengawas menggunakan "Finalisasi Paksa" (Force Finish)?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Gunakan tombol ini jika siswa meninggalkan ruang ujian tanpa menekan tombol kumpulkan jawaban atau waktu telah habis namun sesi browser siswa tertutup. Pengawas wajib memasukkan alasan resmi yang akan tersimpan di Audit Log.
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Kapan pengawas boleh mengizinkan Ujian Ulang (Retake)?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Jika terjadi kendala darurat (misal: komputer siswa korslet di 5 menit awal atau terjadi listrik padam khusus pada 1 PC), pengawas dapat mengklik <strong>Izinkan Retake</strong> dengan memasukkan alasan valid. Sistem akan membuat attempt baru dan mereset timer untuk siswa tersebut.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. TROUBLESHOOTING LAN -->
    <div class="tab-pane fade" id="faq-tech">
        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Komputer klien tidak bisa membuka halaman server (ERR_CONNECTION_TIMED_OUT)</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Pemeriksaan di komputer server:
                        <ol class="mb-0 mt-1 ps-3">
                            <li>Pastikan server berjalan dengan flag host: <code>php artisan serve --host=0.0.0.0 --port=8000</code>.</li>
                            <li>Pastikan firewall server mengizinkan port 8000. Jalankan di PowerShell Admin server:
                                <div class="bg-dark text-white p-2 rounded my-1 font-monospace fs-8">
                                    New-NetFirewallRule -DisplayName "CBT LAN" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
                                </div>
                            </li>
                            <li>Pastikan router WiFi tidak mengaktifkan fitur <strong>AP Isolation</strong> agar antar perangkat dalam satu WiFi dapat saling berkomunikasi.</li>
                        </ol>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0 text-primary">Bagaimana cara backup basis data hasil ujian?</h6>
                    </div>
                    <div class="card-body fs-7 text-secondary">
                        Karena basis data menggunakan SQLite, seluruh data tersimpan dalam satu file tunggal di direktori:
                        <code>database/database.sqlite</code>.
                        Cukup salin file tersebut ke flashdisk atau folder aman setelah sesi ujian selesai.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
