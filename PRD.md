# PRODUCT REQUIREMENTS DOCUMENT

**Revisi v1.1:** AdminLTE 3 wajib menggunakan asset lokal `public/theme/adminLTE`.
# CBT School — Computer Based Test

| Metadata | Value |
|---|---|
| Product | CBT School |
| Document | PRD |
| Version | 1.0 |
| Status | Baseline for prototype |
| Product type | Functional web prototype |
| Target | School (SD/SMP/SMA/SMK) |
| Deployment model | Local Area Network (LAN) |
| Branding | Generic |
| Backend | Laravel |
| UI template | AdminLTE 3 |
| Prototype database | SQLite |
| Data generation | Faker + Laravel Seeders |
| Primary language | Bahasa Indonesia |

---

## 1. Ringkasan Produk

CBT School adalah aplikasi Computer Based Test berbasis web untuk membantu sekolah melaksanakan ujian digital melalui komputer/laptop yang terhubung ke jaringan lokal. Server aplikasi berada pada satu komputer/server sekolah dan perangkat pengguna mengaksesnya melalui browser di jaringan LAN yang sama.

Prototype harus mensimulasikan proses administrasi sekolah, pengelolaan bank soal, pembuatan jadwal ujian, pelaksanaan ujian individual, monitoring, penilaian otomatis, dan pelaporan hasil. Seluruh alur menggunakan data dummy dan database SQLite lokal.

## 2. Tujuan Produk

1. Menghasilkan prototype CBT yang dapat didemonstrasikan dari awal sampai akhir.
2. Menyediakan antarmuka profesional berbasis AdminLTE 3.
3. Mendukung empat role dengan hak akses berbeda.
4. Memungkinkan admin mengelola data sekolah.
5. Memungkinkan guru mengelola soal dan membuat ujian terjadwal.
6. Memungkinkan siswa mengikuti ujian individual sesuai jadwal.
7. Menjamin timer dan status attempt tidak dapat di-reset dengan refresh browser.
8. Menyimpan jawaban otomatis ke database prototype.
9. Menghitung nilai pilihan ganda secara otomatis.
10. Menyediakan monitoring dan laporan sederhana.
11. Menjadi fondasi untuk pengembangan produksi berikutnya tanpa mengklaim prototype sebagai sistem produksi.

## 3. Batasan dan Scope

### 3.1 In Scope

- Authentication dan multi-role.
- Dashboard sesuai role.
- Profil sekolah.
- Tahun ajaran dan semester.
- Jurusan, kelas, mata pelajaran.
- Data guru dan siswa.
- Seeder dan Faker.
- Bank soal pilihan ganda A–E.
- Kategori dan tingkat kesulitan soal.
- Pembuatan dan penjadwalan ujian.
- Penentuan peserta individual.
- Randomisasi soal dan opsi.
- Snapshot soal.
- Ruang ujian.
- Timer berbasis waktu server.
- Autosave jawaban.
- Resume attempt setelah refresh/logout-login.
- Submit manual dan otomatis.
- Scoring otomatis.
- Monitoring peserta.
- Publikasi hasil.
- Rekap nilai dan analisis soal dasar.
- Audit log aktivitas penting.
- Export sederhana jika tidak mengganggu fitur inti.
- Automated tests untuk aturan penting.

### 3.2 Out of Scope

- Soal essay atau jawaban bebas.
- Audio/video question.
- Webcam atau AI proctoring.
- Integrasi Dapodik/LMS.
- Aplikasi Android/iOS.
- Payment gateway.
- Multi-tenant SaaS.
- Sinkronisasi cloud.
- Integrasi API eksternal.
- Anti-cheating yang menjamin siswa tidak dapat membuka aplikasi lain.
- Produksi, high availability, dan disaster recovery tingkat enterprise.

## 4. Asumsi dan Keputusan Produk

