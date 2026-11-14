# PROGRESS — CBT School

Dokumen ini adalah catatan status aktual implementasi. AI wajib memperbaruinya setiap selesai task.

## Project Status

| Item | Status |
|---|---|
| Project inspection | Selesai |
| Dokumentasi awal | Selesai |
| Laravel foundation | Belum dimulai |
| Authentication & roles | Belum dimulai |
| Master data | Belum dimulai |
| Question bank | Belum dimulai |
| Exam management | Belum dimulai |
| Exam engine | Belum dimulai |
| Monitoring & scoring | Belum dimulai |
| Reporting | Belum dimulai |
| LAN demo readiness | Belum dimulai |

## Current Task

**Task ID:** P1-T01  
**Task:** Laravel environment and SQLite  
**Status:** Pending

## Completed Tasks

- [x] **P0-T01:** Inspect existing project (2026-11-14)
- [x] **P0-T02:** Initialize project instructions (2026-11-14)
- [x] **P0-T03:** Documentation commit (2026-11-14)

## Task Log

| Date | Task ID | Summary | Tests | Commit |
|---|---|---|---|---|
| 2026-11-14 | P0-T01 | Inspeksi kondisi aktual project, runtime (PHP, Composer, Node), asset theme, database, dan status Git | `php -v`, `artisan --version`, inspeksi folder | 1b4126d |
| 2026-11-14 | P0-T02 | Review dan perapian instruksi kerja, memastikan konsistensi keputusan produk di README, PRD, AGENTS, IMPLEMENTATION_PLAN, dan PROGRESS | Validasi silang isi dokumen | 1b4126d |
| 2026-11-14 | P0-T03 | Inisialisasi Git repository dan commit awal dokumen rencana implementasi | `git status`, verifikasi tidak ada secret | 1b4126d |

## Theme Asset Inspection

- Expected theme path: `public/theme/adminLTE`
- Folder exists: Ya (`public/theme/adminLTE` / `public/theme/AdminLTE`)
- CSS files found: `dist/css/adminlte.css`, `dist/css/adminlte.min.css`, `dist/css/adminlte-colors-v3.css`, dll.
- JavaScript files found: `dist/js/adminlte.js`, `dist/js/adminlte.min.js`, `dist/js/adminlte.esm.js`
- Plugin folders found: Paket bawaan dist AdminLTE (`dist/assets/`, `dist/pages/`, `dist/tables/`, dll.)
- Image/font folders found: `dist/assets/img/` (avatar, logo, credit icons)
- Blade asset references verified: Belum (akan dikerjakan pada P1-T02)
- Core theme assets loaded without CDN: Disediakan lokal di `public/theme/adminLTE/dist`
- Catatan versi theme: Paket di `public/theme/adminLTE` adalah AdminLTE v4.10.1 (Bootstrap 5). Sesuai aturan AGENTS.md/README.md, asset yang disediakan ini wajib digunakan langsung tanpa diganti atau diunduh ulang.

## Environment Inspection

- Project path: `C:\laragon\www\CBT`
- Laravel version: 13.42.0 (Laravel Framework `^13.17`)
- PHP version: PHP 8.3.28 (CLI Visual C++ 2019 x64, Laragon: `C:\laragon\bin\php\8.3.28\php.exe`)
- Composer version: 2.10.2 (`C:\laragon\bin\composer\composer.phar`)
- Node/NPM version: Node v24.16.0 / NPM 11.13.0
- AdminLTE 3 asset location: `public/theme/adminLTE/dist`
- Database configuration: SQLite (`database/database.sqlite` sudah ada; `DB_CONNECTION=sqlite`)
- Git repository status: Diinisialisasi (clean documentation commit)
- Existing uncommitted changes: File fondasi Laravel belum di-stage (akan masuk pada Task P1-T01)
- Relevant dependencies: `laravel/framework` (13.42.0), `laravel/tinker` (3.0), dev: `fakerphp/faker` (1.24), `phpunit/phpunit` (12.5.44)

## Decisions and Assumptions

- Branding generic: CBT School.
- Target pengguna: sekolah.
- Deployment: LAN.
- Framework: Laravel.
- UI: AdminLTE 3 (menggunakan asset yang disediakan di `public/theme/adminLTE`).
- Database prototype: SQLite.
- Data dummy: Faker/Seeder.
- Role: Super Admin, Admin Sekolah, Guru, Siswa.
- Mekanisme ujian: scheduled.
- Pelaksanaan: individual.
- Jenis soal: pilihan ganda A–E.
- Timer: server-based.
- Autosave: SQLite.
- Scoring: otomatis.
- Tidak menggunakan database produksi.

## Known Issues / Risks

1. **Path Environment Windows/Laragon:** Binary `php` dan `composer` terletak di `C:\laragon\bin\php\8.3.28` dan `C:\laragon\bin\composer`. Script/command harus menggunakan absolute path atau menambahkan folder tersebut ke `$env:PATH`.
2. **Git Repository:** Repository Git belum ada. Perlu inisialisasi `git init` pada task P0-T03 agar commit berkala sesuai AGENTS.md dapat berjalan.
3. **Versi Asset Theme:** Dokumen menyebutkan AdminLTE 3, namun asset yang disediakan di `public/theme/adminLTE` berisikan build AdminLTE v4.10.1 (Bootstrap 5). Sesuai prinsip *pertahankan asset lokal yang disediakan tanpa mengunduh ulang*, asset lokal yang ada di `public/theme/adminLTE/dist` akan digunakan secara konsisten.

## Next Task

Setelah P0-T03 selesai, lanjutkan P1-T01 — Laravel environment and SQLite.

## Required Update Format

Setiap task selesai, tambahkan:
- tanggal;
- task ID;
- ringkasan perubahan;
- daftar file penting;
- test command dan hasil;
- acceptance criteria;
- commit hash dan pesan;
- known issue;
- task berikutnya.
