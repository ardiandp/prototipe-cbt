<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // UMUM & LAN
            [
                'category' => 'general',
                'question' => 'Apa itu sistem CBT School?',
                'answer' => 'CBT School adalah aplikasi Computer Based Test berbasis web yang dirancang untuk sekolah (SMP/SMA/SMK) dengan arsitektur server lokal LAN (Local Area Network). Sistem dapat beroperasi mandiri 100% tanpa sambungan internet publik, menjamin kelancaran ujian meski jaringan luar terganggu.',
                'order' => 1,
            ],
            [
                'category' => 'general',
                'question' => 'Mengapa asset AdminLTE dimuat secara offline?',
                'answer' => 'Semua file tema (CSS, JS, gambar, avatar) disimpan di folder public/theme/adminLTE/dist. Ini memastikan antarmuka tetap tampil sempurna saat laptop server dan klien siswa hanya terhubung via router switch LAN sekolah tanpa akses CDN internet.',
                'order' => 2,
            ],
            [
                'category' => 'general',
                'question' => 'Berapa spesifikasi komputer server yang disarankan?',
                'answer' => "Untuk kapasitas 30–50 siswa serentak (1 ruang laboratorium):\n- Prosesor: Intel Core i3 / Ryzen 3 setara atau lebih tinggi.\n- RAM: Minimal 4 GB (Disarankan 8 GB).\n- Penyimpanan: SSD (Solid State Drive) untuk penulisan SQLite yang cepat.\n- Jaringan: Kabel LAN Gigabit atau Router WiFi 5 GHz tanpa AP Isolation.",
                'order' => 3,
            ],

            // ADMIN SEKOLAH
            [
                'category' => 'admin',
                'question' => 'Bagaimana alur setup awal data master sekolah?',
                'answer' => "Urutan setup yang disarankan:\n1. Profil Sekolah: Lengkapi nama sekolah, NPSN, nama kepala sekolah, dan alamat.\n2. Tahun Ajaran & Semester: Pastikan periode aktif sudah diaktifkan.\n3. Jurusan & Kelas: Buat tingkatan rombel (misal: XII RPL 1, XII TKJ 1).\n4. Mata Pelajaran: Daftarkan mapel yang akan diujikan.\n5. Akun Guru & Siswa: Daftarkan akun pengampu dan peserta ujian.",
                'order' => 1,
            ],
            [
                'category' => 'admin',
                'question' => 'Bagaimana cara mereset dataset ujian demo ke kondisi awal?',
                'answer' => "Cukup jalankan satu baris perintah Artisan di terminal server:\nphp artisan cbt:reset-demo --force\nPerintah ini akan menyiapkan data demo siap pakai: ujian aktif (EXAM-PWPB-01), ujian dinilai (EXAM-BD-01), bank soal lengkap, dan akun seluruh peran.",
                'order' => 2,
            ],
            [
                'category' => 'admin',
                'question' => 'Apa fungsi menu Audit Log & Aktivitas?',
                'answer' => 'Audit Log mencatat jejak digital dari setiap tindakan krusial: riwayat login pengguna, publikasi atau pembatalan jadwal ujian, tindakan pengawas (finalisasi paksa dan retake), serta publikasi nilai hasil ujian lengkap dengan waktu, IP, dan pelakunya.',
                'order' => 3,
            ],

            // GURU PENGAMPU
            [
                'category' => 'teacher',
                'question' => 'Bagaimana cara membuat butir soal pilihan ganda?',
                'answer' => 'Masuk ke menu Bank Soal -> klik Kelola Butir Soal -> klik Tambah Soal. Setiap butir soal memiliki editor teks pertanyaan, opsi unggah gambar lokal opsional, 5 pilihan jawaban (A, B, C, D, E), penentuan kunci jawaban benar, dan bobot skor positif.',
                'order' => 1,
            ],
            [
                'category' => 'teacher',
                'question' => 'Apa itu "Snapshot Soal" saat jadwal ujian dibuat?',
                'answer' => 'Demi menjaga objektivitas dan integritas penilaian, saat jadwal ujian dibuat sistem membuat salinan snapshot soal dan opsi. Jika sewaktu-waktu guru mengubah atau menghapus soal di Bank Soal induk, soal pada ujian yang sedang atau telah berjalan tidak akan berubah.',
                'order' => 2,
            ],
            [
                'category' => 'teacher',
                'question' => 'Kapan siswa dapat melihat nilai ujian mereka?',
                'answer' => 'Nilai ujian siswa tidak akan langsung terlihat setelah submit. Guru atau Admin harus meninjau rekapitulasi terlebih dahulu, kemudian menekan tombol Terbitkan Hasil (Publish Results) di halaman Hasil Ujian.',
                'order' => 3,
            ],

            // SISWA PESERTA
            [
                'category' => 'student',
                'question' => 'Bagaimana cara mulai mengerjakan ujian?',
                'answer' => 'Login menggunakan Username (NIS) dan Password yang diberikan sekolah. Buka menu Ujian Tersedia, pilih ujian yang berstatus Siap Dikerjakan, masukkan Token Ujian (jika diminta proktor), dan klik Mulai Ujian.',
                'order' => 1,
            ],
            [
                'category' => 'student',
                'question' => 'Apakah jawaban saya hilang jika komputer restart atau mati listrik?',
                'answer' => 'Tidak hilang. Sistem menggunakan fitur Realtime Autosave. Setiap kali Anda memilih opsi jawaban (A–E), jawaban langsung tersimpan di server dengan status hijau "Tersimpan". Jika komputer mati, Anda cukup menyalakan komputer dan login kembali untuk melanjutkan sisa waktu ujian.',
                'order' => 2,
            ],
            [
                'category' => 'student',
                'question' => 'Mengapa waktu ujian tetap berkurang saat halaman di-refresh?',
                'answer' => 'Waktu ujian dihitung mutlak oleh jam server (Server Time Truth), bukan jam lokal laptop. Me-refresh halaman atau menutup browser tidak akan menambah waktu atau mengulang timer dari awal.',
                'order' => 3,
            ],
            [
                'category' => 'student',
                'question' => 'Apa kegunaan tombol "Ragu-Ragu"?',
                'answer' => 'Tombol ragu-ragu memberi warna kuning pada kotak nomor soal di palet navigasi, memudahkan Anda untuk meninjau kembali soal tersebut sebelum menekan tombol Selesai Ujian.',
                'order' => 4,
            ],

            // PENGAWAS / PROKTOR
            [
                'category' => 'proctor',
                'question' => 'Bagaimana cara memantau peserta di ruang ujian?',
                'answer' => 'Buka menu Monitoring Ujian pada dashboard Guru atau Admin. Halaman ini memperbarui progres siswa secara berkala dan menampilkan status: Belum Mulai, Sedang Mengerjakan, Selesai, atau Kedaluwarsa.',
                'order' => 1,
            ],
            [
                'category' => 'proctor',
                'question' => 'Kapan pengawas menggunakan "Finalisasi Paksa" (Force Finish)?',
                'answer' => 'Gunakan tombol ini jika siswa meninggalkan ruang ujian tanpa menekan tombol kumpulkan jawaban atau waktu telah habis namun sesi browser siswa tertutup. Pengawas wajib memasukkan alasan resmi yang akan tersimpan di Audit Log.',
                'order' => 2,
            ],
            [
                'category' => 'proctor',
                'question' => 'Kapan pengawas boleh mengizinkan Ujian Ulang (Retake)?',
                'answer' => 'Jika terjadi kendala darurat (misal: komputer siswa korslet di 5 menit awal atau terjadi listrik padam khusus pada 1 PC), pengawas dapat mengklik Izinkan Retake dengan memasukkan alasan valid. Sistem akan membuat attempt baru dan mereset timer untuk siswa tersebut.',
                'order' => 3,
            ],

            // TROUBLESHOOTING LAN
            [
                'category' => 'tech',
                'question' => 'Komputer klien tidak bisa membuka halaman server (ERR_CONNECTION_TIMED_OUT)',
                'answer' => "Pemeriksaan di komputer server:\n1. Pastikan server berjalan dengan host 0.0.0.0: php artisan serve --host=0.0.0.0 --port=8000.\n2. Pastikan firewall server mengizinkan port 8000: New-NetFirewallRule -DisplayName 'CBT LAN' -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow.\n3. Pastikan router WiFi tidak mengaktifkan fitur AP Isolation.",
                'order' => 1,
            ],
            [
                'category' => 'tech',
                'question' => 'Bagaimana cara backup basis data hasil ujian?',
                'answer' => 'Karena basis data menggunakan SQLite, seluruh data tersimpan dalam satu file tunggal di direktori: database/database.sqlite. Cukup salin file tersebut ke flashdisk atau folder aman setelah sesi ujian selesai.',
                'order' => 2,
            ],
        ];

        foreach ($faqs as $item) {
            Faq::create($item);
        }
    }
}