1. Satu instalasi prototype mewakili satu sekolah.
2. Penggunaan utama adalah komputer/laptop di laboratorium sekolah.
3. Pengguna mengakses server melalui LAN; internet tidak diperlukan saat aplikasi berjalan, setelah seluruh dependency dan asset tersedia secara lokal.
4. Ujian menggunakan mekanisme **scheduled**.
5. Setiap siswa memulai ujian secara **individual**, bukan guru memulai satu sesi serentak untuk seluruh kelas.
6. Jadwal memiliki `start_at` dan `end_at`; durasi attempt ditentukan terpisah.
7. Siswa hanya dapat memulai dalam rentang jadwal.
8. Batas akhir attempt adalah nilai yang lebih awal antara `started_at + duration` dan `exam.end_at`.
9. Maksimal satu attempt aktif/valid per siswa per ujian. Retake hanya melalui tindakan admin/guru yang berwenang dan tercatat.
10. SQLite dipakai untuk prototype dan harus mudah diganti ke MySQL/MariaDB melalui konfigurasi Laravel.
11. Tidak menggunakan data pribadi siswa/guru sungguhan.
12. Akun demo disediakan melalui seeder.

## 5. Role dan Matriks Hak Akses

### 5.1 Super Admin

- Mengakses dashboard global.
- Mengelola akun admin.
- Mengelola identitas aplikasi/sekolah.
- Melihat seluruh data prototype.
- Mengakses audit log.
- Menjalankan reset data demo jika disediakan dengan konfirmasi kuat.

### 5.2 Admin Sekolah

- Mengelola tahun ajaran dan semester.
- Mengelola jurusan, kelas, mata pelajaran.
- Mengelola data guru dan siswa.
- Mengelola/meninjau jadwal ujian.
- Melihat monitoring dan laporan seluruh sekolah.
- Melakukan tindakan administratif yang diizinkan, termasuk retake dengan alasan.

### 5.3 Guru

- Melihat kelas dan mata pelajaran yang menjadi kewenangannya.
- Mengelola kategori dan bank soal miliknya/yang diizinkan.
- Membuat, mengubah, menjadwalkan, dan memublikasikan ujian sesuai kewenangan.
- Menentukan peserta.
- Melihat monitoring ujian yang dikelolanya.
- Melihat hasil, analisis soal, dan memublikasikan nilai.
- Meminta atau memberikan retake jika kebijakan akses mengizinkan; semua tindakan dicatat.

### 5.4 Siswa

- Melihat profil sendiri.
- Melihat ujian yang ditugaskan kepadanya.
- Memulai ujian hanya pada jadwal yang valid.
- Melanjutkan attempt yang masih aktif.
- Menjawab soal dan men-submit.
- Melihat riwayat dan hasil yang telah dipublikasikan.
- Tidak dapat melihat kunci jawaban selama ujian.

### 5.5 Authorization

- Menu yang disembunyikan bukan pengganti backend authorization.
- Semua route dan aksi harus dilindungi middleware/policy.
- Siswa hanya boleh mengakses data dan attempt miliknya.
- Guru hanya boleh mengakses ujian sesuai ownership/assignment.
- Admin sekolah memiliki cakupan sekolah.
- Super Admin memiliki cakupan administrasi aplikasi.

## 6. Modul Fungsional

### M01 — Authentication

**Halaman:** Login, logout, profil, ubah password (opsional).

**Persyaratan:**
- Login username/email dan password sesuai implementasi.
- Password di-hash.
- Redirect sesuai role.
- Session diregenerasi setelah login dan dihapus saat logout.
- Pesan error tidak membocorkan informasi sensitif.
- Akun demo tampil pada halaman login hanya untuk environment prototype.

**Acceptance criteria:**
- Setiap role dapat login.
- Pengguna diarahkan ke dashboard sesuai role.
- Pengguna tidak dapat mengakses route role lain.
- Logout mengakhiri sesi.

### M02 — Dashboard

**Super Admin:** total admin, guru, siswa, kelas, ujian, aktivitas sistem.

**Admin:** statistik sekolah, jumlah ujian, peserta sedang mengerjakan, ujian mendatang, aktivitas, grafik ringkas.

**Guru:** bank soal, ujian dibuat, ujian mendatang/berlangsung/selesai, peserta, ringkasan nilai.

