<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamScoringTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private Classroom $classroom;
    private Subject $subject;
    private QuestionCategory $category;
    private Exam $exam;
    private ExamParticipant $participant;
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringService = new ScoringService();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Penguji', 'is_active' => true]);

        $this->subject = Subject::create(['code' => 'BD', 'name' => 'Basis Data', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);
        $this->category = QuestionCategory::create(['subject_id' => $this->subject->id, 'code' => 'KAT-BD', 'name' => 'SQL & Query', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-SCORE-01',
            'title' => 'Ujian Akhir Basis Data',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'total_questions' => 3,
            'passing_grade' => 75.00,
            'status' => 'published',
            'random_question' => false,
            'random_answer' => false,
        ]);

        $this->studentUser = User::factory()->student()->create(['username' => 'siswa_score_test']);
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'nis' => '77001',
            'name' => 'Siswa Tes Nilai',
            'gender' => 'L',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->participant = ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'assigned',
        ]);
    }

    /**
     * Helper to setup 3 questions with custom weights.
     */
    private function setupQuestionsWithWeights(array $weights = [30.0, 40.0, 30.0]): array
    {
        $questions = [];

        foreach ($weights as $index => $weight) {
            $num = $index + 1;
            $bankQ = Question::create([
                'question_category_id' => $this->category->id,
                'teacher_id' => $this->teacher->id,
                'code' => "Q-SCR-0{$num}",
                'question_text' => "Pertanyaan Soal Ke-{$num}",
                'weight' => $weight,
                'difficulty' => 'medium',
                'status' => 'active',
                'order' => $num,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $idx => $label) {
                QuestionOption::create([
                    'question_id' => $bankQ->id,
                    'label' => $label,
                    'option_text' => "Pilihan {$label} soal {$num}",
                    'is_correct' => $label === 'A', // Option A is correct
                    'order' => $idx + 1,
                ]);
            }

            $eq = ExamQuestion::create([
                'exam_id' => $this->exam->id,
                'question_id' => $bankQ->id,
                'question_text' => $bankQ->question_text,
                'weight' => $weight,
                'order' => $num,
            ]);

            foreach ($bankQ->options as $opt) {
                ExamQuestionOption::create([
                    'exam_question_id' => $eq->id,
                    'question_option_id' => $opt->id,
                    'label' => $opt->label,
                    'option_text' => $opt->option_text,
                    'is_correct' => $opt->is_correct,
                    'order' => $opt->order ?? 1,
                ]);
            }

            $questions[] = $eq;
        }

        return $questions;
    }

    /**
     * Helper to create attempt with mapped questions.
     */
    private function createAttempt(array $examQuestions): ExamAttempt
    {
        $attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(20),
            'expires_at' => Carbon::now()->addMinutes(40),
            'status' => 'in_progress',
        ]);

        foreach ($examQuestions as $index => $eq) {
            $attQ = ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $eq->id,
                'order' => $index + 1,
            ]);

            foreach ($eq->options as $opt) {
                $attQ->options()->create([
                    'exam_question_option_id' => $opt->id,
                    'display_label' => $opt->label,
                    'order' => $opt->order,
                ]);
            }
        }

        return $attempt;
    }

    public function test_perfect_score_when_all_questions_are_answered_correctly(): void
    {
        $questions = $this->setupQuestionsWithWeights([20, 30, 50]);
        $attempt = $this->createAttempt($questions);

        // Answer all questions with option A (correct)
        foreach ($attempt->attemptQuestions as $aq) {
            $correctOption = $aq->question->options->firstWhere('is_correct', true);
            $aq->update([
                'selected_exam_question_option_id' => $correctOption->id,
                'answered_at' => Carbon::now(),
            ]);
        }

        $scoredAttempt = $this->scoringService->scoreAttempt($attempt);

        $this->assertEquals(100.00, $scoredAttempt->score);
        $this->assertEquals(3, $scoredAttempt->total_correct);
        $this->assertEquals(3, $scoredAttempt->total_answered);
        $this->assertEquals(0, $scoredAttempt->total_incorrect);
        $this->assertEquals(0, $scoredAttempt->total_unanswered);
        $this->assertEquals(100.00, $scoredAttempt->total_weight);
        $this->assertEquals(100.00, $scoredAttempt->earned_weight);
        $this->assertTrue($scoredAttempt->is_passed);
    }

    public function test_scoring_with_correct_incorrect_and_unanswered_questions(): void
    {
        // Total weight = 20 + 30 + 50 = 100
        $questions = $this->setupQuestionsWithWeights([20.0, 30.0, 50.0]);
        $attempt = $this->createAttempt($questions);

        $attemptQuestions = $attempt->attemptQuestions;

        // Q1 (weight 20): Answer correctly (option A)
        $q1CorrectOpt = $attemptQuestions[0]->question->options->firstWhere('is_correct', true);
        $attemptQuestions[0]->update(['selected_exam_question_option_id' => $q1CorrectOpt->id]);

        // Q2 (weight 30): Answer incorrectly (option B)
        $q2WrongOpt = $attemptQuestions[1]->question->options->firstWhere('label', 'B');
        $attemptQuestions[1]->update(['selected_exam_question_option_id' => $q2WrongOpt->id]);

        // Q3 (weight 50): Leave unanswered (null)
        $attemptQuestions[2]->update(['selected_exam_question_option_id' => null]);

        $scored = $this->scoringService->scoreAttempt($attempt);

        // Score: (20 / 100) * 100 = 20.00
        $this->assertEquals(20.00, $scored->score);
        $this->assertEquals(1, $scored->total_correct);
        $this->assertEquals(2, $scored->total_answered);
        $this->assertEquals(1, $scored->total_incorrect);
        $this->assertEquals(1, $scored->total_unanswered);
        $this->assertEquals(100.00, $scored->total_weight);
        $this->assertEquals(20.00, $scored->earned_weight);
        $this->assertFalse($scored->is_passed); // Passing grade is 75.00

        // Check individual attempt questions
        $attemptQuestions[0]->refresh();
        $this->assertTrue($attemptQuestions[0]->is_correct);
        $this->assertEquals(20.00, $attemptQuestions[0]->earned_weight);

        $attemptQuestions[1]->refresh();
        $this->assertFalse($attemptQuestions[1]->is_correct);
        $this->assertEquals(0.00, $attemptQuestions[1]->earned_weight);

        $attemptQuestions[2]->refresh();
        $this->assertFalse($attemptQuestions[2]->is_correct);
        $this->assertEquals(0.00, $attemptQuestions[2]->earned_weight);
    }

    public function test_passing_grade_threshold_logic(): void
    {
        // 4 questions, each weight 25. Total weight = 100. Passing grade = 75.00
        $this->exam->update(['passing_grade' => 75.00]);
        $questions = $this->setupQuestionsWithWeights([25.0, 25.0, 25.0, 25.0]);
        $attempt = $this->createAttempt($questions);

        $aqs = $attempt->attemptQuestions;

        // Answer 3 correct (score = 75.00) -> exact match with passing grade
        for ($i = 0; $i < 3; $i++) {
            $opt = $aqs[$i]->question->options->firstWhere('is_correct', true);
            $aqs[$i]->update(['selected_exam_question_option_id' => $opt->id]);
        }
        $aqs[3]->update(['selected_exam_question_option_id' => null]);

        $scored = $this->scoringService->scoreAttempt($attempt);
        $this->assertEquals(75.00, $scored->score);
        $this->assertTrue($scored->is_passed);

        // Now answer 2 correct (score = 50.00) -> failed
        $aqs[2]->update(['selected_exam_question_option_id' => null]);
        $scored2 = $this->scoringService->scoreAttempt($attempt);
        $this->assertEquals(50.00, $scored2->score);
        $this->assertFalse($scored2->is_passed);
    }

    public function test_scoring_uses_snapshot_and_is_immune_to_question_bank_mutations(): void
    {
        $questions = $this->setupQuestionsWithWeights([50.0, 50.0]);
        $attempt = $this->createAttempt($questions);

        // Student answers Q1 with option A (correct in snapshot)
        $q1CorrectOpt = $questions[0]->options->firstWhere('is_correct', true);
        $attempt->attemptQuestions[0]->update(['selected_exam_question_option_id' => $q1CorrectOpt->id]);

        // Student answers Q2 with option A (correct in snapshot)
        $q2CorrectOpt = $questions[1]->options->firstWhere('is_correct', true);
        $attempt->attemptQuestions[1]->update(['selected_exam_question_option_id' => $q2CorrectOpt->id]);

        // MUTATE QUESTION BANK: Teacher changes correct answer in original question bank or deletes bank question
        $originalBankQ1 = Question::where('code', 'Q-SCR-01')->first();
        $originalBankQ1->options()->update(['is_correct' => false]);
        $originalBankQ1->options()->where('label', 'B')->update(['is_correct' => true]);

        // Delete bank question 2 entirely
        $originalBankQ2 = Question::where('code', 'Q-SCR-02')->first();
        $originalBankQ2->options()->delete();
        $originalBankQ2->delete();

        // Calculate score
        $scored = $this->scoringService->scoreAttempt($attempt);

        // Snapshot is preserved! Score must still be 100.00
        $this->assertEquals(100.00, $scored->score);
        $this->assertEquals(2, $scored->total_correct);
        $this->assertTrue($scored->is_passed);
    }

    public function test_scoring_service_is_idempotent(): void
    {
        $questions = $this->setupQuestionsWithWeights([40.0, 60.0]);
        $attempt = $this->createAttempt($questions);

        // Q1 correct (40), Q2 incorrect (0) -> Score: 40.00
        $q1Opt = $questions[0]->options->firstWhere('is_correct', true);
        $attempt->attemptQuestions[0]->update(['selected_exam_question_option_id' => $q1Opt->id]);

        $q2Wrong = $questions[1]->options->firstWhere('label', 'C');
        $attempt->attemptQuestions[1]->update(['selected_exam_question_option_id' => $q2Wrong->id]);

        // Run scoring first time
        $run1 = $this->scoringService->scoreAttempt($attempt);
        $score1 = $run1->score;
        $earned1 = $run1->earned_weight;

        // Run scoring second time
        $run2 = $this->scoringService->scoreAttempt($attempt);
        $score2 = $run2->score;
        $earned2 = $run2->earned_weight;

        // Run scoring third time
        $run3 = $this->scoringService->scoreAttempt($attempt);
        $score3 = $run3->score;
        $earned3 = $run3->earned_weight;

        $this->assertEquals(40.00, $score1);
        $this->assertEquals($score1, $score2);
        $this->assertEquals($score2, $score3);
        $this->assertEquals($earned1, $earned2);
        $this->assertEquals($earned2, $earned3);
    }

    public function test_batch_scoring_for_exam(): void
    {
        $questions = $this->setupQuestionsWithWeights([50.0, 50.0]);

        // Student 1 attempt (completed)
        $attempt1 = $this->createAttempt($questions);
        $attempt1->update(['status' => 'completed']);
        $attempt1->attemptQuestions[0]->update(['selected_exam_question_option_id' => $questions[0]->options->first()->id]);

        // Student 2 attempt (expired)
        $s2User = User::factory()->student()->create();
        $s2 = Student::create(['user_id' => $s2User->id, 'nis' => '77002', 'name' => 'Siswa 2', 'gender' => 'P', 'classroom_id' => $this->classroom->id, 'is_active' => true]);
        $attempt2 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s2->id,
            'started_at' => Carbon::now()->subMinutes(40),
            'expires_at' => Carbon::now()->subMinutes(10),
            'status' => 'expired',
        ]);
        foreach ($questions as $idx => $eq) {
            ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt2->id,
                'exam_question_id' => $eq->id,
                'order' => $idx + 1,
            ]);
        }

        // Student 3 attempt (in_progress - should NOT be batch scored by default)
        $s3User = User::factory()->student()->create();
        $s3 = Student::create(['user_id' => $s3User->id, 'nis' => '77003', 'name' => 'Siswa 3', 'gender' => 'L', 'classroom_id' => $this->classroom->id, 'is_active' => true]);
        $attempt3 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s3->id,
            'started_at' => Carbon::now()->subMinutes(10),
            'expires_at' => Carbon::now()->addMinutes(50),
            'status' => 'in_progress',
        ]);

        $count = $this->scoringService->scoreExam($this->exam);
        $this->assertEquals(2, $count);

        $attempt1->refresh();
        $this->assertNotNull($attempt1->score);

        $attempt2->refresh();
        $this->assertNotNull($attempt2->score);

        $attempt3->refresh();
        $this->assertNull($attempt3->score);
    }

    public function test_automatic_scoring_on_student_manual_submit(): void
    {
        $questions = $this->setupQuestionsWithWeights([50.0, 50.0]);
        $attempt = $this->createAttempt($questions);

        // Answer Q1 correct, Q2 wrong
        $attempt->attemptQuestions[0]->update([
            'selected_exam_question_option_id' => $questions[0]->options->firstWhere('is_correct', true)->id,
        ]);
        $attempt->attemptQuestions[1]->update([
            'selected_exam_question_option_id' => $questions[1]->options->firstWhere('label', 'D')->id,
        ]);

        // Student submits attempt
        $response = $this->actingAs($this->studentUser)
            ->post(route('student.attempts.finish', $attempt));

        $response->assertRedirect();

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertNotNull($attempt->score);
        $this->assertEquals(50.00, $attempt->score);
        $this->assertEquals(1, $attempt->total_correct);
        $this->assertEquals(1, $attempt->total_incorrect);
        $this->assertEquals(0, $attempt->total_unanswered);
        $this->assertFalse($attempt->is_passed);
    }
}

