<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cbt:reset-demo {--force : Jalankan reset tanpa konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset ulang database dan isi dengan dataset skenario demo CBT School yang konsisten';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->line('<fg=cyan;options=bold>          CBT SCHOOL — RESET DATASET DEMO LAN            </>');
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->newLine();

        if (! $this->option('force')) {
            if (! $this->confirm('Tindakan ini akan me-reset database SQLite dan mengisi ulang data demo. Lanjutkan?', true)) {
                $this->warn('Operasi reset demo dibatalkan.');
                return Command::SUCCESS;
            }
        }

        $this->info('1. Menjalankan migrasi database bersih (migrate:fresh)...');
        Artisan::call('migrate:fresh', [
            '--force' => true,
        ]);
        $this->line('   <fg=green>✓</> Database SQLite berhasil di-reset.');

        $this->info('2. Menyemai dataset master data, bank soal, jadwal ujian, dan skenario demo (db:seed)...');
        Artisan::call('db:seed', [
            '--force' => true,
        ]);
        $this->line('   <fg=green>✓</> Seluruh seeder demo berhasil dijalankan.');

        $this->newLine();
        $this->info('================ AKUN PENGGUNA DEMO ================');
        $this->table(
            ['Role / Peran', 'Nama Pengguna', 'Username', 'Kata Sandi', 'Akses Utama'],
            [
                ['Superadmin', 'Super Administrator', 'superadmin', 'password', 'Konfigurasi sistem & profil sekolah'],
                ['Admin Sekolah', 'Admin Sekolah', 'admin', 'password', 'Master data, monitoring, audit log'],
                ['Guru Pengampu', 'Budi Santoso, S.Pd., M.Kom.', 'guru', 'password', 'Bank soal, jadwal ujian, monitoring, nilai'],
                ['Siswa Peserta', 'Ahmad Dani', 'siswa', 'password', 'Ruang ujian, pengerjaan CBT, riwayat nilai'],
            ]
        );

        $this->newLine();
        $this->info('================ JADWAL UJIAN DEMO SIAP DIUJI ================');
        $this->table(
            ['Kode Ujian', 'Mata Pelajaran & Judul', 'Token', 'Status', 'Skenario Pengujian'],
            [
                ['EXAM-PWPB-01', 'PWPB - Asesmen Sumatif Tengah Semester', 'CBT2026', 'Aktif (Published)', 'Login sebagai siswa -> Masukkan token -> Kerjakan CBT'],
                ['EXAM-BD-01', 'BD - Penilaian Harian Basis Data', 'BASISDATA', 'Selesai (Published)', 'Lihat rekap nilai, ranking, analisis soal, export CSV & PDF'],
                ['EXAM-PWPB-REMIDIAL', 'PWPB - Ujian Remidial & Pengayaan', '-', 'Draft', 'Uji alur guru mengedit butir soal & publikasi jadwal'],
            ]
        );

        $this->newLine();
        $this->line('<fg=green;options=bold>Dataset demo CBT School berhasil disiapkan!</>');
        $this->line('Silakan buka aplikasi di browser dan ikuti panduan pada file <fg=yellow>DEMO_GUIDE.md</>.');
        $this->newLine();

        return Command::SUCCESS;
    }
}