**Siswa:** identitas, ujian tersedia/mendatang, attempt aktif, riwayat, nilai yang dipublikasikan.

Dashboard memakai data database, bukan angka hardcoded.

### M03 — Master Data Sekolah

Entitas dan field minimal:

**Profil sekolah**
- nama, NPSN dummy, alamat, email, telepon, kepala sekolah, logo opsional.

**Tahun ajaran**
- nama/periode, tanggal mulai, tanggal selesai, status aktif.

**Semester**
- tahun ajaran, nama/jenis semester, tanggal mulai/selesai, status.

**Jurusan**
- kode, nama, status.

**Kelas**
- nama kelas, tingkat, jurusan opsional, wali kelas opsional, tahun ajaran, status.

**Mata pelajaran**
- kode, nama, tingkat, kelompok, status.

**Guru**
- user_id, NIP dummy, nama, email, telepon, status, assignment mata pelajaran/kelas.

**Siswa**
- user_id, NIS, NISN dummy, nama, jenis kelamin, tanggal lahir dummy, alamat dummy, class_id, status.

**Fungsi:** list, detail, create, edit, delete dengan konfirmasi, pencarian, filter, pagination, validasi, import/export simulasi bila feasible.

**Acceptance criteria:**
- CRUD berjalan dan tersimpan di SQLite.
- Validasi mencegah username, NIS, kode, dan relasi yang tidak valid.
- Data terkait tidak terhapus secara tidak sengaja; gunakan soft delete atau proteksi relasi sesuai kebutuhan.

### M04 — Bank Soal

**Kategori soal:** mata pelajaran, tingkat, materi/bab, kesulitan.

**Soal pilihan ganda:**
- kode soal;
- kategori/mata pelajaran;
- tingkat kesulitan (mudah/sedang/sulit);
- teks pertanyaan;
- gambar opsional;
- opsi A, B, C, D, E;
- satu kunci jawaban;
- bobot positif;
- status draft/aktif/nonaktif;
- pembuat soal.

**Aksi:** list, filter, search, create, edit, duplicate, preview, delete jika belum terpakai, import/export jika feasible.

**Aturan:**
- Opsi dan kunci tidak boleh kosong.
- Tepat satu opsi benar.
- Bobot harus lebih besar dari nol.
- Soal yang sudah dipakai pada ujian terbit tidak boleh mengubah snapshot ujian.
- Kunci jawaban tidak pernah dikirim ke browser saat siswa mengerjakan.

**Acceptance criteria:**
- Guru dapat membuat dan preview soal.
- Soal dapat dipilih ke ujian.
- Perubahan bank soal tidak mengubah snapshot ujian yang sudah dibuat/diterbitkan.

### M05 — Manajemen Ujian dan Jadwal

**Field ujian:**
- kode ujian;
- judul;
- deskripsi/instruksi;
- mata pelajaran;
- guru pembuat;
- tahun ajaran/semester;
- tanggal dan waktu mulai (`start_at`);
- tanggal dan waktu selesai (`end_at`);
- durasi menit;
- passing grade;
- jumlah soal;
- random question;
- random answer;
- token opsional (nonaktif secara default);
- status;
- hasil dipublikasikan atau belum.

**Pembuatan ujian:**
1. Isi informasi dasar.
2. Pilih mata pelajaran dan kelas.
3. Pilih peserta.
4. Pilih soal dan jumlah soal.
5. Atur urutan acak dan opsi acak.
6. Atur jadwal dan durasi.
7. Preview.
8. Simpan draft atau publish.

**Status tampilan:** Draft, Scheduled, Available, Ongoing, Completed, Cancelled. Status turunan waktu harus dihitung dari jadwal dan kondisi attempt; jangan hanya mengandalkan label manual.

**Aturan jadwal:**
- `end_at` harus lebih besar dari `start_at`.
- Durasi harus positif.
- Siswa tidak dapat memulai sebelum `start_at`.
- Siswa tidak dapat memulai pada/ setelah `end_at`.
- Siswa yang mulai tepat sebelum `end_at` hanya memperoleh waktu sampai batas `end_at`.
- Waktu akhir attempt = `min(started_at + duration, exam.end_at)`.
- Jadwal dan durasi disimpan menggunakan timezone aplikasi yang konsisten.

