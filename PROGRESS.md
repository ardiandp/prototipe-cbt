# PROGRESS — CBT School

Dokumen ini adalah catatan status aktual implementasi. AI wajib memperbaruinya setiap selesai task.

## Project Status

| Item | Status |
|---|---|
| Project inspection | Selesai |
| Dokumentasi awal | Selesai |
| Laravel foundation | Selesai |
| Authentication & roles | Selesai |
| Master data | Selesai (Phase 3 lengkap) |
| Question bank | Dalam pengerjaan (P4-T01 selesai) |
| Exam management | Belum dimulai |
| Exam engine | Belum dimulai |
| Monitoring & scoring | Belum dimulai |
| Reporting | Belum dimulai |
| LAN demo readiness | Belum dimulai |

## Current Task

**Task ID:** P4-T02  
**Task:** Question CRUD  
**Status:** Pending

## Completed Tasks

- [x] **P0-T01:** Inspect existing project (2026-11-14)
- [x] **P0-T02:** Initialize project instructions (2026-11-14)
- [x] **P0-T03:** Documentation commit (2026-11-14)
- [x] **P1-T01:** Laravel environment and SQLite (2026-11-14)
- [x] **P1-T02:** Integrasi AdminLTE 3 dari asset yang sudah disediakan (2026-11-14)
- [x] **P1-T03:** Route and view foundation (2026-11-14)
- [x] **P2-T01:** User and role model (2026-11-16)
- [x] **P2-T02:** Login and logout (2026-11-16)
- [x] **P2-T03:** Authorization (2026-11-16)
- [x] **P3-T01:** School profile and academic period (2026-11-16)
- [x] **P3-T02:** Department, class, subject (2026-11-16)
- [x] **P3-T03:** Teacher management (2026-11-16)
- [x] **P3-T04:** Student management (2026-11-16)
- [x] **P3-T05:** Master data seeders and dashboards (2026-11-16)
- [x] **P4-T01:** Category and question schema (2026-11-16)
- [x] **P4-T02:** Question CRUD, duplicate, and preview (2026-11-16)
- [x] **P4-T03:** Question content and media (2026-11-16)

## Task Log

