# AGENTS.md — OpenCode Rules for CBT School

## 1. Wajib dibaca
Sebelum bekerja, baca:
1. `README.md`
2. `PRD.md`
3. `AGENTS.md`
4. `IMPLEMENTATION_PLAN.md`
5. `PROGRESS.md`

## 2. Prinsip kerja
- Kerjakan hanya satu task aktif pada satu waktu.
- Ikuti urutan task di `IMPLEMENTATION_PLAN.md`.
- Jangan mengimplementasikan fase berikutnya sebelum task aktif selesai dan diverifikasi.
- Jangan mengarang struktur project; inspeksi dahulu.
- Pertahankan perubahan pengguna yang sudah ada.
- Jika menemukan konflik antara PRD dan kondisi project, dokumentasikan dan pilih solusi paling aman; jangan menghapus fitur tanpa persetujuan.
- Ajukan pertanyaan hanya jika ada keputusan yang benar-benar menghalangi implementasi. Jika tidak, gunakan asumsi yang paling konservatif dan catat.

## 3. Stack dan batasan
- Laravel sesuai versi project/kompatibilitas PHP.
- **Wajib menggunakan AdminLTE 3 yang sudah disediakan pada `public/theme/adminLTE`.**
- Inspeksi struktur folder sebelum menulis referensi CSS/JS.
- Gunakan asset dengan helper `asset()` dan path aktual yang ditemukan.
- Jangan mengunduh ulang, mengganti, memindahkan, menghapus, atau menimpa asset theme.
- Jangan menggunakan CDN untuk asset inti yang tersedia secara lokal.
- Jika folder tidak ditemukan, dokumentasikan sebagai blocker; jangan memasang theme alternatif tanpa izin.

- Laravel sesuai versi project/kompatibilitas PHP.
- AdminLTE 3 adalah template wajib.
- Blade sebagai rendering utama. Livewire boleh digunakan jika dependency sudah ada atau manfaatnya jelas.
- SQLite untuk prototype.
- Faker dan Laravel Seeder untuk data dummy.
- Semua asset yang dibutuhkan saat demo LAN sebaiknya disimpan lokal.
- Jangan menambahkan database produksi, API eksternal, atau layanan cloud.
- Jangan mengganti AdminLTE 3 dengan template lain.

## 4. Kualitas kode
- Layout Blade harus memuat CSS/JS dari `public/theme/adminLTE` sesuai struktur aktual.
- Buat partial/layout reusable untuk navbar, sidebar, footer, dan pemanggilan asset.
- Hindari duplikasi pemanggilan asset.

- Ikuti Laravel conventions dan PSR yang relevan.
- Controller tetap tipis.
- Validasi input di Form Request atau mekanisme Laravel yang sesuai.
- Gunakan Policy/Middleware untuk authorization.
- Gunakan Eloquent relationships dan eager loading bila diperlukan.
- Gunakan transaction untuk operasi bisnis kritis.
- Gunakan nama route, class, method, dan variabel yang konsisten.
- Hindari duplikasi view, query, dan business logic.
- Jangan hardcode data statistik dashboard jika data tersedia di database.
- Hindari N+1 query.
- Sediakan empty, loading, success, validation-error, dan failure state.
- Jangan meninggalkan tombol utama tanpa aksi.

## 5. Aturan khusus CBT
- Waktu server adalah sumber kebenaran.
- Simpan `started_at` dan `expires_at` di database.
- `expires_at = min(started_at + duration, exam.end_at)`.
- Refresh tidak boleh membuat attempt baru atau mereset timer.
- Siswa hanya dapat mengakses attempt miliknya.
- Simpan snapshot pertanyaan, opsi, bobot, dan urutan untuk ujian yang dipublikasikan/attempt.
- Jangan mengirim kunci jawaban ke client selama ujian.
- Jawaban hanya dapat diubah jika attempt masih aktif dan waktu belum habis.
- Submit harus idempotent.
- Finalisasi attempt yang kedaluwarsa harus dapat dilakukan tanpa bergantung pada browser tetap terbuka.
- Scoring harus menggunakan snapshot attempt, bukan data bank soal terbaru.
- Semua tindakan retake/admin finalization dicatat.
- Jangan mengklaim tab detection sebagai anti-cheating yang kuat.

## 6. Test wajib
- Jalankan test terkait setelah perubahan.
- Tambahkan/ubah test untuk business rule baru.
- Untuk perubahan UI, periksa route, render, validasi, dan interaksi yang relevan.
- Untuk timer/attempt/answer/scoring, wajib ada feature test.
- Jangan menonaktifkan test hanya agar suite lulus.
- Jika test gagal karena environment, dokumentasikan command, error, dan alasan.

## 7. Git: WAJIB COMMIT SETIAP TASK
Setiap selesai satu task yang terdefinisi di `IMPLEMENTATION_PLAN.md`:
1. Periksa `git status`.
2. Review diff.
3. Jalankan test/lint yang relevan.
4. Pastikan tidak ada secret, `.env`, database pribadi, file build sementara, atau data sensitif yang ikut.
5. Update `PROGRESS.md`.
6. Tandai task selesai di `IMPLEMENTATION_PLAN.md` hanya jika acceptance criteria terpenuhi.
7. Stage hanya file yang terkait task.
8. Buat satu commit untuk task tersebut.

Format commit:
- `docs: ...`
- `chore: ...`
- `feat: ...`
- `fix: ...`
- `test: ...`
- `refactor: ...`

Contoh:
- `docs: initialize CBT School project instructions`
- `chore: configure Laravel and SQLite prototype`
- `feat: add role-based authentication`
- `feat: implement school master data`
- `feat: implement question bank`
- `feat: implement scheduled exams`
- `feat: implement student exam engine`
- `feat: add answer autosave and timer`
- `feat: implement scoring and monitoring`
- `test: cover examination lifecycle`

Jangan:
- melakukan `git push` tanpa instruksi pengguna;
- melakukan force push;
- mengubah history commit;
- melakukan commit jika test penting gagal tanpa mencatat pengecualian di `PROGRESS.md`;
- memasukkan `.env` atau credential ke Git;
- menggabungkan banyak task yang tidak berkaitan ke satu commit.

Jika repository belum memiliki Git, periksa kondisi folder dan minta/ikuti instruksi inisialisasi yang aman. Jangan menginisialisasi ulang repository yang sudah ada.

## 8. Dokumentasi progres
Setelah setiap task:
- update status task;
- catat perubahan file;
- catat test dan hasil;
- catat commit hash;
- catat risiko/known issue;
- tulis task berikutnya.

## 9. Laporan akhir task
Berikan ringkasan singkat:
- Task ID/nama;
- apa yang dibuat;
- test yang dijalankan;
- acceptance criteria;
- commit hash/pesan;
- task berikutnya.

Setelah satu task selesai, berhenti dan tunggu instruksi berikutnya.