**Snapshot:**
- Ketika ujian dipublikasikan atau peserta/attempt disiapkan, simpan snapshot teks pertanyaan, opsi, bobot, urutan, dan pemetaan opsi yang diperlukan.
- Perubahan atau penghapusan bank soal setelah publish tidak mengubah ujian tersebut.

**Acceptance criteria:**
- Guru dapat membuat dan publish ujian.
- Peserta yang tidak ditugaskan tidak dapat mengakses ujian.
- Ujian tidak dapat dimulai di luar jadwal.
- Soal dan opsi stabil untuk ujian yang sudah dipublikasikan.

### M06 — Ruang Ujian Siswa

**Sebelum mulai:**
- judul, mata pelajaran, guru, jumlah soal, durasi, jadwal, instruksi;
- persetujuan aturan ujian;
- tombol mulai hanya aktif saat jadwal valid.

**Layout ruang ujian:**
- header: ujian, identitas siswa, timer, status koneksi/simpan;
- area utama: nomor, pertanyaan, gambar opsional, opsi A–E;
- navigasi nomor soal;
- indikator belum dijawab, dijawab, ditandai ragu-ragu, sedang dibuka;
- tombol sebelumnya, berikutnya, tandai ragu-ragu, selesai.

**Perilaku:**
- Siswa dapat berpindah nomor dan mengubah jawaban sebelum batas waktu.
- Penanda ragu-ragu tidak mengubah jawaban.
- Jawaban disimpan ke server setiap perubahan (dengan debounce hanya jika aman; jangan menimbulkan kehilangan data).
- UI menampilkan status Menyimpan, Tersimpan, atau Gagal menyimpan.
- Refresh tidak mengubah attempt, jawaban, urutan soal, atau `expires_at`.
- Jika koneksi putus, tampilkan peringatan dan antrekan lokal hanya sebagai mekanisme pemulihan sementara; server tetap sumber data utama.
- Saat koneksi pulih, kirim ulang dengan validasi attempt, waktu, dan versi jawaban.
- Setelah submit atau batas waktu, jawaban tidak dapat diubah.

**Timer:**
- Backend menyimpan `started_at` dan `expires_at`.
- Browser menampilkan countdown dari waktu akhir server yang disinkronkan.
- Jangan percaya jam perangkat pengguna.
- Setiap request simpan jawaban dan submit memvalidasi waktu di backend.
- Refresh/login ulang melanjutkan attempt yang sama.
- Timer habis menyebabkan submit otomatis atau finalisasi attempt oleh backend saat request berikutnya/worker terjadwal jika tersedia. Untuk prototype LAN tanpa queue eksternal, pastikan halaman aktif memanggil endpoint finalisasi dan semua akses attempt yang kedaluwarsa memicu finalisasi idempotent.

**Submit:**
- Konfirmasi menampilkan total, terjawab, belum dijawab, ragu-ragu.
- Submit manual menyimpan jawaban terakhir, menghitung skor, menandai attempt submitted, dan mencatat waktu.
- Submit otomatis berjalan saat `expires_at` tercapai.
- Proses submit harus idempotent; request ganda tidak membuat hasil ganda.

**Acceptance criteria:**
- Siswa hanya dapat memulai sesuai jadwal.
- Timer tetap benar setelah refresh.
- Jawaban tersimpan dan dapat dipulihkan.
- Siswa tidak dapat mengubah jawaban setelah submit/expired.
- Attempt tidak dapat digandakan.

### M07 — Monitoring

Admin/guru melihat:
- nama/NIS/kelas siswa;
- ujian;
- waktu mulai dan batas waktu;
- jumlah terjawab/belum dijawab;
- status Not Started, In Progress, Submitted, Time Expired;
- waktu aktivitas/simpan terakhir bila tersedia.

Aksi:
- melihat detail attempt dan progres;
- finalisasi administratif bila diperlukan;
- memberikan retake dengan alasan;
- semua tindakan administratif masuk audit log.

