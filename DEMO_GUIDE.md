# Panduan Skenario Demo CBT School

Panduan ini disusun untuk penguji, asesor, atau penilai yang ingin mendemonstrasikan seluruh fitur sistem **CBT School** secara langsung (*end-to-end*) di lingkungan lokal/LAN.

---

## 1. Akun Pengguna Demo

Seluruh akun demo menggunakan kata sandi default: **`password`**

| Role / Peran | Username | Kata Sandi | Akses Utama |
|---|---|---|---|
| **Super Administrator** | `superadmin` | `password` | Konfigurasi sistem, profil sekolah, dan manajemen akun |
| **Admin Sekolah** | `admin` | `password` | Master data (guru, siswa, kelas), monitoring, rekap nilai & audit log |
| **Guru Pengampu** | `guru` | `password` | Bank soal, jadwal ujian, live monitoring peserta, penilaian & analisis butir soal |
| **Siswa Peserta** | `siswa` | `password` | Ruang ujian CBT, pengerjaan soal pilihan ganda, dan riwayat nilai |

---

## 2. Perintah Reset Data Demo

Kapan saja Anda ingin mengembalikan kondisi database ke skenario demo awal yang bersih dan konsisten, jalankan perintah berikut di terminal:

```bash
php artisan cbt:reset-demo --force
```

Perintah ini akan secara otomatis:
1. Me-refresh database SQLite secara bersih (`migrate:fresh`).
2. Mengisi seluruh profil sekolah, tahun ajaran, jurusan, kelas, mapel, guru, siswa, dan bank soal.
3. Menyiapkan 3 skenario jadwal ujian:
   - **`EXAM-PWPB-01`**: Ujian aktif dan siap dikerjakan siswa `siswa` (Token: `CBT2026`).
   - **`EXAM-BD-01`**: Ujian selesai dengan rekap skor, siap diuji laporan & analisis butir soal.
   - **`EXAM-PWPB-REMIDIAL`**: Ujian status draft untuk menguji alur pembuatan/editing jadwal guru.
4. Menghasilkan riwayat audit log awal yang realistis.

---

## 3. Skenario Pengujian End-to-End

### Skenario A: Pengalaman Ujian Siswa (Student Exam Experience)
1. Buka browser dan akses halaman login: `http://localhost:8000/login`
2. Masuk menggunakan akun siswa:
   - **Username**: `siswa`
   - **Password**: `password`
3. Pada dashboard siswa, klik menu **"Ujian Tersedia"**.
4. Temukan jadwal ujian **"Asesmen Sumatif Tengah Semester - Pemrograman Web"** (`EXAM-PWPB-01`), lalu klik tombol **"Masuk Ujian"**.
5. Pada ruang persiapan:
   - Periksa informasi ujian (Durasi: 60 menit, KKM: 75, Jumlah Soal: 10).
   - Masukkan Token Ujian: **`CBT2026`**
   - Klik tombol **"Mulai Mengerjakan Ujian"**.
6. Anda sekarang berada di **Ruang Ujian CBT**:
   - **Navigasi Soal**: Klik nomor soal pada panel palette di sisi kanan untuk berpindah butir soal.
   - **Pilihan Jawaban**: Klik opsi A, B, C, D, atau E. Perhatikan indikator badge di kanan atas berubah menjadi *"Tersimpan"* (Autosave).
   - **Ragu-Ragu**: Centang kotak *"Tandai Ragu-Ragu"*, perhatikan nomor soal pada palette berubah warna menjadi kuning.
   - **Timer Server**: Perhatikan sisa waktu yang terus berjalan mundur secara sinkron dengan jam server.
   - **Uji Anti-Reset Timer**: Tekan tombol refresh browser (**F5**) — sesi pengerjaan tetap aman, jawaban tidak hilang, dan waktu tidak berulang.
7. Di soal terakhir, klik tombol **"Selesai Ujian"**:
   - Periksa modal rekapitulasi (jumlah soal terjawab, ragu-ragu, dan belum dijawab).
   - Centang konfirmasi dan klik **"Kirim Jawaban Akhir"**.
8. Anda diarahkan ke halaman penyelesaian ujian.

---

### Skenario B: Monitoring Peserta & Tindakan Administratif (Live Monitoring)
1. Buka tab baru atau jendela *incognito*, login sebagai Guru:
   - **Username**: `guru`
   - **Password**: `password`
