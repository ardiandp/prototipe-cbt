<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
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

class ExamAttemptLifecycleTest extends TestCase
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

        $this->subject = Subject::factory()->create(['name' => 'Basis Data']);
    }

    private function createReadyExam(array $overrides = []): Exam
    {
        $exam = Exam::create(array_merge([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian Akhir Semester Basis Data',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10), // Sedang buka
            'end_at' => Carbon::now()->addHours(2),
            'passing_grade' => 75.00,
            'status' => 'published',
        ], $overrides));

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'order' => 1,
            'question_text' => 'Perintah SQL untuk mengambil data adalah?',
            'weight' => 10.0,
            'difficulty' => 'easy',
        ]);

        ExamQuestionOption::create([
            'exam_question_id' => $question->id,
            'label' => 'A',
            'option_text' => 'SELECT',
            'is_correct' => true,
            'order' => 1,
        ]);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'classroom_id' => $this->studentA->classroom_id,
            'status' => 'registered',
        ]);

        return $exam;
    }

    public function test_student_can_start_attempt_when_exam_is_open_and_assigned(): void
    {
        $exam = $this->createReadyExam();

        $response = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $this->studentA->id)
            ->first();

        $this->assertNotNull($attempt);
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertEquals(1, $attempt->attempt_number);
        $this->assertTrue($attempt->isActive());

        // Redirected to attempt screen
        $response->assertRedirect(route('student.attempts.show', $attempt));

        // Participant status updated to present
        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'status' => 'present',
        ]);
    }

    public function test_duplicate_start_resumes_existing_active_attempt(): void
    {
        $exam = $this->createReadyExam();

        // Panggilan start pertama
        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $this->assertEquals(1, ExamAttempt::count());
        $attempt1 = ExamAttempt::first();

        // Panggilan start kedua (misal siswa tekan back atau klik mulai lagi)
        $response2 = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        // Jumlah attempt tetap 1 (tidak membuat duplicate attempt)
        $this->assertEquals(1, ExamAttempt::count());
        $response2->assertRedirect(route('student.attempts.show', $attempt1));
    }

    public function test_expires_at_is_clamped_by_exam_end_at(): void
    {
        // Kasus 1: Sisa jendela ujian (30 menit) < durasi ujian (60 menit)
        $exam = $this->createReadyExam([
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(30), // Hanya tersisa 30 menit
        ]);

        $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $attempt = ExamAttempt::first();

        // Batas waktu attempt harus clamped ke exam.end_at, bukan started_at + 60 menit
        $this->assertEquals($exam->end_at->toDateTimeString(), $attempt->expires_at->toDateTimeString());
    }

    public function test_token_verification_when_exam_requires_token(): void
    {
        $exam = $this->createReadyExam(['token' => 'ABC123XYZ']);

        // 1. Tanpa token
        $resNoToken = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));
        $resNoToken->assertRedirect(route('student.exams.show', $exam));
        $resNoToken->assertSessionHas('error');
        $this->assertEquals(0, ExamAttempt::count());

        // 2. Token salah
        $resWrongToken = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam), ['token' => 'WRONGTOKEN']);
        $resWrongToken->assertRedirect(route('student.exams.show', $exam));
        $resWrongToken->assertSessionHas('error');
        $this->assertEquals(0, ExamAttempt::count());

        // 3. Token benar (case insensitive check)
        $resCorrect = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam), ['token' => 'abc123xyz']);
        $attempt = ExamAttempt::first();
        $this->assertNotNull($attempt);
        $resCorrect->assertRedirect(route('student.attempts.show', $attempt));
    }

    public function test_student_cannot_start_attempt_if_not_assigned(): void
    {
        $exam = $this->createReadyExam();

        // Student B tidak terdaftar
        $response = $this->actingAs($this->studentUserB)
            ->post(route('student.exams.start', $exam));

        $response->assertForbidden();
        $this->assertEquals(0, ExamAttempt::count());
    }

    public function test_student_cannot_start_attempt_if_exam_is_not_open(): void
    {
        // 1. Upcoming exam
        $upcomingExam = $this->createReadyExam([
            'start_at' => Carbon::now()->addHour(),
            'end_at' => Carbon::now()->addHours(2),
        ]);

        $resUpcoming = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $upcomingExam));
        $resUpcoming->assertRedirect(route('student.exams.show', $upcomingExam));
        $resUpcoming->assertSessionHas('error');

        // 2. Past exam
        $pastExam = $this->createReadyExam([
            'start_at' => Carbon::now()->subHours(2),
            'end_at' => Carbon::now()->subHour(),
        ]);

        $resPast = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $pastExam));
        $resPast->assertRedirect(route('student.exams.show', $pastExam));
        $resPast->assertSessionHas('error');
    }

    public function test_student_cannot_start_attempt_if_exam_is_draft(): void
    {
        $draftExam = $this->createReadyExam(['status' => 'draft']);

        $response = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $draftExam));

        $response->assertNotFound();
    }

    public function test_completed_attempt_cannot_be_restarted(): void
    {
        $exam = $this->createReadyExam();

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(30),
            'expires_at' => Carbon::now()->addMinutes(30),
            'completed_at' => Carbon::now()->subMinute(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $response->assertRedirect(route('student.exams.show', $exam));
        $response->assertSessionHas('error');
        $this->assertEquals(1, ExamAttempt::count());
    }

    public function test_expired_attempt_auto_finalizes_and_denies_new_attempt(): void
    {
        $exam = $this->createReadyExam();

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(80),
            'expires_at' => Carbon::now()->subMinutes(20), // Sudah lewat
            'status' => 'in_progress',
        ]);

        // Kunjungi show attempt
        $resShow = $this->actingAs($this->studentUserA)
            ->get(route('student.attempts.show', $attempt));

        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
        $resShow->assertRedirect(route('student.exams.show', $exam));

        // Coba panggil start
        $resStart = $this->actingAs($this->studentUserA)
            ->post(route('student.exams.start', $exam));

        $resStart->assertRedirect(route('student.exams.show', $exam));
        $resStart->assertSessionHas('error');
    }

    public function test_student_cannot_access_other_student_attempt(): void
    {
        $exam = $this->createReadyExam();

        $attemptA = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addHour(),
            'status' => 'in_progress',
        ]);

        // Student B mencoba melihat attempt Student A
        $response = $this->actingAs($this->studentUserB)
            ->get(route('student.attempts.show', $attemptA));

        $response->assertForbidden();
    }
}