Untuk prototype gunakan polling berkala 5–10 detik. Tidak perlu WebSocket.

### M08 — Scoring dan Hasil

**Rumus:**

`nilai = (total bobot jawaban benar / total bobot seluruh soal) × 100`

- Jawaban salah/kosong = 0.
- Bobot soal positif.
- Jika total bobot nol, validasi ujian gagal dipublikasikan.
- Pembulatan nilai ditentukan konsisten (misalnya dua angka desimal).

**Hasil:**
- identitas siswa;
- ujian/mata pelajaran/kelas;
- mulai dan selesai;
- durasi aktual;
- total soal;
- benar, salah, kosong;
- nilai;
- status lulus/tidak lulus berdasarkan passing grade.

Hasil siswa hanya dapat dilihat setelah dipublikasikan. Kunci dan pembahasan tidak ditampilkan secara default.

**Acceptance criteria:**
- Nilai cocok dengan bobot dan jawaban.
- Submit berulang tidak mengubah hasil.
- Siswa tidak dapat melihat hasil sebelum publikasi.

### M09 — Laporan dan Analisis

Laporan minimal:
- rekap hasil per ujian;
- rekap per kelas;
- rekap per mata pelajaran;
- ranking;
- peserta belum mulai;
- peserta selesai;
- distribusi nilai;
- analisis butir soal: jumlah peserta, benar/salah, persentase benar, distribusi pilihan A–E.

Export Excel/PDF termasuk prioritas sekunder setelah alur ujian inti stabil.

### M10 — Pengaturan dan Audit

Pengaturan:
- identitas aplikasi/sekolah;
- logo;
- timezone;
- tahun ajaran aktif;
- passing grade default;
- pengaturan akun.

Audit log minimal:
- login/logout;
- create/update/delete data penting;
- pembuatan/publikasi/perubahan jadwal ujian;
- mulai dan submit attempt;
- perubahan jawaban;
- retake;
- tindakan administratif.

## 7. Data dan Model Konseptual

Gunakan migration Laravel dan relasi Eloquent. Nama tabel dapat disesuaikan setelah inspeksi project, tetapi cakupan entitas minimal:

1. `users`
2. `school_profiles`
3. `academic_years`
4. `semesters`
5. `departments`
6. `classes`
7. `subjects`
8. `teachers`
9. `students`
10. `teacher_subjects` atau tabel pivot sejenis
11. `class_subjects` atau tabel pivot sejenis
12. `question_categories`
13. `questions`
14. `question_options`
15. `exams`
16. `exam_questions`
17. `exam_participants`
18. `exam_attempts`
19. `exam_attempt_questions`
20. `exam_answers`
21. `audit_logs`

Relasi konseptual:
- User memiliki role dan dapat terhubung ke profil guru/siswa.
- Siswa berada pada satu kelas aktif.
- Kelas berada pada tahun ajaran.
- Guru mengampu satu/lebih mata pelajaran dan kelas.
- Soal berada pada kategori/mata pelajaran.
- Ujian memiliki guru, mata pelajaran, peserta, dan snapshot soal.
- Peserta ujian memiliki maksimal satu attempt aktif/valid.
- Attempt memiliki snapshot urutan soal dan jawaban siswa.
- Hasil dihitung dari snapshot attempt, bukan bank soal terbaru.

Gunakan foreign key, unique constraints, index untuk pencarian/join penting, timestamps, dan soft delete bila sesuai.

## 8. Data Dummy dan Seeder

Gunakan Faker dengan locale Indonesia bila tersedia. Seeder harus reproducible dengan seed tetap agar hasil demo konsisten.

Jumlah awal yang disarankan:
- 1 Super Admin;
- 2 Admin;
- 15 Guru;
- 300 Siswa;
- 12 Kelas;
- 4 Jurusan (sesuaikan jenjang);
- 10 Mata Pelajaran;
- 30 Kategori;
- 500 Soal;
- 8 Ujian;
- data peserta dan riwayat attempt bervariasi.