2. Klik menu **"Monitoring Ujian"** di sidebar.
3. Klik tombol **"Pantau Live"** pada ujian `EXAM-PWPB-01`.
4. Perhatikan fitur monitoring:
   - Ringkasan live status: Belum Mulai, Sedang Mengerjakan, Selesai, Kedaluwarsa.
   - Auto-refresh real-time setiap 5 detik (tanpa reload halaman penuh).
   - Klik tombol **"Detail"** pada salah satu baris peserta untuk melihat rincian attempt dan jawaban yang sedang dipilih peserta.
5. Uji Tindakan Administratif:
   - **Selesaikan Paksa (Force Finish)**: Klik tombol merah *"Force Finish"*, masukkan alasan (misal: *"Peserta mengalami gangguan teknis"*), lalu konfirmasi. Attempt siswa langsung diselesaikan secara sepihak dan dihitung nilainya.
   - **Izin Ujian Ulang (Retake)**: Pada peserta yang sudah selesai/force-finish, klik tombol kuning *"Beri Izin Retake"*, masukkan alasan otorisasi. Siswa terkait dapat memulai sesi attempt baru.
   - **Audit Tindakan**: Klik tombol **"Log Audit Ujian"** di pojok kanan atas untuk melihat seluruh riwayat tindakan pengawas berserta alasannya.

---

### Skenario C: Rekap Nilai, Peringkat, Analisis Butir Soal & Export
1. Masih sebagai akun `guru` (atau `admin`), buka menu **"Hasil & Rekap Nilai"**.
2. Pilih ujian **"Penilaian Harian Basis Data - DDL & DML"** (`EXAM-BD-01`) yang sudah selesai.
3. **Publikasi Hasil ke Siswa**:
   - Perhatikan tombol status publikasi nilai (*"Publikasikan Nilai ke Siswa"* / *"Tarik Publikasi"*).
   - Jika dipublikasikan, akun `siswa` dapat membuka menu *"Riwayat & Nilai"* untuk melihat nilai dan lembar jawaban.
4. Buka menu **"Laporan & Peringkat"**:
   - Klik tombol **"Laporan Lengkap"** pada `EXAM-BD-01`.
   - **Statistik Nilai**: Tinjau nilai Rata-rata, Tertinggi, Terendah, Median, dan Standar Deviasi.
   - **Papan Peringkat (Ranking)**: Tinjau urutan ranking siswa dengan medali emas (peringkat 1), perak (2), dan perunggu (3).
   - **Distribusi Nilai**: Tinjau sebaran frekuensi nilai dalam rentang (histogram).
5. Buka tab **"Analisis Butir Soal"**:
   - Tinjau tingkat kesukaran per butir soal: **Mudah** ($\ge 70\%$), **Sedang** ($30\% - 69\%$), **Sukar** ($< 30\%$).
   - Tinjau efektivitas distraktor / pengecoh: persentase pemilihan opsi A, B, C, D, dan E oleh peserta.
6. Uji Fitur Ekspor:
   - Klik **"Export CSV Nilai"**: mengunduh file spreadsheet CSV UTF-8 BOM yang langsung rapi di Microsoft Excel.
   - Klik **"Export Analisis CSV"**: mengunduh data analisis butir soal ke format CSV.
   - Klik **"Cetak Laporan / PDF"**: membuka tampilan siap cetak dengan Kop Surat resmi sekolah (`SchoolProfile`), rekapitulasi, dan kolom tanda tangan Kepala Sekolah & Guru Pengampu.

---

### Skenario D: Audit Log & Keamanan Sistem
1. Login sebagai **Admin Sekolah**:
   - **Username**: `admin`
   - **Password**: `password`
2. Klik menu **"Audit Log & Aktivitas"** di sidebar.
3. Fitur pelacakan:
   - Filter berdasarkan jenis tindakan: `login`, `logout`, `publish_exam`, `force_finish`, `retake_authorization`, `publish_results`.
   - Filter berdasarkan rentang tanggal atau kata kunci pencarian.
   - Klik tombol **"Detail"** pada baris log: memeriksa timestamp WIB, IP address, user agent, keterangan alasan, dan payload data JSON lengkap.

---

## 4. Cara Menjalankan Server Lokal (LAN Demo)

Untuk menjalankan server CBT School agar dapat diakses dari komputer klien lain di jaringan lokal / WiFi sekolah:

```bash
# Jalankan server lokal di semua interface jaringan (port 8000)
php artisan serve --host=0.0.0.0 --port=8000
```

Klien di jaringan lokal dapat mengakses dengan membuka browser dan mengetik alamat IP host server:
`http://<IP-KOMPUTER-SERVER>:8000` (contoh: `http://192.168.1.100:8000`).

