<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamAttemptQuestionOption;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamDemoSeeder extends Seeder
{
    private ScoringService $scoringService;

    public function __construct()
    {
        $this->scoringService = new ScoringService();
    }

    /**
     * Run the database seeds for full demo scenario readiness.
     */
    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $semester = Semester::where('is_active', true)->first() ?? Semester::first();

        $teacherUser = User::where('username', 'guru')->first();
        $teacher = Teacher::where('user_id', $teacherUser?->id)->first() ?? Teacher::first();

        $adminUser = User::where('username', 'admin')->first() ?? User::where('role', 'admin')->first();
        $studentUser = User::where('username', 'siswa')->first();
        $studentAhmad = Student::where('user_id', $studentUser?->id)->first() ?? Student::first();

        $classXRpl1 = Classroom::where('name', 'X RPL 1')->first() ?? Classroom::first();
        $classXRpl2 = Classroom::where('name', 'X RPL 2')->first() ?? $classXRpl1;

        $pwpbSubject = Subject::where('code', 'PWPB')->first() ?? Subject::first();
        $bdSubject = Subject::where('code', 'BD')->first() ?? Subject::skip(1)->first() ?? Subject::first();
        $mtkSubject = Subject::where('code', 'MTK')->first();
        $binSubject = Subject::where('code', 'BIN')->first();

        $sitiTeacherUser = User::where('username', 'guru.matematika')->first();
        $sitiTeacher = Teacher::where('user_id', $sitiTeacherUser?->id)->first() ?? $teacher;

        $pwpbCategory = QuestionCategory::where('subject_id', $pwpbSubject->id)->first() ?? QuestionCategory::first();
        $bdCategory = QuestionCategory::where('subject_id', $bdSubject->id)->first() ?? QuestionCategory::skip(1)->first() ?? $pwpbCategory;
        $mtkCategory = QuestionCategory::where('code', 'MTK-X-2026')->first() ?? ($mtkSubject ? QuestionCategory::where('subject_id', $mtkSubject->id)->first() : null);
        $binCategory = QuestionCategory::where('code', 'BIN-XII-2026')->first() ?? ($binSubject ? QuestionCategory::where('subject_id', $binSubject->id)->first() : null);

        // =========================================================================
        // 1. EXAM 1: UJIAN AKTIF SIAP DIUJI (LIVE READY FOR STUDENT DEMO)
        // =========================================================================
        $exam1 = Exam::updateOrCreate(
            ['code' => 'EXAM-PWPB-01'],
            [
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
                'subject_id' => $pwpbSubject->id,
                'teacher_id' => $teacher->id,
                'title' => 'Asesmen Sumatif Tengah Semester - Pemrograman Web',
                'description' => 'Asesmen kompetensi dasar pemrograman web berbasis client dan server untuk kelas X RPL.',
                'start_at' => Carbon::now()->subHours(2),
                'end_at' => Carbon::now()->addDays(7),
                'duration' => 60,
                'passing_grade' => 75.00,
                'random_question' => true,
                'random_answer' => true,
                'token' => 'CBT2026',
                'status' => 'published',
                'is_results_published' => false,
            ]
        );

        $this->snapshotQuestions($exam1, $pwpbCategory, 10);
        $this->assignClassroomParticipants($exam1, $classXRpl1);

        // Seed attempt demo states for Exam 1
        $this->seedExam1Attempts($exam1, $studentAhmad, $classXRpl1);

        // =========================================================================
        // 2. EXAM 2: UJIAN SELESAI & DIPUBLIKASIKAN (FOR REPORT & ITEM ANALYSIS)
        // =========================================================================
        $exam2 = Exam::updateOrCreate(
            ['code' => 'EXAM-BD-01'],
            [
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
                'subject_id' => $bdSubject->id,
                'teacher_id' => $teacher->id,
                'title' => 'Penilaian Harian Basis Data - DDL & DML',
                'description' => 'Evaluasi penguasaan sintaks Data Definition Language dan Data Manipulation Language.',
                'start_at' => Carbon::now()->subDays(4),
                'end_at' => Carbon::now()->subDays(1),
                'duration' => 45,
                'passing_grade' => 75.00,
                'random_question' => false,
                'random_answer' => false,
                'token' => 'BASISDATA',
                'status' => 'published',
                'is_results_published' => true,
            ]
        );

        $this->snapshotQuestions($exam2, $bdCategory, 10);
        $this->assignClassroomParticipants($exam2, $classXRpl1);
        $this->assignClassroomParticipants($exam2, $classXRpl2);

        // Seed finished attempts with scores and item analysis distribution
        $this->seedExam2CompletedAttempts($exam2);

        // =========================================================================
        // 3. EXAM 3: UJIAN SELESAI MATEMATIKA (COMPLETED & PUBLISHED FOR RAPORT)
        // =========================================================================
        $examMtk = null;
        if ($mtkSubject && $mtkCategory) {
            $examMtk = Exam::updateOrCreate(
                ['code' => 'EXAM-MTK-01'],
                [
                    'academic_year_id' => $year->id,
                    'semester_id' => $semester->id,
                    'subject_id' => $mtkSubject->id,
                    'teacher_id' => $sitiTeacher->id,
                    'title' => 'Asesmen Sumatif Matematika - Semester Ganjil',
                    'description' => 'Evaluasi capaian materi fungsi eksponen, logaritma, persamaan linier, dan peluang dasar.',
                    'start_at' => Carbon::now()->subDays(6),
                    'end_at' => Carbon::now()->subDays(3),
                    'duration' => 60,
                    'passing_grade' => 75.00,
                    'random_question' => false,
                    'random_answer' => false,
                    'token' => 'MATEMATIKA',
                    'status' => 'published',
                    'is_results_published' => true,
                ]
            );

            $this->snapshotQuestions($examMtk, $mtkCategory, 10);
            $this->assignClassroomParticipants($examMtk, $classXRpl1);
            $this->assignClassroomParticipants($examMtk, $classXRpl2);

            $this->seedCompletedExamAttempts($examMtk, [
                '20261001' => 5, // Ahmad Dani: 5 of 6 correct = 83.33 (Tuntas KKM)
                '20261002' => 6, // Annisa: 6 of 6 = 100.00
                '20261003' => 5, // Bagas: 5 of 6 = 83.33
                '20261004' => 4, // Citra: 4 of 6 = 66.67
                '20261005' => 5, // Dimas: 5 of 6 = 83.33
                '20261006' => 4, // Eka: 4 of 6 = 66.67
            ]);
        }

        // =========================================================================
        // 4. EXAM 4: UJIAN SELESAI BAHASA INDONESIA (COMPLETED & PUBLISHED FOR RAPORT)
        // =========================================================================
        $examBin = null;
        if ($binSubject && $binCategory) {
            $examBin = Exam::updateOrCreate(
                ['code' => 'EXAM-BIN-01'],
                [
                    'academic_year_id' => $year->id,
                    'semester_id' => $semester->id,
                    'subject_id' => $binSubject->id,
                    'teacher_id' => $teacher->id,
                    'title' => 'Penilaian Akhir Semester Bahasa Indonesia',
                    'description' => 'Penilaian capaian kompetensi surat lamaran pekerjaan, teks editorial, dan kaidah kebahasaan.',
                    'start_at' => Carbon::now()->subDays(5),
                    'end_at' => Carbon::now()->subDays(2),
                    'duration' => 45,
                    'passing_grade' => 75.00,
                    'random_question' => false,
                    'random_answer' => false,
                    'token' => 'BAHASA26',
                    'status' => 'published',
                    'is_results_published' => true,
                ]
            );

            $this->snapshotQuestions($examBin, $binCategory, 10);
            $this->assignClassroomParticipants($examBin, $classXRpl1);
            $this->assignClassroomParticipants($examBin, $classXRpl2);

            $this->seedCompletedExamAttempts($examBin, [
                '20261001' => 4, // Ahmad Dani: 4 of 4 = 100.00 (Tuntas KKM)
                '20261002' => 4, // Annisa: 4 of 4 = 100.00
                '20261003' => 3, // Bagas: 3 of 4 = 75.00
                '20261004' => 4, // Citra: 4 of 4 = 100.00
                '20261005' => 3, // Dimas: 3 of 4 = 75.00
                '20261006' => 3, // Eka: 3 of 4 = 75.00
            ]);
        }

        // =========================================================================
        // 5. EXAM 5: UJIAN DRAFT (FOR TEACHER WORKFLOW DEMO)
        // =========================================================================
        $exam3 = Exam::updateOrCreate(
            ['code' => 'EXAM-PWPB-REMIDIAL'],
            [
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
                'subject_id' => $pwpbSubject->id,
                'teacher_id' => $teacher->id,
                'title' => 'Ujian Remidial & Pengayaan Pemrograman Web',
                'description' => 'Sesi remidial khusus bagi peserta asesmen sumatif yang belum melampaui KKM.',
                'start_at' => Carbon::now()->addDays(2),
                'end_at' => Carbon::now()->addDays(5),
                'duration' => 45,
                'passing_grade' => 75.00,
                'random_question' => true,
                'random_answer' => false,
                'token' => 'REMIDIAL26',
                'status' => 'draft',
                'is_results_published' => false,
            ]
        );

        $this->snapshotQuestions($exam3, $pwpbCategory, 5);
        $this->assignClassroomParticipants($exam3, $classXRpl1);

        // =========================================================================
        // 6. SEED DEMO AUDIT LOGS
        // =========================================================================
        $this->seedDemoAuditLogs($teacherUser, $adminUser, $studentUser, $exam1, $exam2, $examMtk, $examBin);
    }

    /**
     * Snapshot questions from a category into an exam.
     */
    private function snapshotQuestions(Exam $exam, ?QuestionCategory $category, int $limit): void
    {
        if (! $category) {
            return;
        }

        // Clean existing snapshot questions if re-seeding
        $exam->questions()->delete();

        $bankQuestions = Question::with('options')
            ->where('question_category_id', $category->id)
            ->active()
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $order = 0;
        foreach ($bankQuestions as $bq) {
            $order++;
            $eq = ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $bq->id,
                'order' => $order,
                'code' => $bq->code,
                'question_text' => $bq->question_text,
                'question_image' => $bq->question_image,
                'difficulty' => $bq->difficulty,
                'weight' => $bq->weight,
            ]);

            foreach ($bq->options as $opt) {
                ExamQuestionOption::create([
                    'exam_question_id' => $eq->id,
                    'label' => $opt->label,
                    'option_text' => $opt->option_text,
                    'option_image' => $opt->option_image,
                    'is_correct' => $opt->is_correct,
                    'order' => $opt->order,
                ]);
            }
        }

        $exam->update(['total_questions' => $exam->questions()->count()]);
    }

    /**
     * Assign all students in a classroom as exam participants.
     */
    private function assignClassroomParticipants(Exam $exam, ?Classroom $classroom): void
    {
        if (! $classroom) {
            return;
        }

        $students = Student::where('classroom_id', $classroom->id)->active()->get();

        foreach ($students as $student) {
            ExamParticipant::firstOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'enrolled',
                    'can_retake' => false,
                ]
            );
        }
    }

    /**
     * Seed attempts for Exam 1 with varied real-time monitoring states:
     * - Ahmad Dani (username: 'siswa'): NO attempt (ready to test)
     * - Annisa Permata: in_progress (4 answered, 1 flagged doubtful)
     * - Bagas Pratama: completed (scored 85)
     */
    private function seedExam1Attempts(Exam $exam, ?Student $studentAhmad, ?Classroom $classroom): void
    {
        if (! $classroom) {
            return;
        }

        // Clean any existing attempts for fresh demo
        ExamAttempt::where('exam_id', $exam->id)->delete();

        // 1. Siswa Ahmad Dani stays enrolled with 0 attempt so user can log in and start!
        if ($studentAhmad) {
            ExamParticipant::where('exam_id', $exam->id)
                ->where('student_id', $studentAhmad->id)
                ->update(['status' => 'enrolled', 'can_retake' => false]);
        }

        // 2. Annisa Permata - In Progress attempt
        $annisa = Student::where('classroom_id', $classroom->id)
            ->where('nis', '20261002')
            ->first();

        if ($annisa) {
            ExamParticipant::where('exam_id', $exam->id)
                ->where('student_id', $annisa->id)
                ->update(['status' => 'present']);

            $startedAt = Carbon::now()->subMinutes(15);
            $expiresAt = $startedAt->copy()->addMinutes($exam->duration);

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $annisa->id,
                'attempt_number' => 1,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status' => 'in_progress',
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            ]);

            $questions = $exam->questions()->with('options')->get();
            $labels = ['A', 'B', 'C', 'D', 'E'];

            foreach ($questions as $qIndex => $q) {
                $attemptQuestion = ExamAttemptQuestion::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $q->id,
                    'order' => $qIndex + 1,
                    'is_doubtful' => ($qIndex === 2), // Question 3 is doubtful
                ]);

                foreach ($q->options as $optIndex => $opt) {
                    ExamAttemptQuestionOption::create([
                        'exam_attempt_question_id' => $attemptQuestion->id,
                        'exam_question_option_id' => $opt->id,
                        'display_label' => $labels[$optIndex] ?? 'A',
                        'order' => $optIndex + 1,
                    ]);
                }

                // Answer first 4 questions
                if ($qIndex < 4) {
                    $selectedOpt = $q->options->firstWhere('is_correct', true) ?? $q->options->first();
                    $attemptQuestion->update([
                        'selected_exam_question_option_id' => $selectedOpt?->id,
                        'answered_at' => Carbon::now()->subMinutes(10 - $qIndex),
                    ]);
                }
            }

            $attempt->update(['total_answered' => 4]);
        }

        // 3. Bagas Pratama - Completed attempt
        $bagas = Student::where('classroom_id', $classroom->id)
            ->where('nis', '20261003')
            ->first();

        if ($bagas) {
            ExamParticipant::where('exam_id', $exam->id)
                ->where('student_id', $bagas->id)
                ->update(['status' => 'present']);

            $startedAt = Carbon::now()->subMinutes(55);
            $expiresAt = $startedAt->copy()->addMinutes($exam->duration);
            $completedAt = Carbon::now()->subMinutes(10);

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $bagas->id,
                'attempt_number' => 1,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'completed_at' => $completedAt,
                'status' => 'completed',
                'ip_address' => '192.168.1.103',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0',
            ]);

            $questions = $exam->questions()->with('options')->get();
            $labels = ['A', 'B', 'C', 'D', 'E'];

            foreach ($questions as $qIndex => $q) {
                $attemptQuestion = ExamAttemptQuestion::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $q->id,
                    'order' => $qIndex + 1,
                    'is_doubtful' => false,
                ]);

                foreach ($q->options as $optIndex => $opt) {
                    ExamAttemptQuestionOption::create([
                        'exam_attempt_question_id' => $attemptQuestion->id,
                        'exam_question_option_id' => $opt->id,
                        'display_label' => $labels[$optIndex] ?? 'A',
                        'order' => $optIndex + 1,
                    ]);
                }

                // Bagas answers 8 questions correctly, 2 incorrectly
                $correctOpt = $q->options->firstWhere('is_correct', true);
                $wrongOpt = $q->options->firstWhere('is_correct', false) ?? $q->options->first();
                $chosenOpt = ($qIndex < 8) ? $correctOpt : $wrongOpt;

                $attemptQuestion->update([
                    'selected_exam_question_option_id' => $chosenOpt?->id,
                    'answered_at' => Carbon::now()->subMinutes(40 - $qIndex),
                ]);
            }

            // Run official ScoringService
            $this->scoringService->scoreAttempt($attempt);
        }
    }

    /**
     * Seed completed attempts for Exam 2 with diverse scores across students.
     */
    private function seedExam2CompletedAttempts(Exam $exam): void
    {
        $this->seedCompletedExamAttempts($exam, [
            '20261001' => 4, // Ahmad Dani: 4 of 4 = 100.00 (Tuntas KKM)
            '20261002' => 4, // Annisa: 4 of 4 = 100.00
            '20261003' => 3, // Bagas: 3 of 4 = 75.00
            '20261004' => 3, // Citra: 3 of 4 = 75.00
            '20261005' => 3, // Dimas: 3 of 4 = 75.00
            '20261006' => 2, // Eka: 2 of 4 = 50.00 (Remedial)
        ]);
    }

    /**
     * Seed completed attempts for an exam with configurable correct counts per student.
     *
     * @param Exam $exam
     * @param array<string, int> $customCorrectCounts [nis => number_of_correct_answers]
     * @param int $defaultCorrectOffset
     */
    private function seedCompletedExamAttempts(Exam $exam, array $customCorrectCounts = [], int $defaultCorrectOffset = 0): void
    {
        ExamAttempt::where('exam_id', $exam->id)->delete();

        $participants = ExamParticipant::where('exam_id', $exam->id)->with('student')->get();
        $questions = $exam->questions()->with('options')->orderBy('order')->get();
        $labels = ['A', 'B', 'C', 'D', 'E'];
        $totalQuestions = $questions->count();

        foreach ($participants as $index => $participant) {
            $student = $participant->student;
            if (! $student) {
                continue;
            }

            // Determine target correct answers
            if (isset($customCorrectCounts[$student->nis])) {
                $correctTarget = $customCorrectCounts[$student->nis];
            } else {
                $pattern = [$totalQuestions, max(1, $totalQuestions - 1), max(1, $totalQuestions - 1), max(1, $totalQuestions - 2), max(1, $totalQuestions - 1), max(1, $totalQuestions - 2)];
                $correctTarget = $pattern[($index + $defaultCorrectOffset) % count($pattern)];
            }
            $correctTarget = min($totalQuestions, max(0, $correctTarget));

            $startedAt = Carbon::now()->subDays(3)->addMinutes($index * 5);
            $expiresAt = $startedAt->copy()->addMinutes($exam->duration);
            $completedAt = $startedAt->copy()->addMinutes(min($exam->duration - 5, 20 + ($index % 12)));

            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'attempt_number' => 1,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'completed_at' => $completedAt,
                'status' => 'completed',
                'ip_address' => '192.168.1.' . (110 + $index),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CBTBrowser/1.0',
            ]);

            $participant->update([
                'status' => 'present',
            ]);

            foreach ($questions as $qIndex => $q) {
                $attemptQuestion = ExamAttemptQuestion::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $q->id,
                    'order' => $qIndex + 1,
                    'is_doubtful' => false,
                ]);

                foreach ($q->options as $optIndex => $opt) {
                    ExamAttemptQuestionOption::create([
                        'exam_attempt_question_id' => $attemptQuestion->id,
                        'exam_question_option_id' => $opt->id,
                        'display_label' => $labels[$optIndex] ?? 'A',
                        'order' => $optIndex + 1,
                    ]);
                }

                $correctOpt = $q->options->firstWhere('is_correct', true);
                $wrongOptions = $q->options->where('is_correct', false)->values();

                if ($qIndex < $correctTarget) {
                    $selectedOpt = $correctOpt;
                } else {
                    $distractorIndex = ($index + $qIndex) % max(1, $wrongOptions->count());
                    $selectedOpt = $wrongOptions->get($distractorIndex) ?? $wrongOptions->first();
                }

                $attemptQuestion->update([
                    'selected_exam_question_option_id' => $selectedOpt?->id,
                    'answered_at' => $startedAt->copy()->addMinutes($qIndex * 2 + 1),
                ]);
            }

            // Score attempt using official service
            $this->scoringService->scoreAttempt($attempt);
        }
    }

    /**
     * Seed realistic audit logs for demo history inspection.
     */
    private function seedDemoAuditLogs(?User $teacherUser, ?User $adminUser, ?User $studentUser, Exam $exam1, Exam $exam2, ?Exam $examMtk = null, ?Exam $examBin = null): void
    {
        if ($adminUser) {
            AuditLog::record(
                'login',
                "Pengguna {$adminUser->name} ({$adminUser->username}) berhasil masuk ke sistem.",
                $adminUser,
                ['role' => $adminUser->role->value, 'username' => $adminUser->username],
                $adminUser
            );
        }

        if ($teacherUser) {
            AuditLog::record(
                'login',
                "Pengguna {$teacherUser->name} ({$teacherUser->username}) berhasil masuk ke sistem.",
                $teacherUser,
                ['role' => $teacherUser->role->value, 'username' => $teacherUser->username],
                $teacherUser
            );

            AuditLog::record(
                'publish_exam',
                "Jadwal ujian '{$exam1->title}' dipublikasikan.",
                $exam1,
                ['exam_id' => $exam1->id, 'exam_code' => $exam1->code, 'token' => $exam1->token],
                $teacherUser
            );

            AuditLog::record(
                'publish_exam',
                "Jadwal ujian '{$exam2->title}' dipublikasikan.",
                $exam2,
                ['exam_id' => $exam2->id, 'exam_code' => $exam2->code, 'token' => $exam2->token],
                $teacherUser
            );

            AuditLog::record(
                'publish_results',
                "Pengumuman hasil dan rekap nilai ujian '{$exam2->title}' telah dirilis kepada siswa.",
                $exam2,
                ['exam_id' => $exam2->id, 'exam_code' => $exam2->code],
                $teacherUser
            );

            if ($examMtk) {
                AuditLog::record(
                    'publish_exam',
                    "Jadwal ujian '{$examMtk->title}' dipublikasikan.",
                    $examMtk,
                    ['exam_id' => $examMtk->id, 'exam_code' => $examMtk->code, 'token' => $examMtk->token],
                    $teacherUser
                );
                AuditLog::record(
                    'publish_results',
                    "Pengumuman hasil dan rekap nilai ujian '{$examMtk->title}' telah dirilis kepada siswa.",
                    $examMtk,
                    ['exam_id' => $examMtk->id, 'exam_code' => $examMtk->code],
                    $teacherUser
                );
            }

            if ($examBin) {
                AuditLog::record(
                    'publish_exam',
                    "Jadwal ujian '{$examBin->title}' dipublikasikan.",
                    $examBin,
                    ['exam_id' => $examBin->id, 'exam_code' => $examBin->code, 'token' => $examBin->token],
                    $teacherUser
                );
                AuditLog::record(
                    'publish_results',
                    "Pengumuman hasil dan rekap nilai ujian '{$examBin->title}' telah dirilis kepada siswa.",
                    $examBin,
                    ['exam_id' => $examBin->id, 'exam_code' => $examBin->code],
                    $teacherUser
                );
            }
        }

        if ($studentUser) {
            AuditLog::record(
                'login',
                "Pengguna {$studentUser->name} ({$studentUser->username}) berhasil masuk ke sistem.",
                $studentUser,
                ['role' => $studentUser->role->value, 'username' => $studentUser->username],
                $studentUser
            );
        }
    }
}
