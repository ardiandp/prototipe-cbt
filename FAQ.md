# Panduan Penggunaan & Tanya-Jawab (FAQ) — CBT School

Dokumen ini berisi panduan operasional lengkap, prosedur penggunaan, serta pertanyaan yang sering diajukan (*Frequently Asked Questions*) untuk seluruh peran pengguna sistem **CBT School (Computer Based Test)**: Administrator, Guru Pengampu, Siswa Peserta Ujian, dan Pengawas Ruangan/Proktor.

---

## Daftar Isi
1. [Ikhtisar Sistem CBT School](#1-ikhtisar-sistem-cbt-school)
2. [Panduan Administrator (Super Admin & Admin Sekolah)](#2-panduan-administrator-super-admin--admin-sekolah)
3. [Panduan Guru Pengampu (Pembuat Soal & Jadwal Ujian)](#3-panduan-guru-pengampu-pembuat-soal--jadwal-ujian)
4. [Panduan Siswa (Peserta Ujian CBT)](#4-panduan-siswa-peserta-ujian-cbt)
5. [Panduan Pengawas Ruangan & Proktor](#5-panduan-pengawas-ruangan--proktor)
6. [Troubleshooting Teknis LAN & Pemecahan Masalah](#6-troubleshooting-teknis-lan--pemecahan-masalah)

---

## 1. Ikhtisar Sistem CBT School

### Q: Apa itu CBT School?
**A:** CBT School adalah aplikasi ujian berbasis komputer (*Computer Based Test*) yang dirancang khusus untuk kebutuhan sekolah (SMP, SMA, SMK). Sistem ini dioptimalkan untuk berjalan secara mandiri pada jaringan intranet lokal (**Local Area Network / LAN**) tanpa bergantung pada koneksi internet publik.

### Q: Mengapa CBT School menggunakan arsitektur offline LAN?
**A:** Untuk menjamin:
- **Ketahanan Jaringan:** Ujian tetap berjalan stabil walau koneksi internet ISP padam.
- **Kemandirian Asset:** Seluruh file tema (AdminLTE), CSS, JavaScript, font, dan gambar disimpan lokal (0% CDN eksternal).
- **Keamanan Ujian:** Mencegah siswa membuka website pencarian luar atau bertukar pesan secara online saat berada dalam jaringan ujian terisolasi.

### Q: Apa saja peran (*roles*) dalam aplikasi ini?
1. **Super Admin:** Memiliki hak akses tertinggi terhadap konfigurasi server, database, dan audit log global.
2. **Admin Sekolah:** Mengelola data master satuan pendidikan (profil sekolah, tahun ajaran, jurusan, kelas, mata pelajaran, akun guru, dan akun siswa).
3. **Guru:** Mengelola bank soal pilihan ganda (A–E), membuat jadwal ujian, monitoring peserta, serta melihat analisis butir soal dan rekap nilai.
4. **Siswa:** Mengerjakan ujian terjadwal, melihat sisa waktu server, mengubah jawaban dengan autosave, dan melihat lembar hasil/nilai setelah dipublikasikan.

---

## 2. Panduan Administrator (Super Admin & Admin Sekolah)

### Q: Apa langkah awal yang harus dilakukan Admin setelah instalasi?
**A:** Urutan penyiapan data master sekolah:
1. Buka menu **Data Master > Profil Sekolah**, perbarui nama sekolah, NPSN, nama kepala sekolah, dan alamat.
2. Buka **Data Master > Tahun Ajaran**, pastikan tahun ajaran dan semester yang aktif sudah diaktifkan (misal: 2026/2027 Ganjil).
3. Buka **Data Master > Jurusan** dan **Kelas**, buat rombongan belajar yang ada.
4. Buka **Data Master > Mata Pelajaran**, daftarkan mapel yang diujikan.
5. Daftarkan akun guru melalui **Data Master > Data Guru** dan akun siswa melalui **Data Master > Data Siswa**.

### Q: Bagaimana cara mengaktifkan Tahun Ajaran dan Semester baru?
**A:** 
- Masuk ke menu **Tahun Ajaran**.
- Klik tombol **Aktifkan** pada tahun ajaran yang diinginkan.
- Pada tabel semester di bawahnya, klik **Aktifkan Semester** (Ganjil atau Genap). Sistem hanya mengizinkan satu periode aktif dalam satu waktu.

### Q: Bagaimana cara mereset data demo ke kondisi awal pengujian?
**A:** Jika sistem sedang digunakan untuk simulasi demonstrasi, jalankan perintah Artisan berikut di terminal server:
```bash
php artisan cbt:reset-demo --force
```
Perintah ini akan menyegarkan database SQLite dan menyiapkan:
- 1 Ujian Aktif Siap Uji (`EXAM-PWPB-01`)
- 1 Ujian Selesai Dinilai (`EXAM-BD-01`)
- 1 Ujian Draft (`EXAM-PWPB-REMIDIAL`)
- Akun demo untuk seluruh role.

### Q: Bagaimana cara melihat riwayat aktivitas penting pada sistem?
**A:** Masuk ke menu **Audit Log & Aktivitas**. Seluruh aktivitas penting (login, logout, publikasi ujian, finalisasi paksa, otorisasi ujian susulan/retake, dan publikasi nilai) tercatat lengkap dengan stempel waktu, nama pengguna, IP address, dan rincian payload perubahan.

---

## 3. Panduan Guru Pengampu (Pembuat Soal & Jadwal Ujian)

### Q: Bagaimana cara membuat Bank Soal baru?
**A:** 
1. Masuk ke menu **Bank Soal**, klik **+ Tambah Bank Soal**.
2. Pilih mata pelajaran, masukkan nama bank soal (misal: *Bank Soal Pemrograman Web XII*), dan tentukan kode unik.
3. Klik **Simpan**. Setelah itu, klik tombol **Kelola Butir Soal** untuk mulai menginput pertanyaan.

### Q: Apa saja format soal yang didukung?
**A:** Sistem mendukung format **Pilihan Ganda 5 Opsi (A, B, C, D, E)**:
- Pertanyaan dapat berupa teks dan gambar ilustrasi (PNG, JPG, WebP).
- Setiap butir soal wajib memiliki tepat 1 kunci jawaban benar.
- Bobot butir soal harus bernilai positif (default: 1.0).

### Q: Apa itu sistem "Snapshot Soal" saat membuat jadwal ujian?
**A:** Demi menjaga integritas dan keadilan ujian:
- Ketika jadwal ujian dibuat dan butir soal dipilih, sistem membuat **snapshot independen** (rekaman salinan soal, opsi, dan bobot pada saat itu).
- Jika guru kemudian mengedit atau menghapus soal di bank soal, **soal pada ujian yang sedang atau sudah berjalan TIDAK AKAN BERUBAH**. Nilai siswa tetap akurat sesuai soal yang mereka kerjakan.

### Q: Bagaimana cara mengatur acak soal dan opsi jawaban?
**A:** Pada form **Tambah/Ubah Jadwal Ujian**:
- Centang opsi **Acak Urutan Soal** agar nomor soal yang diterima setiap siswa berbeda-beda.
- Centang opsi **Acak Pilihan Jawaban** agar urutan letak opsi A–E pada setiap siswa diacak secara matematis. Randomisasi bersifat stabil dan terkunci per siswa.

### Q: Kapan siswa dapat melihat nilai ujian mereka?
**A:** Nilai ujian siswa **tidak akan muncul** sampai guru atau admin menekan tombol **Terbitkan Nilai** (*Publish Results*) di halaman Hasil Ujian. Ini memberikan kesempatan bagi guru untuk memeriksa rekapitulasi terlebih dahulu sebelum mengumumkan hasil kepada siswa.

---

## 4. Panduan Siswa (Peserta Ujian CBT)

### Q: Bagaimana cara memulai ujian?
**A:** 
1. Buka browser (Chrome, Edge, atau Firefox) dan akses alamat IP server LAN CBT (misal: `http://192.168.1.100:8000`).
2. Masukkan **Username (NIS)** dan **Password**, lalu klik **Masuk**.
3. Di dashboard siswa, klik menu **Ujian Tersedia**.
4. Cari ujian yang berstatus **Siap Dikerjakan**, lalu klik **Mulai Ujian**.
5. Baca petunjuk pada Halaman Konfirmasi, masukkan **Token Ujian** (jika ada), lalu klik **Mulai Sekarang**.

### Q: Mengapa timer ujian tetap berjalan saat saya me-refresh halaman atau menutup browser?
**A:** Timer ujian CBT School menggunakan **Waktu Server (*Server Time Truth*)**, bukan timer jam di laptop/HP siswa.
- Waktu selesai dihitung dari: `started_at + durasi`.
- Me-refresh halaman, mengganti perangkat, atau menutup browser **tidak akan menambah waktu atau mereset timer**. Siswa disarankan tetap fokus mengerjakan di ruang ujian.

### Q: Apakah jawaban saya aman jika laptop mati mendadak atau jaringan WiFi terputus?
**A:** **Sangat aman.** CBT School dilengkapi fitur **Realtime Autosave**:
- Setiap kali Anda mengklik opsi jawaban (A, B, C, D, atau E), jawaban langsung dikirim dan disimpan ke server seketika itu juga.
- Indikator status di pojok kanan akan menampilkan tulisan hijau **"Tersimpan"**.
- Jika koneksi terputus sesaat, sistem akan mencoba mengirim ulang secara otomatis tanpa menghapus jawaban yang telah Anda pilih.

### Q: Apa fungsi tombol "Tandai Ragu-Ragu"?
**A:** Tombol warna kuning berlogo bendera/tanya ini berfungsi memberi tanda pada nomor soal di daftar navigasi jika Anda belum yakin dengan jawaban yang dipilih, sehingga mudah dicari kembali sebelum waktu habis.

### Q: Bagaimana cara mengakhiri ujian (*Submit*)?
**A:** 
1. Buka nomor soal terakhir atau klik tombol **Selesai Ujian**.
2. Modal dialog ringkasan akan muncul menampilkan jumlah soal yang sudah dijawab, belum dijawab, dan ragu-ragu.
3. Centang kotak persetujuan konfirmasi, lalu klik tombol merah **Ya, Kumpulkan Jawaban Sekarang**.

---

## 5. Panduan Pengawas Ruangan & Proktor

### Q: Di mana pengawas dapat memantau pengerjaan siswa secara langsung?
**A:** Masuk ke menu **Monitoring Ujian**:
- Dashboard monitoring memperbarui data secara otomatis (polling berkala).
- Menampilkan ringkasan: Total Peserta, Belum Mulai, Sedang Mengerjakan, Selesai, dan Kedaluwarsa.
- Menampilkan persentase progress jawaban per siswa secara visual.

### Q: Kapan tindakan "Finalisasi Paksa" (*Force Finish*) digunakan?
**A:** Tindakan ini digunakan jika:
- Siswa telah selesai ujian dan meninggalkan ruangan tanpa menekan tombol kumpulkan jawaban.
- Waktu ujian telah habis namun browser siswa tertutup sebelum melakukan submit otomatis.
- Pengawas wajib memasukkan alasan administratif (misal: *"Siswa meninggalkan ruangan sebelum submit"*), yang akan dicatat di Audit Log.

### Q: Apa fungsi fitur "Otorisasi Ujian Susulan / Ulang" (*Retake Authorization*)?
**A:** Jika terjadi kendala darurat (misal: komputer siswa rusak parah di awal ujian atau terjadi musibah), pengawas/admin dapat memberikan izin ujian ulang:
1. Klik tombol **Izinkan Retake** pada baris siswa di halaman Monitoring.
2. Masukkan alasan resmi dan masa durasi.
3. Sistem akan membuatkan sesi attempt baru (`attempt_number` bertambah), mereset timer, dan mencatat otorisasi di Audit Log. Jawaban attempt lama tetap diarsipkan sebagai bukti historis.

---

## 6. Troubleshooting Teknis LAN & Pemecahan Masalah

### Q: Komputer siswa tidak dapat membuka halaman CBT (ERR_CONNECTION_TIMED_OUT).
**A:** Periksa langkah-langkah berikut pada Komputer Server:
1. Pastikan server dijalankan dengan binding ke IP LAN:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
2. Pastikan port 8000 diizinkan di Windows Firewall Server. Buka PowerShell Admin di server dan jalankan:
   ```powershell
   New-NetFirewallRule -DisplayName "CBT School LAN Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
   ```
3. Pastikan Router WiFi / Access Point **TIDAK mengaktifkan fitur "AP Isolation"** (Client Isolation), agar laptop siswa dapat berkomunikasi dengan laptop server.

### Q: Waktu timer di layar siswa terlihat tidak sinkron beberapa detik dengan jam server.
**A:** Halaman ruang ujian secara berkala memanggil endpoint `/attempts/{attempt}/sync-time` untuk menyelaraskan jam client dengan jam server. Perbedaan 1–2 detik wajar karena jeda transmisi jaringan wifi lokal dan tidak mempengaruhi waktu akhir (`expires_at`) yang dihitung mutlak di database server.

### Q: Bagaimana cara menjalankan finalisasi otomatis untuk attempt yang kedaluwarsa?
**A:** Jalankan command Artisan di latar belakang server:
```bash
php artisan cbt:finalize-expired-attempts
```
Command ini akan memeriksa semua attempt yang `expires_at`-nya telah lewat namun belum ditandai selesai, lalu melakukan scoring otomatis secara idempotent.

### Q: Bagaimana cara melakukan backup database hasil ujian?
**A:** Karena CBT School menggunakan basis data SQLite, seluruh data sekolah, soal, dan hasil ujian tersimpan dalam satu file:
`database/database.sqlite`
Cukup salin (*copy-paste*) file `database.sqlite` ke flashdisk atau folder cadangan setelah ujian selesai.

---

*Hak Cipta &copy; 2026 CBT School — Computer Based Test System.*
