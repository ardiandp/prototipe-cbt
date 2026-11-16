# IMPLEMENTATION PLAN — CBT School

> Kerjakan berurutan. Satu task per iterasi. Setiap task wajib diuji, didokumentasikan, lalu di-commit.

## Status Legend
- `[ ]` Belum dikerjakan
- `[~]` Sedang dikerjakan
- `[x]` Selesai dan sudah diverifikasi/di-commit
- `[!]` Terblokir

## Aturan eksekusi
1. Baca README, PRD, AGENTS, dan PROGRESS.
2. Pilih task pertama yang belum selesai.
3. Implementasikan hanya task tersebut.
4. Jalankan acceptance checks dan tests.
5. Update PROGRESS dan status task.
6. Commit task.
7. Laporkan hasil dan berhenti.

---

## PHASE 0 — Inspection & Project Documentation

### P0-T01 — Inspect existing project
- [x] Identifikasi apakah project Laravel sudah tersedia.
- [x] Catat versi PHP, Laravel, Composer, Node/NPM jika digunakan.
- [x] Periksa dependency dan struktur folder.
- [x] Periksa keberadaan asset AdminLTE 3.
- [x] Periksa status Git dan perubahan yang belum di-commit.
- [x] Jangan menghapus atau mengubah file pengguna.

**Acceptance:** kondisi project dan risiko tercatat di `PROGRESS.md`.

### P0-T02 — Initialize project instructions
- [x] Pastikan README.md menjadi entry point.
- [x] Buat/review PRD.md.
- [x] Buat/review AGENTS.md.
- [x] Buat/review IMPLEMENTATION_PLAN.md.
- [x] Buat/review PROGRESS.md.
- [x] Pastikan keputusan produk konsisten.

**Acceptance:** dokumentasi lengkap dan tidak saling bertentangan.

### P0-T03 — Documentation commit
- [x] Review diff.
- [x] Pastikan tidak ada secret.
- [x] Commit dokumentasi.

**Commit:** `docs: initialize CBT School implementation plan`

---

## PHASE 1 — Foundation

### P1-T01 — Laravel environment and SQLite
- [x] Validasi versi PHP dan Laravel.
- [x] Konfigurasi SQLite untuk prototype.
- [x] Siapkan `.env.example` tanpa secret.
- [x] Pastikan migration command dapat dijalankan.
- [x] Dokumentasikan setup lokal.

**Acceptance:** aplikasi dapat boot dan koneksi SQLite berhasil.

### P1-T02 — Integrasi AdminLTE 3 dari asset yang sudah disediakan
- [x] Pastikan folder `public/theme/adminLTE` tersedia.
- [x] Inspeksi struktur folder: CSS, JavaScript, plugins, image, dan font.
- [x] Catat nama dan path asset aktual.
- [x] Integrasikan asset lokal ke layout Blade menggunakan helper `asset()`.
- [x] Jangan mengunduh ulang atau memasang theme alternatif.
- [x] Jangan memindahkan, mengganti nama, menghapus, atau menimpa asset yang tersedia.
- [x] Buat layout backend, navbar, sidebar, footer, dan breadcrumb.
- [x] Buat reusable alert, card, form, table, dan modal.
- [x] Pastikan asset inti dapat dimuat tanpa CDN.
- [x] Jika folder tidak ditemukan, hentikan task integrasi dan laporkan blocker.
- [x] Buat layout backend.
- [x] Buat navbar, sidebar, footer, breadcrumb.
- [x] Buat reusable alert, card, form, table, modal.
- [x] Pastikan asset tidak bergantung pada CDN saat demo LAN.

**Acceptance:** halaman contoh menggunakan asset AdminLTE 3 dari `public/theme/adminLTE`; tidak ada theme yang diunduh atau diganti.

### P1-T03 — Route and view foundation
- [x] Susun route groups.
- [x] Buat halaman error/unauthorized/404.
- [x] Buat halaman login shell.
- [x] Terapkan naming convention.
- [x] Tambahkan smoke tests.

**Acceptance:** route utama dapat diakses sesuai status autentikasi.

---

## PHASE 2 — Authentication & Roles

### P2-T01 — User and role model
- [x] Implementasikan users dan role.
- [x] Buat migration, model, factory/seed dasar.
- [x] Terapkan password hashing.

