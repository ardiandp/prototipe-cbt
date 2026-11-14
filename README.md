# CBT School — OpenCode Entry Point

**Dokumentasi revisi v1.1:** menggunakan asset AdminLTE 3 lokal pada `public/theme/adminLTE`.

> **Baca file ini terlebih dahulu setiap kali memulai pekerjaan.**  
> README.md adalah pintu masuk dan pemberi perintah utama untuk AI coding agent (OpenCode).

## 1. Instruksi utama untuk OpenCode

Anda bertugas mengembangkan **CBT School**, aplikasi Computer Based Test berbasis web untuk sekolah. Ikuti urutan berikut sebelum menulis atau mengubah kode:

1. Baca `README.md` sampai selesai.
2. Baca `PRD.md` untuk memahami kebutuhan produk, aturan bisnis, role, alur, dan acceptance criteria.
3. Baca `AGENTS.md` untuk aturan teknis, kualitas kode, keamanan, dan Git.
4. Baca `IMPLEMENTATION_PLAN.md` untuk mengetahui task aktif dan urutan pengerjaan.
5. Baca `PROGRESS.md` untuk mengetahui pekerjaan terakhir, hasil test, dan task berikutnya.
6. Inspeksi project aktual: versi Laravel/PHP, struktur folder, dependency, asset AdminLTE 3, konfigurasi, dan kondisi Git.
7. Kerjakan **hanya satu task aktif** dari `IMPLEMENTATION_PLAN.md`. Jangan melompat fase atau mengimplementasikan seluruh aplikasi sekaligus.
8. Jalankan test dan pemeriksaan yang relevan.
9. Perbarui `PROGRESS.md` dan tandai task selesai di `IMPLEMENTATION_PLAN.md` hanya jika acceptance criteria task terpenuhi.
10. Buat **Git commit setiap selesai mengerjakan satu task** sesuai aturan di `AGENTS.md`.
11. Berhenti setelah task selesai dan laporkan hasil serta task berikutnya. Jangan otomatis melanjutkan task berikutnya tanpa instruksi baru dari pengguna.

## 2. Perintah kerja pertama

Pada eksekusi pertama, jangan langsung membangun fitur aplikasi. Lakukan inspeksi dan siapkan dokumentasi kerja:

- Periksa kondisi project, versi Laravel/PHP, dependency, dan Git.
- **Gunakan theme AdminLTE 3 yang sudah disediakan di `public/theme/adminLTE`.**
- Inspeksi struktur folder tersebut untuk menemukan CSS, JavaScript, plugin, image, dan font.
- Gunakan asset lokal yang benar-benar tersedia; jangan mengasumsikan nama file sebelum memeriksanya.
- Jangan mengunduh ulang, memasang theme alternatif, mengganti, memindahkan, menghapus, atau menimpa asset AdminLTE yang sudah disediakan.
- Gunakan helper Laravel `asset()` dengan path aktual, misalnya `asset('theme/adminLTE/dist/...')`.
- Jangan menggunakan CDN untuk asset inti yang sudah tersedia secara lokal.
- Jika folder tidak ditemukan, catat sebagai blocker dan laporkan; jangan memasang theme lain otomatis.
- Jangan menghapus atau menimpa perubahan pengguna.
- Validasi konsistensi dokumen ini dengan project aktual.
- Buat/review `AGENTS.md`, `IMPLEMENTATION_PLAN.md`, dan `PROGRESS.md`.
- Catat asumsi, risiko, dan ketidaksesuaian project pada `PROGRESS.md`.
- Prototype menggunakan SQLite, bukan database produksi.
- Setelah dokumentasi awal selesai, jalankan pemeriksaan dan commit dengan pesan `docs: initialize CBT School implementation plan`.

Jika project belum ada, siapkan dokumentasi dan rencana saja. Implementasi kode dimulai pada task berikutnya.

## 3. Ringkasan produk

**CBT School** adalah prototype fungsional aplikasi ujian sekolah berbasis web yang dirancang untuk berjalan di jaringan lokal (LAN). Admin mengelola data sekolah dan ujian; guru mengelola bank soal dan ujian; siswa mengikuti ujian individual berdasarkan jadwal.

### Keputusan produk yang sudah ditetapkan

| Area | Keputusan |
|---|---|
| Branding | Generic: CBT School |
| Target | Sekolah (SD/SMP/SMA/SMK) |
| Akses | Web melalui LAN |
| Backend | Laravel |
| UI | AdminLTE 3 |
| Frontend | Blade; Livewire hanya jika cocok dengan project |
| Database prototype | SQLite |
| Data awal | Faker + seeder yang reproducible |
| Jenis soal | Pilihan ganda A–E |
| Mekanisme ujian | Scheduled |
| Pelaksanaan | Individual; setiap siswa memulai attempt sendiri |
| Timer | Berdasarkan waktu server |
| Penyimpanan jawaban | Autosave ke SQLite |
| Penilaian | Otomatis |
| Multi-role | Super Admin, Admin Sekolah, Guru, Siswa |
| Produksi | Tidak termasuk dalam scope prototype |

## 4. Aturan penting

- Prototype harus **benar-benar interaktif**, bukan sekadar halaman statis.
- SQLite adalah database prototype persisten, bukan penyimpanan sementara di browser.
- Jangan menghubungkan database, layanan, API, atau data produksi.
- Seluruh proses utama harus dapat didemokan dari login hingga hasil ujian.
- Timer dan validasi waktu wajib dilakukan di backend.
- Jangan pernah mengirim kunci jawaban ke browser selama ujian berlangsung.
- Snapshot soal dan opsi dibuat saat ujian dipublikasikan/dibentuk untuk peserta agar perubahan bank soal tidak mengubah ujian yang sedang berjalan atau riwayat.
- Jangan melakukan `git push` kecuali diminta.
- Jangan melakukan commit jika test penting gagal tanpa mendokumentasikan kegagalan dan alasan pengecualian.
- Jangan menghapus, me-reset, atau menimpa data/perubahan pengguna yang sudah ada tanpa izin.

## 5. Kriteria hasil akhir

Prototype dianggap selesai apabila seluruh role dapat digunakan, master data dan bank soal dapat dikelola, ujian dapat dijadwalkan, siswa dapat mengerjakan ujian dengan timer dan autosave, refresh tidak mereset attempt, submit dan penilaian otomatis berjalan, monitoring dan laporan tersedia, serta test penting lulus.

## 6. Format laporan setiap task

Setiap selesai task, laporkan:

1. Task ID dan nama task.
2. Ringkasan implementasi.
3. File penting yang dibuat/diubah.
4. Test/perintah pemeriksaan yang dijalankan dan hasilnya.
5. Acceptance criteria yang terpenuhi/belum terpenuhi.
6. Commit hash dan pesan commit.
7. Risiko atau pekerjaan tersisa.
8. Task berikutnya (tanpa langsung mengerjakannya).

**Mulai sekarang: baca seluruh dokumen, inspeksi project, lalu kerjakan hanya task pertama yang belum selesai.**
