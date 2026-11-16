<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamAttemptQuestionOption;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAnswerAutosaveAndRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUserA;
    private Student $studentA;
    private User $studentUserB;
    private Student $studentB;
    private Subject $subject;
    private AcademicYear $academicYear;
    private Semester $semester;
    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->classroom = Classroom::factory()->create(['name' => 'XII-RPL-3', 'level' => 12]);

        $this->teacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);

        $this->studentUserA = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
        $this->studentA = Student::factory()->create([
            'user_id' => $this->studentUserA->id,
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->studentUserB = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
        $this->studentB = Student::factory()->create([
            'user_id' => $this->studentUserB->id,
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->subject = Subject::factory()->create(['name' => 'Pemrograman Web dan Perangkat Bergerak']);
    }

    private function createExamWithAttempt(int $questionCount = 3, array $attemptOverrides = []): array
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian Akhir PWPB',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(2),
            'passing_grade' => 75.00,
            'status' => 'published',
        ]);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'classroom_id' => $this->studentA->classroom_id,
            'status' => 'present',
        ]);

        $attempt = ExamAttempt::create(array_merge([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(60),
            'status' => 'in_progress',
        ], $attemptOverrides));

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = ExamQuestion::create([
                'exam_id' => $exam->id,
                'order' => $i,
                'question_text' => "Soal PWPB #{$i}: Tag untuk membuat form di HTML?",
                'weight' => 10.0,
            ]);

            $attemptQuestion = ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $question->id,
                'order' => $i,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                $opt = ExamQuestionOption::create([
                    'exam_question_id' => $question->id,
                    'label' => $label,
                    'option_text' => $label === 'A' ? '<form>' : '<input-' . $label . '>',
                    'is_correct' => $label === 'A',
                    'order' => $index + 1,
                ]);

                ExamAttemptQuestionOption::create([
                    'exam_attempt_question_id' => $attemptQuestion->id,
                    'exam_question_option_id' => $opt->id,
                    'display_label' => $label,
                    'order' => $index + 1,
                ]);
            }
        }

        return [$exam, $attempt];
    }

    public function test_student_can_autosave_answer_choice_successfully(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(3);
        $aq = $attempt->attemptQuestions()->first();
        $option = $aq->options()->first()->questionOption;

        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $option->id,
                'is_doubtful' => false,
                'client_timestamp' => 1700000000000,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'exam_attempt_question_id' => $aq->id,
            'selected_exam_question_option_id' => $option->id,
            'is_doubtful' => false,
            'answered_count' => 1,
        ]);

        $aq->refresh();
        $this->assertEquals($option->id, $aq->selected_exam_question_option_id);
        $this->assertFalse($aq->is_doubtful);
        $this->assertNotNull($aq->answered_at);

        $attempt->refresh();
        $this->assertEquals(1, $attempt->total_answered);
    }

    public function test_student_can_toggle_doubtful_status(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2);
        $aq = $attempt->attemptQuestions()->first();
        $option = $aq->options()->first()->questionOption;

        // Tandai ragu-ragu
        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $option->id,
                'is_doubtful' => true,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_doubtful' => true,
            'doubtful_count' => 1,
        ]);

        $aq->refresh();
        $this->assertTrue($aq->is_doubtful);
    }

    public function test_autosave_validates_option_belongs_to_the_question(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(3);
        $questions = $attempt->attemptQuestions()->get();

        $aq1 = $questions[0];
        $aq2 = $questions[1];
        $foreignOption = $aq2->options()->first()->questionOption; // Opsi milik soal 2

        // Coba kirim opsi soal 2 untuk soal 1
        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq1->id,
                'exam_question_option_id' => $foreignOption->id,
                'is_doubtful' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        $aq1->refresh();
        $this->assertNull($aq1->selected_exam_question_option_id);
    }

    public function test_autosave_rejected_when_attempt_is_expired(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2, [
            'started_at' => Carbon::now()->subHours(2),
            'expires_at' => Carbon::now()->subHour(),
            'status' => 'in_progress',
        ]);

        $aq = $attempt->attemptQuestions()->first();
        $option = $aq->options()->first()->questionOption;

        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $option->id,
                'is_doubtful' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'is_expired' => true,
        ]);

        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
    }

    public function test_autosave_rejected_when_attempt_is_completed(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2, [
            'status' => 'completed',
            'completed_at' => Carbon::now()->subMinutes(5),
        ]);

        $aq = $attempt->attemptQuestions()->first();
        $option = $aq->options()->first()->questionOption;

        $response = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $option->id,
                'is_doubtful' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'is_completed' => true,
        ]);
    }

    public function test_stale_request_cannot_overwrite_newer_answer(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2);
        $aq = $attempt->attemptQuestions()->first();
        $options = $aq->options()->get();

        $newerOption = $options[0]->questionOption;
        $olderOption = $options[1]->questionOption;

        // 1. Simpan jawaban lebih baru (timestamp 5000)
        $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $newerOption->id,
                'client_timestamp' => 5000,
            ]);

        $aq->refresh();
        $this->assertEquals($newerOption->id, $aq->selected_exam_question_option_id);

        // 2. Request lama yang terlambat tiba di server (timestamp 2000)
        $resStale = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => $olderOption->id,
                'client_timestamp' => 2000,
            ]);

        $resStale->assertOk();
        $resStale->assertJson(['ignored' => true]);

        // Nilai di database tetap terjaga pada jawaban yang lebih baru (tidak tertimpa)
        $aq->refresh();
        $this->assertEquals($newerOption->id, $aq->selected_exam_question_option_id);
    }

    public function test_recovery_of_answers_after_refresh(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(3);
        $questions = $attempt->attemptQuestions()->get();

        // Soal 1: Terjawab
        $opt1 = $questions[0]->options()->first()->questionOption;
        $questions[0]->update([
            'selected_exam_question_option_id' => $opt1->id,
            'is_doubtful' => false,
            'answered_at' => Carbon::now(),
        ]);

        // Soal 2: Ragu-ragu
        $opt2 = $questions[1]->options()->skip(1)->first()->questionOption;
        $questions[1]->update([
            'selected_exam_question_option_id' => $opt2->id,
            'is_doubtful' => true,
            'answered_at' => Carbon::now(),
        ]);

        // Refresh / reload halaman ujian
        $response = $this->actingAs($this->studentUserA)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // Verifikasi bahwa jawaban yang tersimpan ter-render secara utuh
        $response->assertSee('value="' . $opt1->id . '"', false);
        $response->assertSee('value="' . $opt2->id . '"', false);
        $response->assertViewHas('answeredCount', 1);
        $response->assertViewHas('doubtfulCount', 1);
        $response->assertViewHas('unansweredCount', 1);
    }

    public function test_student_can_finish_attempt_and_finalize(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2);

        $response = $this->actingAs($this->studentUserA)
            ->post(route('student.attempts.finish', $attempt));

        $response->assertRedirect(route('student.exams.show', $exam));
        $response->assertSessionHas('success');

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertTrue($attempt->isCompleted());
        $this->assertNotNull($attempt->completed_at);

        // Status peserta pada exam_participants terupdate menjadi completed
        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'status' => 'completed',
        ]);
    }

    public function test_other_student_cannot_save_or_finish_attempt(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt(2);
        $aq = $attempt->attemptQuestions()->first();

        // Student B mencoba save answer di attempt Student A
        $resSave = $this->actingAs($this->studentUserB)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $aq->id,
                'exam_question_option_id' => 1,
            ]);
        $resSave->assertForbidden();

        // Student B mencoba finish attempt Student A
        $resFinish = $this->actingAs($this->studentUserB)
            ->post(route('student.attempts.finish', $attempt));
        $resFinish->assertForbidden();
    }
}