### P2-T02 — Login and logout
- [x] Form login.
- [x] Validasi credential.
- [x] Session regeneration.
- [x] Logout dan invalidasi session.
- [x] Demo account seeder.

### P2-T03 — Authorization
- [x] Role middleware.
- [x] Policies/authorization untuk resource.
- [x] Redirect dashboard berdasarkan role.
- [x] Sidebar/menu berdasarkan role.
- [x] Feature tests akses role.

**Acceptance Phase 2:** empat role dapat login dan tidak dapat mengakses fungsi di luar kewenangannya.

---

## PHASE 3 — School Master Data

### P3-T01 — School profile and academic period
- [x] Profil sekolah.
- [x] Tahun ajaran.
- [x] Semester.
- [x] Validasi periode aktif.

### P3-T02 — Department, class, subject
- [x] Jurusan.
- [x] Kelas.
- [x] Mata pelajaran.
- [x] Relasi dan validasi.

### P3-T03 — Teacher management
- [x] Profil guru.
- [x] Relasi user.
- [x] Assignment mata pelajaran/kelas.
- [x] CRUD, filter, pagination.

### P3-T04 — Student management
- [x] Profil siswa.
- [x] Relasi user dan kelas.
- [x] CRUD, filter, pagination.
- [x] Unique NIS dan username.

### P3-T05 — Master data seeders and dashboards
- [x] Faker/factory/seeders reproducible.
- [x] Dashboard data-driven per role.
- [x] Tests CRUD dan relasi.

**Acceptance Phase 3:** admin dapat mengelola data sekolah dan dashboard menampilkan data aktual dari SQLite. (Selesai dan diverifikasi)

---

## PHASE 4 — Question Bank

### P4-T01 — Category and question schema
- [x] Migration kategori, soal, opsi.
- [x] Relasi dan index.
- [x] Validasi kunci dan bobot.

### P4-T02 — Question CRUD
- [x] List, search, filter, pagination.
- [x] Create/edit/delete.
- [x] Duplicate.
- [x] Preview.

### P4-T03 — Question content and media
- [x] Editor pertanyaan.
- [x] Opsi A–E.
- [x] Upload gambar lokal opsional.
- [x] Validasi file.

### P4-T04 — Question seeding
- [x] Seed kategori.
- [x] Seed soal dummy yang koheren.
- [x] Reproducible seed.

**Acceptance Phase 4:** guru dapat mengelola dan preview soal pilihan ganda. (Selesai dan diverifikasi)

---

## PHASE 5 — Exam Management & Scheduling

### P5-T01 — Exam schema and CRUD
- [x] Migration ujian.
- [x] Form create/edit.
- [x] Ownership dan authorization.
- [x] Validasi tanggal/durasi.

### P5-T02 — Question selection and snapshots
- [x] Pilih soal.
- [x] Atur jumlah soal.
- [x] Simpan snapshot pertanyaan, opsi, bobot.
- [x] Lindungi snapshot dari perubahan bank soal.

### P5-T03 — Participant assignment
- [x] Pilih kelas/peserta.
- [x] Simpan peserta ujian.
- [x] Validasi peserta unik.
- [x] Daftar peserta.

### P5-T04 — Schedule and publication
- [x] `start_at` dan `end_at`.
- [x] Durasi attempt.
- [x] Random question/answer.
- [x] Draft, publish, cancel.
- [x] Preview ujian.
- [x] Tests jadwal dan akses peserta.

**Acceptance Phase 5:** guru dapat membuat ujian terjadwal dengan snapshot soal dan peserta yang benar.

---

## PHASE 6 — Exam Engine

### P6-T01 — Attempt lifecycle
- [x] Model dan migration attempt.
- [x] Mulai attempt individual.
- [x] Cegah duplicate attempt.
- [x] Hitung `expires_at` dengan batas `exam.end_at`.
- [x] Resume attempt.

### P6-T02 — Attempt question ordering
- [x] Simpan urutan soal per attempt.
- [x] Randomisasi stabil.
- [x] Randomisasi opsi dengan pemetaan yang aman.
- [x] Jangan expose kunci jawaban.

### P6-T03 — Exam room UI
- [x] Halaman persiapan.
- [x] Layout ruang ujian.
- [x] Navigasi nomor.
- [x] Pilihan jawaban.
- [x] Penanda ragu-ragu.
- [x] Indikator status jawaban.

