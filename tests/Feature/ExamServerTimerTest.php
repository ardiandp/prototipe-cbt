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

class ExamServerTimerTest extends TestCase
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

        $this->classroom = Classroom::factory()->create(['name' => 'XII-RPL-2', 'level' => 12]);

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

        $this->subject = Subject::factory()->create(['name' => 'Pemrograman Berorientasi Objek']);
    }

    private function createExamWithAttempt(array $attemptOverrides = []): array
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian PBO',
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

        $q = ExamQuestion::create([
            'exam_id' => $exam->id,
            'order' => 1,
            'question_text' => 'Keyword untuk pewarisan class di Java?',
            'weight' => 10,
        ]);

        ExamQuestionOption::create([
            'exam_question_id' => $q->id,
            'label' => 'A',
            'option_text' => 'extends',
            'is_correct' => true,
            'order' => 1,
        ]);

        $attempt = ExamAttempt::create(array_merge([
            'exam_id' => $exam->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(60),
            'status' => 'in_progress',
        ], $attemptOverrides));

        return [$exam, $attempt];
    }

    public function test_timer_sync_endpoint_returns_accurate_server_time_and_remaining_seconds(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt([
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(45), // sisa ~2700 detik
        ]);

        $response = $this->actingAs($this->studentUserA)
            ->getJson(route('student.attempts.sync-time', $attempt));

        $response->assertOk();
        $response->assertJsonStructure([
            'server_time',
            'server_timestamp',
            'expires_at',
            'expires_timestamp',
            'remaining_seconds',
            'is_expired',
            'status',
        ]);

        $this->assertFalse($response->json('is_expired'));
        $this->assertEquals('in_progress', $response->json('status'));
        $this->assertGreaterThan(2650, $response->json('remaining_seconds'));
        $this->assertLessThanOrEqual(2700, $response->json('remaining_seconds'));
    }

    public function test_timer_sync_auto_finalizes_when_expires_at_is_past(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt([
            'started_at' => Carbon::now()->subMinutes(90),
            'expires_at' => Carbon::now()->subMinutes(30), // sudah kedaluwarsa 30 menit lalu
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->studentUserA)
            ->getJson(route('student.attempts.sync-time', $attempt));

        $response->assertOk();
        $response->assertJson([
            'is_expired' => true,
            'status' => 'expired',
            'remaining_seconds' => 0,
        ]);

        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
    }

    public function test_refreshing_page_does_not_reset_started_at_or_timer(): void
    {
        // 1. Siswa mulai attempt aktif
        [$exam, $attempt] = $this->createExamWithAttempt([
            'started_at' => Carbon::now()->subMinutes(10),
            'expires_at' => Carbon::now()->addMinutes(50),
        ]);

        $originalStartedAt = $attempt->started_at->toDateTimeString();
        $originalExpiresAt = $attempt->expires_at->toDateTimeString();

        // 2. Simulasikan refresh browser (request ulang halaman show attempt)
        $response = $this->actingAs($this->studentUserA)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // 3. Pastikan waktu started_at dan expires_at tidak pernah tereset
        $attempt->refresh();
        $this->assertEquals($originalStartedAt, $attempt->started_at->toDateTimeString());
        $this->assertEquals($originalExpiresAt, $attempt->expires_at->toDateTimeString());
        $this->assertEquals(1, ExamAttempt::count());
    }

    public function test_finalize_expired_endpoint_is_idempotent(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt([
            'started_at' => Carbon::now()->subHours(2),
            'expires_at' => Carbon::now()->subHour(),
            'status' => 'in_progress',
        ]);

        // Panggilan finalisasi pertama
        $res1 = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.finalize-expired', $attempt));
        $res1->assertOk();
        $res1->assertJson(['success' => true, 'status' => 'expired']);

        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);

        // Panggilan finalisasi kedua (idempotency check)
        $res2 = $this->actingAs($this->studentUserA)
            ->postJson(route('student.attempts.finalize-expired', $attempt));
        $res2->assertOk();
        $res2->assertJson(['success' => true, 'status' => 'expired']);
    }

    public function test_artisan_command_finalizes_expired_attempts_without_browser_open(): void
    {
        // Attempt 1: Expired, in_progress -> should be finalized
        [$exam1, $attemptExpired] = $this->createExamWithAttempt([
            'started_at' => Carbon::now()->subHours(2),
            'expires_at' => Carbon::now()->subMinutes(15),
            'status' => 'in_progress',
        ]);

        // Attempt 2: Active, in_progress -> should remain in_progress
        $attemptActive = ExamAttempt::create([
            'exam_id' => $exam1->id,
            'student_id' => $this->studentB->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(45),
            'status' => 'in_progress',
        ]);

        // Attempt 3: Already completed -> should remain completed
        $attemptCompleted = ExamAttempt::create([
            'exam_id' => $exam1->id,
            'student_id' => $this->studentA->id,
            'attempt_number' => 2,
            'started_at' => Carbon::now()->subHours(3),
            'expires_at' => Carbon::now()->subHours(2),
            'completed_at' => Carbon::now()->subHours(2),
            'status' => 'completed',
        ]);

        // Jalankan perintah artisan
        $this->artisan('cbt:finalize-expired-attempts')
            ->expectsOutputToContain('Successfully finalized 1 expired exam attempt(s).')
            ->assertExitCode(0);

        $attemptExpired->refresh();
        $attemptActive->refresh();
        $attemptCompleted->refresh();

        $this->assertEquals('expired', $attemptExpired->status);
        $this->assertEquals('in_progress', $attemptActive->status);
        $this->assertEquals('completed', $attemptCompleted->status);
    }

    public function test_other_student_cannot_sync_or_finalize_attempt(): void
    {
        [$exam, $attempt] = $this->createExamWithAttempt();

        // Student B mencoba mengakses attempt Student A
        $resSync = $this->actingAs($this->studentUserB)
            ->getJson(route('student.attempts.sync-time', $attempt));
        $resSync->assertForbidden();

        $resFinalize = $this->actingAs($this->studentUserB)
            ->postJson(route('student.attempts.finalize-expired', $attempt));
        $resFinalize->assertForbidden();
    }
}