Akun demo:
| Role | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `password` |
| Admin | `admin` | `password` |
| Guru | `guru01` | `password` |
| Guru | `guru02` | `password` |
| Siswa | `siswa01` | `password` |
| Siswa | `siswa02` | `password` |

Akun ini hanya untuk environment prototype. Jangan gunakan kredensial tersebut di produksi.

Soal dummy harus masuk akal dan memiliki opsi serta kunci yang konsisten. Jangan membuat teks pertanyaan acak yang tidak berkaitan dengan opsi.

Seeder harus aman dijalankan ulang melalui mekanisme reset database yang eksplisit; jangan menjalankan reset secara otomatis pada setiap start aplikasi.

## 9. UI/UX

### 9.0 Ketentuan asset theme (WAJIB)

Theme AdminLTE 3 sudah disediakan pengguna pada:

`public/theme/adminLTE`

Ketentuan:
1. Inspeksi struktur folder sebelum menulis referensi CSS/JS.
2. Gunakan asset lokal dari folder tersebut untuk layout aplikasi.
3. Gunakan helper Laravel `asset()` dengan path dan nama file yang benar-benar ditemukan.
4. Jangan mengunduh ulang, memasang theme alternatif, mengganti nama folder, memindahkan, menghapus, atau menimpa asset theme.
5. Jangan menggunakan CDN untuk asset inti AdminLTE/plugin yang sudah tersedia lokal.
6. Jika plugin yang dibutuhkan tidak tersedia, dokumentasikan gap dan pilih solusi kompatibel tanpa merusak theme.
7. Jika folder tidak ditemukan, catat blocker dan laporkan kepada pengguna; jangan mengunduh theme secara otomatis.

## 9.1 Arah visual


### 9.1 Arah visual
- Modern, bersih, profesional, berorientasi pendidikan.
- Branding generik CBT School.
- AdminLTE 3 sebagai template utama.
- Warna utama biru, latar putih/abu-abu, warna status hijau/kuning/merah.
- Gunakan komponen, spacing, tabel, form, modal, badge, dan alert yang konsisten.
- Hindari halaman admin yang terlalu padat.

### 9.2 Layout
Admin/guru:
- navbar;
- sidebar berdasarkan role;
- breadcrumb;
- konten;
- footer.

Siswa:
- dashboard sederhana;
- halaman ruang ujian memiliki layout khusus tanpa sidebar admin.

### 9.3 Responsif
Optimalkan ruang ujian untuk desktop/laptop laboratorium. Pastikan tablet dan layar kecil tetap dapat digunakan tanpa layout rusak.

### 9.4 Status antarmuka
Setiap aksi harus memiliki loading, success, validation error, empty state, dan failure state yang jelas. Tombol utama tidak boleh inert.

## 10. Persyaratan Teknis dan Keamanan

- Gunakan Laravel conventions.
- Gunakan validation/Form Request.
- Gunakan middleware dan Policy.
- Gunakan CSRF protection.
- Escape output untuk mencegah XSS.
- Hash password.
- Validasi ownership untuk setiap attempt dan jawaban.
- Gunakan database transaction untuk mulai attempt, menyimpan jawaban penting, submit, dan scoring.
- Submit harus idempotent.
- Cegah duplicate active attempt dengan unique constraint/transaction/locking yang sesuai SQLite.
- Jangan expose answer key pada HTML, JavaScript, atau response endpoint ruang ujian.
- Backend menjadi sumber kebenaran untuk jadwal, timer, status, dan nilai.
- Jangan mengklaim browser mampu mencegah screenshot, tab switching, atau aplikasi lain.
- Simpan asset yang dibutuhkan secara lokal untuk skenario LAN offline.
- Jangan menambahkan dependency eksternal tanpa alasan dan dokumentasi.

## 11. Arsitektur Kode

Pisahkan tanggung jawab. Gunakan Service class bila logika bisnis perlu dipakai lintas controller.

Service yang disarankan:
- `ExamService`
- `ExamSessionService`
- `AnswerService`
- `ScoringService`
- `QuestionService`
- `MonitoringService`
- `ReportService`