### P6-T04 — Server timer
- [x] Timer berbasis `expires_at`.
- [x] Sinkronisasi waktu server.
- [x] Validasi waktu di backend.
- [x] Refresh tidak mereset timer.
- [x] Expired attempt difinalisasi secara idempotent.

### P6-T05 — Answer autosave and recovery
- [x] Endpoint simpan jawaban.
- [x] Validasi ownership dan waktu.
- [x] Simpan ke SQLite.
- [x] Status saving/saved/failed.
- [x] Recovery setelah refresh/koneksi pulih.
- [x] Cegah request lama menimpa jawaban terbaru.

### P6-T06 — Manual and automatic submit
- [x] Konfirmasi submit.
- [x] Ringkasan jawaban.
- [x] Submit manual.
- [x] Submit otomatis.
- [x] Idempotency.
- [x] Tests lifecycle lengkap.

**Acceptance Phase 6:** siswa dapat menyelesaikan ujian; timer, jawaban, dan attempt tetap konsisten setelah refresh.

---

## PHASE 7 — Monitoring & Scoring

### P7-T01 — Monitoring dashboard
- [x] Daftar peserta dan status.
- [x] Progress jawaban.
- [x] Polling berkala.
- [x] Detail attempt.

### P7-T02 — Administrative actions
- [ ] Finalisasi administratif.
- [ ] Retake authorization.
- [ ] Alasan tindakan wajib.
- [ ] Audit log.

### P7-T03 — Scoring service
- [ ] Scoring berdasarkan snapshot.
- [ ] Bobot soal.
- [ ] Jawaban benar/salah/kosong.
- [ ] Passing grade.
- [ ] Hasil idempotent.
- [ ] Unit tests scoring.

### P7-T04 — Result publication
- [ ] Hasil guru/admin.
- [ ] Publikasi/sembunyikan hasil.
- [ ] Hasil siswa hanya setelah publish.
- [ ] Detail hasil.

**Acceptance Phase 7:** monitoring akurat dan nilai dihitung benar.

---

## PHASE 8 — Reports & Audit

### P8-T01 — Result reports
- [ ] Rekap ujian.
- [ ] Rekap kelas/mapel.
- [ ] Ranking.
- [ ] Distribusi nilai.

### P8-T02 — Item analysis
- [ ] Persentase benar/salah.
- [ ] Distribusi pilihan A–E.
- [ ] Jumlah peserta per soal.

### P8-T03 — Export
- [ ] Export CSV/Excel jika dependency sesuai.
- [ ] PDF jika feasible.
- [ ] Validasi filter dan data export.

### P8-T04 — Audit log
- [ ] Catat aktivitas penting.
- [ ] Filter audit log.
- [ ] Proteksi akses.

**Acceptance Phase 8:** laporan dan audit dapat digunakan untuk demo.

---

## PHASE 9 — Demo Readiness & LAN

### P9-T01 — Demo dataset and scenario
- [ ] Data demo konsisten.
- [ ] Sediakan ujian demo dengan jadwal yang mudah diuji.
- [ ] Sediakan instruksi demo end-to-end.
- [ ] Sediakan cara reset data demo yang eksplisit.

### P9-T02 — LAN deployment documentation
- [ ] Panduan menjalankan server lokal.
- [ ] Panduan akses dari komputer klien.
- [ ] Catatan firewall dan IP lokal.
- [ ] Asset lokal tanpa CDN.
- [ ] Catatan keterbatasan prototype.

### P9-T03 — Full regression and final review
- [ ] Test seluruh role.
- [ ] Test seluruh alur ujian.
- [ ] Test refresh/resume.
- [ ] Test waktu habis.
- [ ] Test scoring dan publikasi.
- [ ] Periksa responsive UI.
- [ ] Periksa Git status dan dokumentasi.

**Acceptance Phase 9:** prototype siap didemonstrasikan melalui LAN dengan seluruh alur utama berjalan.

---

## Definition of Done per Task

Sebuah task hanya boleh ditandai `[x]` jika:
1. implementasi sesuai scope task;
2. acceptance criteria task terpenuhi;
3. test relevan dijalankan;
4. tidak ada regression yang diketahui;
5. dokumentasi progres diperbarui;
6. Git commit berhasil dibuat.

Jika test gagal, tandai `[!]` atau biarkan belum selesai, dokumentasikan error, dan jangan mengklaim task selesai.
