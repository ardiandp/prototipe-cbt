<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
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

class ExamScheduleAndPublicationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private Subject $subject;
    private AcademicYear $academicYear;
    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->teacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);

        $classroom = Classroom::factory()->create(['name' => 'XII-RPL-1', 'level' => 12]);

        $this->studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $classroom->id,
            'is_active' => true,
        ]);

        $this->subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
    }

    private function createExam(array $overrides = []): Exam
    {
        return Exam::create(array_merge([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian Akhir Semester Pemrograman',
            'duration' => 60,
            'start_at' => Carbon::now()->addHour(),
            'end_at' => Carbon::now()->addHours(3),
            'passing_grade' => 75.00,
            'random_question' => true,
            'random_answer' => true,
            'status' => 'draft',
        ], $overrides));
    }

    private function addQuestionToExam(Exam $exam): ExamQuestion
    {
        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'order' => 1,
            'question_text' => 'Apa kepanjangan dari PHP?',
            'weight' => 10.0,
            'difficulty' => 'easy',
        ]);

        ExamQuestionOption::create([
            'exam_question_id' => $question->id,
            'label' => 'A',
            'option_text' => 'Hypertext Preprocessor',
            'is_correct' => true,
            'order' => 1,
        ]);

        return $question;
    }

    private function addParticipantToExam(Exam $exam, Student $student): ExamParticipant
    {
        return ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'classroom_id' => $student->classroom_id,
            'status' => 'registered',
        ]);
    }

    public function test_exam_cannot_be_published_without_questions(): void
    {
        $exam = $this->createExam();
        $this->addParticipantToExam($exam, $this->student);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.publish', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('error');

        $exam->refresh();
        $this->assertEquals('draft', $exam->status);
    }

    public function test_exam_cannot_be_published_without_participants(): void
    {
        $exam = $this->createExam();
        $this->addQuestionToExam($exam);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.publish', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('error');

        $exam->refresh();
        $this->assertEquals('draft', $exam->status);
    }

    public function test_exam_cannot_be_published_if_end_at_is_past(): void
    {
        $exam = $this->createExam([
            'start_at' => Carbon::now()->subHours(3),
            'end_at' => Carbon::now()->subHour(),
        ]);
        $this->addQuestionToExam($exam);
        $this->addParticipantToExam($exam, $this->student);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.publish', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('error');

        $exam->refresh();
        $this->assertEquals('draft', $exam->status);
    }

    public function test_exam_can_be_published_when_prerequisites_met(): void
    {
        $exam = $this->createExam();
        $this->addQuestionToExam($exam);
        $this->addParticipantToExam($exam, $this->student);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.publish', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('success');

        $exam->refresh();
        $this->assertEquals('published', $exam->status);
        $this->assertTrue($exam->isPublished());
    }

    public function test_teacher_can_unpublish_exam_back_to_draft(): void
    {
        $exam = $this->createExam(['status' => 'published']);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.unpublish', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('success');

        $exam->refresh();
        $this->assertEquals('draft', $exam->status);
        $this->assertTrue($exam->isDraft());
    }

    public function test_teacher_can_cancel_exam(): void
    {
        $exam = $this->createExam(['status' => 'published']);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.cancel', $exam));

        $response->assertRedirect(route('teacher.exams.show', $exam));
        $response->assertSessionHas('warning');

        $exam->refresh();
        $this->assertEquals('cancelled', $exam->status);
        $this->assertTrue($exam->isCancelled());
    }

    public function test_teacher_can_preview_exam(): void
    {
        $exam = $this->createExam();
        $this->addQuestionToExam($exam);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.preview', $exam));

        $response->assertOk();
        $response->assertSee('Pratinjau & Simulasi Ujian');
        $response->assertSee('Apa kepanjangan dari PHP?');
        $response->assertSee('Hypertext Preprocessor');
    }

    public function test_student_sees_only_assigned_published_exams(): void
    {
        // 1. Published & Assigned
        $examAssignedPublished = $this->createExam(['title' => 'Ujian Terbuka', 'status' => 'published']);
        $this->addParticipantToExam($examAssignedPublished, $this->student);

        // 2. Draft & Assigned
        $examAssignedDraft = $this->createExam(['title' => 'Ujian Masih Draft', 'status' => 'draft']);
        $this->addParticipantToExam($examAssignedDraft, $this->student);

        // 3. Published & NOT Assigned
        $examUnassignedPublished = $this->createExam(['title' => 'Ujian Kelas Lain', 'status' => 'published']);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.exams.index'));

        $response->assertOk();
        $response->assertSee('Ujian Terbuka');
        $response->assertDontSee('Ujian Masih Draft');
        $response->assertDontSee('Ujian Kelas Lain');
    }

    public function test_student_can_view_preparation_room_for_assigned_published_exam(): void
    {
        $exam = $this->createExam(['status' => 'published']);
        $this->addQuestionToExam($exam);
        $this->addParticipantToExam($exam, $this->student);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $exam));

        $response->assertOk();
        $response->assertSee('Ruang Persiapan Ujian');
        $response->assertSee($exam->title);
        $response->assertSee($this->student->name);
        $response->assertSee($this->student->nis);
    }

    public function test_student_cannot_view_unassigned_exam(): void
    {
        $exam = $this->createExam(['status' => 'published']);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $exam));

        $response->assertForbidden();
    }

    public function test_student_cannot_view_unpublished_exam(): void
    {
        $exam = $this->createExam(['status' => 'draft']);
        $this->addParticipantToExam($exam, $this->student);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $exam));

        $response->assertNotFound();
    }

    public function test_server_timer_calculation_rule(): void
    {
        // CBT Rule 5: expires_at = min(started_at + duration, exam.end_at)
        $exam = $this->createExam([
            'duration' => 60, // 60 menit
            'start_at' => Carbon::parse('2026-11-20 08:00:00'),
            'end_at' => Carbon::parse('2026-11-20 10:00:00'), // window 120 menit
        ]);

        // Kasus A: Mulai jam 08:15, selesai 60 menit kemudian = 09:15 (< 10:00)
        $startedAtA = Carbon::parse('2026-11-20 08:15:00');
        $expiryA = $exam->calculateAttemptExpiry($startedAtA);
        $this->assertEquals('2026-11-20 09:15:00', $expiryA->format('Y-m-d H:i:s'));

        // Kasus B: Mulai jam 09:40, durasi 60 menit harusnya 10:40, tapi dipangkas batas ujian 10:00
        $startedAtB = Carbon::parse('2026-11-20 09:40:00');
        $expiryB = $exam->calculateAttemptExpiry($startedAtB);
        $this->assertEquals('2026-11-20 10:00:00', $expiryB->format('Y-m-d H:i:s'));
    }

    public function test_random_question_and_answer_flags_persistence(): void
    {
        $exam = $this->createExam([
            'random_question' => true,
            'random_answer' => false,
        ]);

        $this->assertTrue($exam->random_question);
        $this->assertFalse($exam->random_answer);

        $exam->update([
            'random_question' => false,
            'random_answer' => true,
        ]);

        $exam->refresh();
        $this->assertFalse($exam->random_question);
        $this->assertTrue($exam->random_answer);
    }
}