Gunakan:
- Models dan relationships;
- Controllers tipis;
- Form Requests;
- Policies;
- Middleware;
- Blade components/partials;
- migrations dan seeders;
- Feature tests dan Unit tests.

Jangan membuat service berlebihan untuk operasi CRUD sederhana.

## 12. Pengujian

### Authentication & authorization
- login berhasil/gagal;
- redirect role;
- route terlindungi;
- siswa tidak dapat mengakses attempt milik siswa lain;
- guru tidak dapat mengakses ujian di luar kewenangannya.

### Master data
- CRUD dan validasi;
- unique username/NIS/kode;
- relasi kelas dan siswa.

### Bank soal
- validasi opsi dan kunci;
- preview;
- snapshot tidak berubah setelah bank soal diedit.

### Exam scheduling
- belum mulai ditolak;
- selama jadwal dapat mulai;
- setelah jadwal ditutup ditolak;
- peserta yang tidak ditugaskan ditolak;
- batas attempt dihitung dengan benar.

### Exam engine
- attempt dibuat satu kali;
- timer tidak reset setelah refresh;
- jawaban tersimpan;
- jawaban dapat diubah sebelum expired;
- jawaban ditolak setelah expired/submitted;
- random order stabil;
- submit manual dan otomatis;
- request submit ganda idempotent.

### Scoring
- benar, salah, kosong;
- bobot berbeda;
- passing grade;
- hasil tidak berubah karena edit bank soal.

### Monitoring dan laporan
- status peserta benar;
- progres jawaban benar;
- publikasi hasil;
- privasi hasil siswa.

## 13. Skenario Demo End-to-End

1. Login sebagai Admin.
2. Lihat dashboard dan data sekolah.
3. Tambahkan atau edit siswa/guru/kelas.
4. Logout.
5. Login sebagai Guru.
6. Buat beberapa soal pilihan ganda.
7. Buat ujian, pilih soal, peserta, jadwal, dan durasi.
8. Publish ujian.
9. Login sebagai Siswa yang ditugaskan.
10. Pastikan ujian belum dapat dimulai sebelum jadwal.
11. Mulai ujian saat jadwal tersedia.
12. Jawab beberapa soal dan tandai satu soal ragu-ragu.
13. Refresh browser dan pastikan timer serta jawaban tetap ada.
14. Submit ujian.
15. Login sebagai Guru dan lihat monitoring serta hasil.
16. Publish hasil.
17. Login sebagai Siswa dan lihat nilai yang dipublikasikan.

Sediakan data demo dan instruksi untuk menguji batas waktu tanpa menunggu lama, misalnya ujian demo berdurasi singkat atau konfigurasi jadwal khusus. Jangan mengubah aturan bisnis inti demi demo.

## 14. Acceptance Criteria Global

Prototype diterima jika:
- semua role dapat login dan hak aksesnya benar;
- seluruh master data utama dapat dikelola;
- bank soal pilihan ganda berfungsi;
- guru dapat membuat dan menjadwalkan ujian;
- peserta ditentukan secara individual;
- siswa hanya dapat memulai sesuai jadwal;
- timer server-based;
- refresh tidak mereset attempt;
- autosave tersimpan di SQLite;
- submit manual dan otomatis berfungsi;
- penilaian otomatis akurat;
- monitoring dan laporan tampil;
- hasil hanya terlihat setelah publikasi;
- seluruh tombol dan navigasi utama berfungsi;
- test kritis lulus;
- README dan dokumentasi teknis diperbarui.

## 15. Deliverables

- Source code Laravel.
- AdminLTE 3 integration.
- Migrations dan seeders.
- Akun demo.
- Multi-role authentication.
- Modul master data.
- Bank soal.
- Manajemen ujian.
- Ruang ujian.
- Timer dan autosave.
- Monitoring.
- Scoring dan laporan.
- Feature/Unit tests.
- `README.md`, `AGENTS.md`, `IMPLEMENTATION_PLAN.md`, `PROGRESS.md`.
- Petunjuk instalasi dan menjalankan di LAN.
