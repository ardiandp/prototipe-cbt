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

class ExamAttemptQuestionOrderingTest extends TestCase
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

        $this->classroom = Classroom::factory()->create(['name' => 'XII-RPL-1', 'level' => 12]);

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

        $this->subject = Subject::factory()->create(['name' => 'Algoritma Pemrograman']);
    }

    private function createExamWithQuestions(int $questionCount = 3, array $examOverrides = []): Exam
    {
        $exam = Exam::create(array_merge([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian Algoritma',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(2),
            'passing_grade' => 75.00,
            'random_question' => false,
            'random_answer' => false,
            'status' => 'published',
        ], $examOverrides));

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = ExamQuestion::create([
                'exam_id' => $exam->id,
                'order' => $i,
                'question_text' => "Teks Pertanyaan Nomor {$i}",
                'weight' => 10.0,
                'difficulty' => 'easy',
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                ExamQuestionOption::create([
                    'exam_question_id' => $question->id,
                    'label' => $label,
                    'option_text' => "Pilihan {$label} untuk soal {$i}",
                    'is_correct' => $label === 'A', // Opsi A selalu kunci jawaban
                    'order' => $index + 1,
                ]);
            }
        }

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'classroom_id' => $this->studentA->classroom_id,
            'status' => 'registered',
        ]);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentB->id,
            'classroom_id' => $this->studentB->classroom_id,
            'status' => 'registered',
        ]);

        return $exam;
    }

    public function test_attempt_initializes_and_persists_question_ordering(): void
    {
        $exam = $this->createExamWithQuestions(3);

        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->studentA->id)->first();
        $this->assertNotNull($attempt);

        $this->assertEquals(3, $attempt->attemptQuestions()->count());

        $orders = $attempt->attemptQuestions()->pluck('order')->toArray();
        $this->assertEquals([1, 2, 3], $orders);

        foreach ($attempt->attemptQuestions as $aq) {
            $this->assertEquals(5, $aq->options()->count());
            $this->assertEquals(['A', 'B', 'C', 'D', 'E'], $aq->options()->pluck('display_label')->toArray());
        }
    }

    public function test_non_random_question_preserves_original_order(): void
    {
        $exam = $this->createExamWithQuestions(4, ['random_question' => false]);

        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::first();
        $examQuestionIds = $exam->questions()->orderBy('order')->pluck('id')->toArray();
        $attemptQuestionIds = $attempt->attemptQuestions()->orderBy('order')->pluck('exam_question_id')->toArray();

        $this->assertEquals($examQuestionIds, $attemptQuestionIds);
    }

    public function test_random_question_generates_stable_order_across_refreshes(): void
    {
        $exam = $this->createExamWithQuestions(8, ['random_question' => true]);

        // Student A mulai attempt
        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::first();
        $firstQuestionIds = $attempt->attemptQuestions()->orderBy('order')->pluck('exam_question_id')->toArray();

        // Verifikasi seluruh 8 pertanyaan terpetakan ke urutan 1..8
        $this->assertCount(8, $firstQuestionIds);
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $attempt->attemptQuestions()->pluck('order')->toArray());

        // Refresh/revisit attempt
        $response = $this->actingAs($this->studentUserA)
            ->get(route('student.attempts.show', $attempt));
        $response->assertOk();

        // Ambil ulang dari database untuk memastikan urutan tetap stabil dan tidak berubah
        $attempt->refresh();
        $revisitedQuestionIds = $attempt->attemptQuestions()->orderBy('order')->pluck('exam_question_id')->toArray();

        $this->assertEquals($firstQuestionIds, $revisitedQuestionIds);
    }

    public function test_random_answer_shuffles_options_with_stable_display_labels(): void
    {
        $exam = $this->createExamWithQuestions(3, ['random_answer' => true]);

        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::first();

        foreach ($attempt->attemptQuestions as $aq) {
            $options = $aq->options()->orderBy('order')->get();

            // Tetap memiliki tepat 5 opsi dengan display label A, B, C, D, E
            $this->assertCount(5, $options);
            $this->assertEquals(['A', 'B', 'C', 'D', 'E'], $options->pluck('display_label')->toArray());
            $this->assertEquals([1, 2, 3, 4, 5], $options->pluck('order')->toArray());

            // Pemetaan option ID tetap unik
            $mappedOptionIds = $options->pluck('exam_question_option_id')->toArray();
            $this->assertCount(5, array_unique($mappedOptionIds));
        }

        // Revisit check
        $firstAq = $attempt->attemptQuestions()->first();
        $firstMapping = $firstAq->options()->pluck('exam_question_option_id', 'display_label')->toArray();

        $this->actingAs($this->studentUserA)->get(route('student.attempts.show', $attempt));

        $reloadedMapping = $firstAq->options()->pluck('exam_question_option_id', 'display_label')->toArray();
        $this->assertEquals($firstMapping, $reloadedMapping);
    }

    public function test_correct_answers_are_never_exposed_in_attempt_room_payload_or_view(): void
    {
        $exam = $this->createExamWithQuestions(2);

        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::first();

        $response = $this->actingAs($this->studentUserA)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // Kunci jawaban tidak boleh muncul di antarmuka atau HTML
        $response->assertDontSee('Kunci Jawaban');
        $response->assertDontSee('is_correct');
        $response->assertDontSee('isCorrect');
        $response->assertDontSee('bg-success-subtle border-success'); // Class penanda kunci jawaban pada preview guru
    }

    public function test_different_students_maintain_independent_orderings(): void
    {
        // Ujian dengan acak soal dan opsi
        $exam = $this->createExamWithQuestions(6, [
            'random_question' => true,
            'random_answer' => true,
        ]);

        // Student A mulai attempt
        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attemptA = ExamAttempt::where('student_id', $this->studentA->id)->first();
        $this->assertNotNull($attemptA);

        // Student B mulai attempt
        $this->actingAs($this->studentUserB)
            ->post(route('student.exams.start', $exam));

        $attemptB = ExamAttempt::where('student_id', $this->studentB->id)->first();
        $this->assertNotNull($attemptB);

        // Masing-masing attempt memiliki 6 butir soal dan terisolasi
        $this->assertEquals(6, $attemptA->attemptQuestions()->count());
        $this->assertEquals(6, $attemptB->attemptQuestions()->count());

        $this->assertNotEquals($attemptA->id, $attemptB->id);
    }
}
