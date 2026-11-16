<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $adminUser;
    private User $studentUser;
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

        $this->otherTeacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->otherTeacher = Teacher::factory()->create([
            'user_id' => $this->otherTeacherUser->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $this->subject = Subject::factory()->create();
        $this->teacher->subjects()->attach($this->subject);
    }

    public function test_teacher_can_view_exam_listing(): void
    {
        // Own exam
        Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Ujian Milik Guru Ini',
        ]);

        // Other teacher exam
        Exam::factory()->create([
            'teacher_id' => $this->otherTeacher->id,
            'title' => 'Ujian Guru Lain',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.index'));

        $response->assertStatus(200);
        $response->assertSee('Ujian Milik Guru Ini');
        $response->assertDontSee('Ujian Guru Lain');
    }

    public function test_teacher_can_see_create_exam_page(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Jadwal Ujian');
        $response->assertSee($this->subject->name);
    }

    public function test_teacher_can_store_valid_exam(): void
    {
        $start = Carbon::tomorrow()->setHour(9)->setMinute(0);
        $end = (clone $start)->addHours(3);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.store'), [
                'code' => 'UJN-TEST-001',
                'title' => 'Asesmen Sumatif Web Development',
                'description' => 'Petunjuk pengerjaan ujian berbasis komputer.',
                'subject_id' => $this->subject->id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $end->format('Y-m-d H:i:s'),
                'duration' => 90,
                'passing_grade' => 75.00,
                'random_question' => 1,
                'random_answer' => 1,
                'token' => 'WEB26',
                'status' => 'draft',
            ]);

        $exam = Exam::where('code', 'UJN-TEST-001')->firstOrFail();
        $response->assertRedirect(route('teacher.exams.show', $exam));

        $this->assertEquals('Asesmen Sumatif Web Development', $exam->title);
        $this->assertEquals($this->teacher->id, $exam->teacher_id);
        $this->assertEquals(90, $exam->duration);
        $this->assertEquals('WEB26', $exam->token);
        $this->assertTrue($exam->random_question);
        $this->assertTrue($exam->random_answer);
        $this->assertEquals('draft', $exam->status);
    }

    public function test_exam_validation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.store'), []);

        $response->assertSessionHasErrors([
            'title',
            'subject_id',
            'start_at',
            'end_at',
            'duration',
            'passing_grade',
            'status',
        ]);
    }

    public function test_end_at_must_be_after_start_at(): void
    {
        $start = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $invalidEnd = Carbon::tomorrow()->setHour(8)->setMinute(0); // earlier than start

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.store'), [
                'title' => 'Ujian Waktu Tidak Valid',
                'subject_id' => $this->subject->id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $invalidEnd->format('Y-m-d H:i:s'),
                'duration' => 60,
                'passing_grade' => 75.00,
                'status' => 'draft',
            ]);

        $response->assertSessionHasErrors(['end_at']);
    }

    public function test_duration_must_be_positive_integer(): void
    {
        $start = Carbon::tomorrow()->setHour(9)->setMinute(0);
        $end = (clone $start)->addHours(2);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.store'), [
                'title' => 'Ujian Durasi Negatif',
                'subject_id' => $this->subject->id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $end->format('Y-m-d H:i:s'),
                'duration' => 0, // invalid (min: 1)
                'passing_grade' => 75.00,
                'status' => 'draft',
            ]);

        $response->assertSessionHasErrors(['duration']);
    }

    public function test_teacher_can_update_their_own_exam(): void
    {
        $exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Judul Sebelum Update',
            'duration' => 60,
        ]);

        $start = Carbon::tomorrow()->setHour(8)->setMinute(0);
        $end = (clone $start)->addHours(3);

        $response = $this->actingAs($this->teacherUser)
            ->put(route('teacher.exams.update', $exam), [
                'code' => $exam->code,
                'title' => 'Judul Setelah Diperbarui',
                'subject_id' => $this->subject->id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $end->format('Y-m-d H:i:s'),
                'duration' => 120,
                'passing_grade' => 80.00,
                'status' => 'published',
            ]);

        $response->assertRedirect(route('teacher.exams.show', $exam));

        $exam->refresh();
        $this->assertEquals('Judul Setelah Diperbarui', $exam->title);
        $this->assertEquals(120, $exam->duration);
        $this->assertEquals('published', $exam->status);
    }

    public function test_teacher_cannot_update_or_delete_exam_owned_by_another_teacher(): void
    {
        $otherExam = Exam::factory()->create([
            'teacher_id' => $this->otherTeacher->id,
            'title' => 'Ujian Guru Sebelah',
        ]);

        $start = Carbon::tomorrow()->setHour(8)->setMinute(0);
        $end = (clone $start)->addHours(3);

        // Update attempt should fail with 403 Forbidden
        $updateResponse = $this->actingAs($this->teacherUser)
            ->put(route('teacher.exams.update', $otherExam), [
                'title' => 'Coba Ubah Ujian Orang Lain',
                'subject_id' => $otherExam->subject_id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $end->format('Y-m-d H:i:s'),
                'duration' => 60,
                'passing_grade' => 75.00,
                'status' => 'draft',
            ]);

        $updateResponse->assertStatus(403);

        // Delete attempt should fail with 403 Forbidden
        $deleteResponse = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.exams.destroy', $otherExam));

        $deleteResponse->assertStatus(403);
    }

    public function test_teacher_can_delete_their_own_exam(): void
    {
        $exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.exams.destroy', $exam));

        $response->assertRedirect(route('teacher.exams.index'));
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_admin_can_manage_any_exam(): void
    {
        $exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Ujian Dikelola Admin',
        ]);

        $start = Carbon::tomorrow()->setHour(8)->setMinute(0);
        $end = (clone $start)->addHours(3);

        // Admin can update
        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('teacher.exams.update', $exam), [
                'title' => 'Ujian Diedit Oleh Admin Sekolah',
                'subject_id' => $exam->subject_id,
                'start_at' => $start->format('Y-m-d H:i:s'),
                'end_at' => $end->format('Y-m-d H:i:s'),
                'duration' => 90,
                'passing_grade' => 75.00,
                'status' => 'published',
            ]);

        $updateResponse->assertRedirect(route('teacher.exams.show', $exam));

        // Admin can delete
        $deleteResponse = $this->actingAs($this->adminUser)
            ->delete(route('teacher.exams.destroy', $exam));

        $deleteResponse->assertRedirect(route('teacher.exams.index'));
        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_student_cannot_access_exam_management_crud(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('teacher.exams.index'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('teacher.exams.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_exam_computed_status_logic(): void
    {
        // 1. Draft
        $draftExam = Exam::factory()->make([
            'status' => 'draft',
        ]);
        $this->assertEquals('draft', $draftExam->computed_status);
        $this->assertEquals('Draft', $draftExam->computed_status_label);

        // 2. Cancelled
        $cancelledExam = Exam::factory()->make([
            'status' => 'cancelled',
        ]);
        $this->assertEquals('cancelled', $cancelledExam->computed_status);
        $this->assertEquals('Dibatalkan', $cancelledExam->computed_status_label);

        // 3. Published & Scheduled (Future)
        $scheduledExam = Exam::factory()->make([
            'status' => 'published',
            'start_at' => Carbon::now()->addDays(2),
            'end_at' => Carbon::now()->addDays(3),
        ]);
        $this->assertEquals('scheduled', $scheduledExam->computed_status);
        $this->assertEquals('Terjadwal', $scheduledExam->computed_status_label);
        $this->assertFalse($scheduledExam->isOpen());

        // 4. Published & Ongoing (Now in range)
        $ongoingExam = Exam::factory()->make([
            'status' => 'published',
            'start_at' => Carbon::now()->subHour(),
            'end_at' => Carbon::now()->addHour(),
        ]);
        $this->assertEquals('ongoing', $ongoingExam->computed_status);
        $this->assertEquals('Sedang Berlangsung', $ongoingExam->computed_status_label);
        $this->assertTrue($ongoingExam->isOpen());

        // 5. Published & Completed (Past)
        $completedExam = Exam::factory()->make([
            'status' => 'published',
            'start_at' => Carbon::now()->subDays(3),
            'end_at' => Carbon::now()->subDays(2),
        ]);
        $this->assertEquals('completed', $completedExam->computed_status);
        $this->assertEquals('Selesai', $completedExam->computed_status_label);
        $this->assertFalse($completedExam->isOpen());
    }
}