| Date | Task ID | Summary | Tests | Commit |
|---|---|---|---|---|
| 2026-11-14 | P0-T01 | Inspeksi kondisi aktual project, runtime (PHP, Composer, Node), asset theme, database, dan status Git | `php -v`, `artisan --version`, inspeksi folder | af9292e |
| 2026-11-14 | P0-T02 | Review dan perapian instruksi kerja, memastikan konsistensi keputusan produk di README, PRD, AGENTS, IMPLEMENTATION_PLAN, dan PROGRESS | Validasi silang isi dokumen | af9292e |
| 2026-11-14 | P0-T03 | Inisialisasi Git repository dan commit awal dokumen rencana implementasi | `git status`, verifikasi tidak ada secret | af9292e |
| 2026-11-14 | P1-T01 | Konfigurasi environment Laravel, SQLite, timezone Asia/Jakarta, pembersihan .env.example, dan penambahan EnvironmentTest | `artisan migrate:status`, `artisan db:show`, `EnvironmentTest` (3 tests passed) | d68a90b |
| 2026-11-14 | P1-T02 | Integrasi asset lokal AdminLTE 3 ke layout master Blade, navbar, sidebar, footer, content-header, komponen reusable (alert, card, modal, table, form-input), halaman demo, dan Feature test | `AdminLteLayoutTest` (5 tests passed, 19 assertions) | 09712dc |
| 2026-11-14 | P1-T03 | Penyusunan route groups (guest, auth, admin, teacher, student), pembuatan halaman login shell, error views (403, 404, 500) berbasis AdminLTE, AuthController, DashboardController, dan RouteAndFoundationTest | `RouteAndFoundationTest`, `phpunit` (12 tests passed, 42 assertions) | d0cbbf0 |
| 2026-11-16 | P2-T01 | Implementasi UserRole Enum (superadmin, admin, teacher, student), migrasi username & role, User model helpers, UserFactory, UserSeeder, dan UserModelTest | `UserModelTest`, `phpunit` (15 tests passed, 73 assertions) | 0865f1d |
| 2026-11-16 | P2-T02 | FormRequest LoginRequest dengan rate limiting & inactive check, login multi-identifier (username/email), dynamic role-based redirect, session regeneration, logout invalidation, dan AuthenticationTest | `AuthenticationTest`, `phpunit` (23 tests passed, 96 assertions) | 64fb14d |
| 2026-11-16 | P2-T03 | Implementasi EnsureUserHasRole middleware (`role`), pembatasan route groups admin/teacher/student, render sidebar dinamis per role, dan AuthorizationTest | `AuthorizationTest`, `phpunit` (30 tests passed, 118 assertions) | f548a37 |
| 2026-11-16 | P3-T01 | Implementasi Profil Sekolah (SchoolProfile) & Periode Akademik (AcademicYear & Semester) lengkap dengan form validation, active period toggle via DB transaction, AdminLTE views, seeders, serta feature tests | `SchoolProfileTest`, `AcademicPeriodTest`, `phpunit` (45 tests passed, 174 assertions) | 90f5be5 |
| 2026-11-16 | P3-T02 | Implementasi CRUD Master Data Jurusan (Department), Kelas (Classroom), dan Mata Pelajaran (Subject) lengkap dengan FormRequests, relasi antar entitas, proteksi penghapusan, views AdminLTE, factories, seeders, serta feature tests | `MasterDataDepartmentTest`, `MasterDataClassroomTest`, `MasterDataSubjectTest`, `phpunit` (68 tests passed, 250 assertions) | 70dc800 |
| 2026-11-16 | P3-T03 | Implementasi Manajemen Guru (Teacher) lengkap dengan relasi akun pengguna (User role teacher), penugasan mata pelajaran (subject_teacher) dan kelas (classroom_teacher), FormRequests, views AdminLTE (index, create, edit), factories, seeders, serta feature tests | `MasterDataTeacherTest`, `phpunit` (77 tests passed, 290 assertions) | bad02dd |
| 2026-11-16 | P3-T04 | Implementasi Manajemen Siswa (Student) lengkap dengan relasi akun pengguna (User role student), relasi kelas (Classroom), FormRequests, views AdminLTE (index, create, edit), factories, seeders, serta feature tests | `MasterDataStudentTest`, `phpunit` (86 tests passed, 327 assertions) | 56068cd |
| 2026-11-16 | P3-T05 | Implementasi data-driven dashboard untuk setiap role (Admin, Guru, Siswa), verifikasi reproducible seeders dari scratch via migrate:fresh --seed, dan feature tests dashboard | `DashboardTest`, `phpunit` (91 tests passed, 357 assertions) | f395948 |
| 2026-11-16 | P4-T01 | Implementasi skema database Bank Soal: QuestionCategory, Question, dan QuestionOption (A–E) lengkap dengan relasi, cascade delete, bobot positif, helper kunci jawaban, factories, seeder bank soal, dan feature tests | `QuestionSchemaTest`, `phpunit` (98 tests passed, 394 assertions) | eb764a0 |
| 2026-11-16 | P4-T02 | Implementasi CRUD Bank Soal & Soal Pilihan Ganda (A–E), pencarian, filter, paginasi, duplikasi soal (reset kode *-COPY & status draft), modal preview soal tampilan CBT, FormRequests, dan feature tests | `QuestionCrudTest`, `phpunit` (109 tests passed, 448 assertions) | 37f4fb6 |
| 2026-11-16 | P4-T03 | Implementasi media gambar lokal soal & opsi (A–E), validasi file (JPG/PNG/WEBP/GIF max 2MB), upload, ganti & hapus gambar, duplikasi file mandiri, cleanup penghapusan, fallback route /storage, dan feature tests | `QuestionMediaTest`, `phpunit` (119 tests passed, 507 assertions) | a50e64b |

## Theme Asset Inspection

- Expected theme path: `public/theme/adminLTE`
- Folder exists: Ya (`public/theme/adminLTE` / `public/theme/AdminLTE`)
- CSS files found: `dist/css/adminlte.css`, `dist/css/adminlte.min.css`, `dist/css/adminlte-colors-v3.css`, dll.
- JavaScript files found: `dist/js/adminlte.js`, `dist/js/adminlte.min.js`, `dist/js/adminlte.esm.js`
- Plugin folders found: Paket bawaan dist AdminLTE (`dist/assets/`, `dist/pages/`, `dist/tables/`, dll.)
- Image/font folders found: `dist/assets/img/` (avatar, logo, credit icons)
- Blade asset references verified: Ya (terintegrasi pada `resources/views/layouts/app.blade.php`, partials, dan komponen)
- Core theme assets loaded without CDN: Ya, dimuat dari `asset('theme/adminLTE/dist/css/adminlte.min.css')` dan `asset('theme/adminLTE/dist/js/adminlte.min.js')`
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

Setelah P4-T03 selesai, lanjutkan P4-T04 — Question seeding (Seed kategori, seed soal dummy yang koheren, reproducible seed).

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
