<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $budiTeacher = Teacher::whereHas('user', function ($q) {
            $q->where('username', 'guru');
        })->first() ?? Teacher::first();

        $sitiTeacher = Teacher::whereHas('user', function ($q) {
            $q->where('username', 'guru.matematika');
        })->first() ?? $budiTeacher;

        $dediTeacher = Teacher::whereHas('user', function ($q) {
            $q->where('username', 'guru.tkj');
        })->first() ?? $budiTeacher;

        $pwpbSubject = Subject::where('code', 'PWPB')->first();
        $bdSubject = Subject::where('code', 'BD')->first();
        $mtkSubject = Subject::where('code', 'MTK')->first();
        $binSubject = Subject::where('code', 'BIN')->first();
        $asjSubject = Subject::where('code', 'ASJ')->first();

        // =========================================================================
        // 1. Bank Soal: Pemrograman Web dan Perangkat Bergerak (PWPB - Kelas XI)
        // =========================================================================
        if ($pwpbSubject) {
            $catPwpb = QuestionCategory::firstOrCreate(
                ['code' => 'PWPB-XI-2026'],
                [
                    'subject_id' => $pwpbSubject->id,
                    'teacher_id' => $budiTeacher?->id,
                    'name' => 'Bank Soal Pemrograman Web & Perangkat Bergerak XI',
                    'level' => '11',
                    'description' => 'Kumpulan butir soal asesmen kompetensi pemrograman web backend dan frontend kelas XI RPL.',
                    'is_active' => true,
                ]
            );

            $pwpbQuestions = [
                [
                    'code' => 'PWPB-001',
                    'text' => 'Protokol HTTP yang paling tepat digunakan untuk mengirimkan data formulir rahasia seperti kata sandi ke server adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'GET', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'POST', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'HEAD', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'OPTIONS', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'TRACE', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'PWPB-002',
                    'text' => 'Dalam arsitektur MVC (Model-View-Controller) pada framework Laravel, komponen yang bertugas mengelola logika interaksi dengan basis data adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'View', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Controller', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'Model', 'is_correct' => true],
                        ['label' => 'D', 'text' => 'Route', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Middleware', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'PWPB-003',
                    'text' => 'Perintah Artisan yang digunakan untuk menjalankan migrasi basis data sekaligus mengisi data seeder pada Laravel adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'php artisan migrate --seed', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'php artisan db:seed --all', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'php artisan make:migration --seed', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'php artisan migrate:run', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'php artisan db:migrate --fresh', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'PWPB-004',
                    'text' => 'Elemen semantik HTML5 yang paling tepat digunakan untuk membungkus area tautan navigasi utama sebuah situs web adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '<menu>', 'is_correct' => false],
                        ['label' => 'B', 'text' => '<section>', 'is_correct' => false],
                        ['label' => 'C', 'text' => '<nav>', 'is_correct' => true],
                        ['label' => 'D', 'text' => '<header>', 'is_correct' => false],
                        ['label' => 'E', 'text' => '<aside>', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'PWPB-005',
                    'text' => 'Dalam modul tata letak CSS Flexbox, properti yang digunakan untuk mengatur perataan elemen-elemen anak di sepanjang sumbu utama (main axis) adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'align-items', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'justify-content', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'flex-direction', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'align-content', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'flex-wrap', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'PWPB-006',
                    'text' => 'Fungsi asynchronous bawaan JavaScript modern yang digunakan untuk mengambil data dari server web melalui HTTP request secara promise-based adalah...',
                    'difficulty' => 'hard',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'fetch()', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'getJSON()', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'XMLHttpRequest()', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'http.request()', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'axios.run()', 'is_correct' => false],
                    ],
                ],
            ];

            $this->seedQuestionGroup($catPwpb, $budiTeacher, $pwpbQuestions);
        }

        // =========================================================================
        // 2. Bank Soal: Basis Data (BD - Kelas XI)
        // =========================================================================
        if ($bdSubject) {
            $catBd = QuestionCategory::firstOrCreate(
                ['code' => 'BD-XI-2026'],
                [
                    'subject_id' => $bdSubject->id,
                    'teacher_id' => $budiTeacher?->id,
                    'name' => 'Bank Soal Basis Data Relasional XI',
                    'level' => '11',
                    'description' => 'Asesmen kompetensi query SQL, DDL, DML, relasi antar tabel, dan normalisasi.',
                    'is_active' => true,
                ]
            );

            $bdQuestions = [
                [
                    'code' => 'BD-001',
                    'text' => 'Perintah SQL berikut ini yang termasuk ke dalam kelompok Data Definition Language (DDL) adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'SELECT', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'INSERT', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'UPDATE', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'CREATE TABLE', 'is_correct' => true],
                        ['label' => 'E', 'text' => 'DELETE', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BD-002',
                    'text' => 'Kolom atau kombinasi kolom yang nilainya mereferensikan Primary Key pada tabel lain untuk menjaga integritas relasional disebut...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Candidate Key', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Foreign Key', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'Unique Key', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Super Key', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Composite Key', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BD-003',
                    'text' => 'Klausa SQL yang digunakan untuk menyaring baris hasil operasi fungsi agregasi (seperti COUNT, SUM, AVG) adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'WHERE', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'GROUP BY', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'HAVING', 'is_correct' => true],
                        ['label' => 'D', 'text' => 'ORDER BY', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'LIMIT', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BD-004',
                    'text' => 'Operasi penggabungan dua tabel yang hanya mengembalikan baris-baris yang memiliki kecocokan nilai pada kedua tabel tersebut adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'INNER JOIN', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'LEFT JOIN', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'RIGHT JOIN', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'FULL OUTER JOIN', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'CROSS JOIN', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BD-005',
                    'text' => 'Syarat utama agar sebuah tabel basis data memenuhi Bentuk Normal Pertama (1NF) adalah...',
                    'difficulty' => 'hard',
                    'weight' => 3.00,
                    'status' => 'draft',
                    'options' => [
                        ['label' => 'A', 'text' => 'Tidak memiliki ketergantungan transitif', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Setiap atribut bernilai atomik (tunggal) dan tidak ada grup berulang', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'Semua atribut non-kunci bergantung penuh pada primary key', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Memiliki minimal dua foreign key', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Tabel telah dipisah menjadi minimal tiga entitas', 'is_correct' => false],
                    ],
                ],
            ];

            $this->seedQuestionGroup($catBd, $budiTeacher, $bdQuestions);
        }

        // =========================================================================
        // 3. Bank Soal: Matematika (MTK - Kelas X)
        // =========================================================================
        if ($mtkSubject) {
            $catMtk = QuestionCategory::firstOrCreate(
                ['code' => 'MTK-X-2026'],
                [
                    'subject_id' => $mtkSubject->id,
                    'teacher_id' => $sitiTeacher?->id,
                    'name' => 'Bank Soal Matematika Kelas X',
                    'level' => '10',
                    'description' => 'Uji pemahaman eksponen, logaritma, persamaan linier, dan peluang dasar.',
                    'is_active' => true,
                ]
            );

            $mtkQuestions = [
                [
                    'code' => 'MTK-001',
                    'text' => 'Bentuk sederhana dari operasi perpangkatan (2^3) x (2^4) adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '64', 'is_correct' => false],
                        ['label' => 'B', 'text' => '128', 'is_correct' => true],
                        ['label' => 'C', 'text' => '256', 'is_correct' => false],
                        ['label' => 'D', 'text' => '32', 'is_correct' => false],
                        ['label' => 'E', 'text' => '512', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'MTK-002',
                    'text' => 'Jika 3x + 5 = 20, maka nilai dari x adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '3', 'is_correct' => false],
                        ['label' => 'B', 'text' => '4', 'is_correct' => false],
                        ['label' => 'C', 'text' => '5', 'is_correct' => true],
                        ['label' => 'D', 'text' => '6', 'is_correct' => false],
                        ['label' => 'E', 'text' => '7', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'MTK-003',
                    'text' => 'Nilai dari logaritma ^2log 64 adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '4', 'is_correct' => false],
                        ['label' => 'B', 'text' => '5', 'is_correct' => false],
                        ['label' => 'C', 'text' => '6', 'is_correct' => true],
                        ['label' => 'D', 'text' => '7', 'is_correct' => false],
                        ['label' => 'E', 'text' => '8', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'MTK-004',
                    'text' => 'Diberikan sistem persamaan linier dua variabel: x + y = 8 dan x - y = 2. Nilai x dan y berturut-turut adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'x = 5 dan y = 3', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'x = 6 dan y = 2', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'x = 4 dan y = 4', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'x = 7 dan y = 1', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'x = 3 dan y = 5', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'MTK-005',
                    'text' => 'Pada sebuah barisan aritmetika dengan suku pertama a = 4 dan beda b = 3, suku ke-10 (U_10) barisan tersebut adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '28', 'is_correct' => false],
                        ['label' => 'B', 'text' => '30', 'is_correct' => false],
                        ['label' => 'C', 'text' => '31', 'is_correct' => true],
                        ['label' => 'D', 'text' => '34', 'is_correct' => false],
                        ['label' => 'E', 'text' => '37', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'MTK-006',
                    'text' => 'Sebuah dadu bermata enam yang seimbang dilempar satu kali. Peluang munculnya mata dadu bilangan prima adalah...',
                    'difficulty' => 'hard',
                    'weight' => 3.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '1/6', 'is_correct' => false],
                        ['label' => 'B', 'text' => '1/3', 'is_correct' => false],
                        ['label' => 'C', 'text' => '1/2', 'is_correct' => true],
                        ['label' => 'D', 'text' => '2/3', 'is_correct' => false],
                        ['label' => 'E', 'text' => '5/6', 'is_correct' => false],
                    ],
                ],
            ];

            $this->seedQuestionGroup($catMtk, $sitiTeacher, $mtkQuestions);
        }

        // =========================================================================
        // 4. Bank Soal: Bahasa Indonesia (BIN - Kelas XII)
        // =========================================================================
        if ($binSubject) {
            $catBin = QuestionCategory::firstOrCreate(
                ['code' => 'BIN-XII-2026'],
                [
                    'subject_id' => $binSubject->id,
                    'teacher_id' => $budiTeacher?->id,
                    'name' => 'Bank Soal Bahasa Indonesia Kelas XII',
                    'level' => '12',
                    'description' => 'Evaluasi kompetensi pemahaman surat lamaran pekerjaan, teks editorial, dan kaidah ejaan.',
                    'is_active' => true,
                ]
            );

            $binQuestions = [
                [
                    'code' => 'BIN-001',
                    'text' => 'Penulisan alamat surat lamaran pekerjaan yang sesuai dengan kaidah Ejaan Bahasa Indonesia (EYD) yang tepat adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Yth. Direktur PT Sukses Mandiri (tanpa kata Kepada dan tanpa titik di akhir baris)', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'Kepada Yth. Bapak Direktur PT Sukses Mandiri.', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'Kepada Yth. Direktur PT Sukses Mandiri', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Yth: Direktur PT Sukses Mandiri.', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Kepada Direktur PT Sukses Mandiri,', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BIN-002',
                    'text' => 'Artikel atau tulisan dalam surat kabar yang berisi pandangan atau pendapat resmi redaksi terhadap peristiwa aktual dan kontroversial disebut...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Artikel Opini', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Teks Editorial (Tajuk Rencana)', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'Feature', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Teks Berita Utama', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Surat Pembaca', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BIN-003',
                    'text' => 'Kalimat berikut ini yang termasuk ke dalam kalimat efektif adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Para hadirin sekalian dipersilakan untuk menempati kursi.', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Kepada para siswa-siswa dimohon segera memasuki ruang kelas.', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'Siswa yang membawa kartu ujian dipersilakan memasuki ruangan.', 'is_correct' => true],
                        ['label' => 'D', 'text' => 'Meskipun hujan lebat, tetapi kegiatan upacara tetap berlangsung.', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Bagi semua peserta ujian wajib mematuhi tata tertib yang berlaku.', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BIN-004',
                    'text' => 'Dalam struktur teks cerita novel sejarah, bagian yang mengenalkan situasi cerita, tokoh, hubungan antar tokoh, dan latar awal disebut...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Orientasi (Pengenalan Situasi Cerita)', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'Pengungkapan Peristiwa', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'Menuju Konflik (Rising Action)', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Puncak Konflik (Klimaks)', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Koda (Penutup Cerita)', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'BIN-005',
                    'text' => 'Makna kata yang tidak sebenarnya, bersifat kiasan, atau memiliki tautan rasa emosional tertentu disebut...',
                    'difficulty' => 'hard',
                    'weight' => 3.00,
                    'status' => 'draft',
                    'options' => [
                        ['label' => 'A', 'text' => 'Makna Leksikal', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Makna Gramatikal', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'Makna Denotatif', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Makna Konotatif', 'is_correct' => true],
                        ['label' => 'E', 'text' => 'Makna Kontekstual', 'is_correct' => false],
                    ],
                ],
            ];

            $this->seedQuestionGroup($catBin, $budiTeacher, $binQuestions);
        }

        // =========================================================================
        // 5. Bank Soal: Administrasi Sistem Jaringan (ASJ - Kelas XI)
        // =========================================================================
        if ($asjSubject) {
            $catAsj = QuestionCategory::firstOrCreate(
                ['code' => 'ASJ-XI-2026'],
                [
                    'subject_id' => $asjSubject->id,
                    'teacher_id' => $dediTeacher?->id,
                    'name' => 'Bank Soal Administrasi Sistem Jaringan XI',
                    'level' => '11',
                    'description' => 'Asesmen kompetensi administrasi server Linux, DNS, DHCP, Web Server, dan remote access.',
                    'is_active' => true,
                ]
            );

            $asjQuestions = [
                [
                    'code' => 'ASJ-001',
                    'text' => 'Nomor port standar (default) yang digunakan oleh layanan Secure Shell (SSH) untuk komunikasi terenkripsi adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'Port 21', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'Port 22', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'Port 23', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'Port 25', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Port 80', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'ASJ-002',
                    'text' => 'Layanan jaringan yang bertugas memberikan konfigurasi alamat IP, subnet mask, gateway, dan DNS server secara otomatis kepada klien adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'DNS Server', 'is_correct' => false],
                        ['label' => 'B', 'text' => 'DHCP Server', 'is_correct' => true],
                        ['label' => 'C', 'text' => 'NTP Server', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'FTP Server', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'Proxy Server', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'ASJ-003',
                    'text' => 'File konfigurasi pada sistem operasi Linux Debian yang digunakan untuk menentukan alamat IP Name Server resolver DNS adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => '/etc/hosts', 'is_correct' => false],
                        ['label' => 'B', 'text' => '/etc/network/interfaces', 'is_correct' => false],
                        ['label' => 'C', 'text' => '/etc/resolv.conf', 'is_correct' => true],
                        ['label' => 'D', 'text' => '/etc/bind/named.conf', 'is_correct' => false],
                        ['label' => 'E', 'text' => '/etc/hostname', 'is_correct' => false],
                    ],
                ],
                [
                    'code' => 'ASJ-004',
                    'text' => 'Perintah terminal Linux yang paling tepat digunakan untuk memeriksa tabel routing aktif pada kernel sistem operasi adalah...',
                    'difficulty' => 'medium',
                    'weight' => 2.50,
                    'status' => 'active',
                    'options' => [
                        ['label' => 'A', 'text' => 'ip route show (atau route -n)', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'ping -r', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'traceroute -v', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'ifconfig eth0', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'iptables -L', 'is_correct' => false],
                    ],
                ],
            ];

            $this->seedQuestionGroup($catAsj, $dediTeacher, $asjQuestions);
        }
    }

    /**
     * Helper to seed a list of questions into a category idempotently.
     */
    private function seedQuestionGroup(QuestionCategory $category, ?Teacher $teacher, array $questions): void
    {
        foreach ($questions as $orderIndex => $qData) {
            $question = Question::updateOrCreate(
                [
                    'question_category_id' => $category->id,
                    'code' => $qData['code'],
                ],
                [
                    'teacher_id' => $teacher?->id,
                    'question_text' => $qData['text'],
                    'difficulty' => $qData['difficulty'],
                    'weight' => $qData['weight'],
                    'status' => $qData['status'] ?? 'active',
                    'order' => $orderIndex + 1,
                ]
            );

            foreach ($qData['options'] as $idx => $opt) {
                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'label' => $opt['label'],
                    ],
                    [
                        'option_text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                        'order' => $idx + 1,
                    ]
                );
            }
        }
    }
}
