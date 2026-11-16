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

        $pwpbSubject = Subject::where('code', 'PWPB')->first();
        $mtkSubject = Subject::where('code', 'MTK')->first();
        $binSubject = Subject::where('code', 'BIN')->first();

        // 1. Bank Soal PWPB
        if ($pwpbSubject) {
            $catPwpb = QuestionCategory::firstOrCreate(
                ['code' => 'PWPB-XI-2026'],
                [
                    'subject_id' => $pwpbSubject->id,
                    'teacher_id' => $budiTeacher?->id,
                    'name' => 'Bank Soal Pemrograman Web & Perangkat Bergerak XI',
                    'level' => '11',
                    'description' => 'Kumpulan butir soal asesmen kompetensi pemrograman web backend dan frontend.',
                    'is_active' => true,
                ]
            );

            $pwpbQuestions = [
                [
                    'code' => 'PWPB-001',
                    'text' => 'Protokol HTTP yang paling tepat digunakan untuk mengirimkan data formulir rahasia seperti kata sandi ke server adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
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
                    'options' => [
                        ['label' => 'A', 'text' => 'php artisan migrate --seed', 'is_correct' => true],
                        ['label' => 'B', 'text' => 'php artisan db:seed --all', 'is_correct' => false],
                        ['label' => 'C', 'text' => 'php artisan make:migration --seed', 'is_correct' => false],
                        ['label' => 'D', 'text' => 'php artisan migrate:run', 'is_correct' => false],
                        ['label' => 'E', 'text' => 'php artisan db:migrate --fresh', 'is_correct' => false],
                    ],
                ],
            ];

            foreach ($pwpbQuestions as $qData) {
                $question = Question::firstOrCreate(
                    [
                        'question_category_id' => $catPwpb->id,
                        'code' => $qData['code'],
                    ],
                    [
                        'teacher_id' => $budiTeacher?->id,
                        'question_text' => $qData['text'],
                        'difficulty' => $qData['difficulty'],
                        'weight' => $qData['weight'],
                        'status' => 'active',
                        'order' => 1,
                    ]
                );

                foreach ($qData['options'] as $idx => $opt) {
                    QuestionOption::firstOrCreate(
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

        // 2. Bank Soal Matematika
        if ($mtkSubject) {
            $catMtk = QuestionCategory::firstOrCreate(
                ['code' => 'MTK-X-2026'],
                [
                    'subject_id' => $mtkSubject->id,
                    'teacher_id' => $budiTeacher?->id,
                    'name' => 'Bank Soal Matematika Kelas X',
                    'level' => '10',
                    'description' => 'Uji pemahaman eksponen, aljabar, dan sistem persamaan linier.',
                    'is_active' => true,
                ]
            );

            $mtkQuestions = [
                [
                    'code' => 'MTK-001',
                    'text' => 'Nilai dari 2^3 x 2^4 adalah...',
                    'difficulty' => 'easy',
                    'weight' => 2.00,
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
                    'options' => [
                        ['label' => 'A', 'text' => '3', 'is_correct' => false],
                        ['label' => 'B', 'text' => '4', 'is_correct' => false],
                        ['label' => 'C', 'text' => '5', 'is_correct' => true],
                        ['label' => 'D', 'text' => '6', 'is_correct' => false],
                        ['label' => 'E', 'text' => '7', 'is_correct' => false],
                    ],
                ],
            ];

            foreach ($mtkQuestions as $qData) {
                $question = Question::firstOrCreate(
                    [
                        'question_category_id' => $catMtk->id,
                        'code' => $qData['code'],
                    ],
                    [
                        'teacher_id' => $budiTeacher?->id,
                        'question_text' => $qData['text'],
                        'difficulty' => $qData['difficulty'],
                        'weight' => $qData['weight'],
                        'status' => 'active',
                        'order' => 1,
                    ]
                );

                foreach ($qData['options'] as $idx => $opt) {
                    QuestionOption::firstOrCreate(
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
}
