<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamParticipantTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $adminUser;
    private User $studentUser;
    private Classroom $classroomA;
    private Classroom $classroomB;
    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $semester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
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

        $subject = Subject::factory()->create();

        $this->classroomA = Classroom::factory()->create(['name' => 'X-IPA-1', 'level' => 10, 'is_active' => true]);
        $this->classroomB = Classroom::factory()->create(['name' => 'X-IPA-2', 'level' => 10, 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'code' => 'EXAM-PART-01',
            'title' => 'Ujian Tengah Semester Fisika',
            'duration' => 90,
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(3),
            'passing_grade' => 75.00,
            'status' => 'draft',
        ]);
    }

    public function test_teacher_can_view_participant_index(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.participants.index', $this->exam));

        $response->assertOk();
        $response->assertSee('Penetapan Peserta Ujian');
        $response->assertSee('Total Peserta Terdaftar');
    }

    public function test_teacher_can_view_participant_assignment_page(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.participants.create', $this->exam));

        $response->assertOk();
        $response->assertSee('Berdasarkan Rombongan Belajar (Kelas)');
        $response->assertSee('Pilih Peserta Individual');
        $response->assertSee($this->classroomA->name);
        $response->assertSee($this->classroomB->name);
    }

    public function test_teacher_can_assign_participants_by_classroom(): void
    {
        // Buat 3 siswa di Kelas A
        $studentsClassA = Student::factory()->count(3)->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        // Buat 2 siswa di Kelas B
        $studentsClassB = Student::factory()->count(2)->create([
            'classroom_id' => $this->classroomB->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'classroom',
                'classroom_ids' => [$this->classroomA->id],
            ]);

        $response->assertRedirect(route('teacher.exams.participants.index', $this->exam));
        $response->assertSessionHas('success');

        $this->assertEquals(3, $this->exam->participants()->count());

        foreach ($studentsClassA as $student) {
            $this->assertDatabaseHas('exam_participants', [
                'exam_id' => $this->exam->id,
                'student_id' => $student->id,
                'classroom_id' => $this->classroomA->id,
                'status' => 'registered',
            ]);
        }

        // Pastikan siswa kelas B tidak masuk
        foreach ($studentsClassB as $student) {
            $this->assertDatabaseMissing('exam_participants', [
                'exam_id' => $this->exam->id,
                'student_id' => $student->id,
            ]);
        }
    }

    public function test_teacher_can_assign_participants_individually(): void
    {
        $student1 = Student::factory()->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);
        $student2 = Student::factory()->create([
            'classroom_id' => $this->classroomB->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'individual',
                'student_ids' => [$student1->id, $student2->id],
            ]);

        $response->assertRedirect(route('teacher.exams.participants.index', $this->exam));
        $this->assertEquals(2, $this->exam->participants()->count());

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $this->exam->id,
            'student_id' => $student1->id,
            'classroom_id' => $this->classroomA->id,
        ]);

        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $this->exam->id,
            'student_id' => $student2->id,
            'classroom_id' => $this->classroomB->id,
        ]);
    }

    public function test_duplicate_participant_assignment_is_prevented(): void
    {
        $student = Student::factory()->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        // Tetapkan pertama kali
        $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'individual',
                'student_ids' => [$student->id],
            ]);

        $this->assertEquals(1, $this->exam->participants()->count());

        // Coba tetapkan ulang lewat classroom mode
        $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'classroom',
                'classroom_ids' => [$this->classroomA->id],
            ]);

        // Total tetap 1, tidak terjadi duplicate exception
        $this->assertEquals(1, $this->exam->participants()->count());
    }

    public function test_teacher_can_remove_participant(): void
    {
        $student = Student::factory()->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        $participant = ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'classroom_id' => $this->classroomA->id,
            'status' => 'registered',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.exams.participants.destroy', [$this->exam, $participant]));

        $response->assertRedirect(route('teacher.exams.participants.index', $this->exam));
        $this->assertDatabaseMissing('exam_participants', [
            'id' => $participant->id,
        ]);
        $this->assertEquals(0, $this->exam->participants()->count());
    }

    public function test_teacher_can_clear_all_participants(): void
    {
        $students = Student::factory()->count(4)->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        foreach ($students as $s) {
            ExamParticipant::create([
                'exam_id' => $this->exam->id,
                'student_id' => $s->id,
                'classroom_id' => $this->classroomA->id,
                'status' => 'registered',
            ]);
        }

        $this->assertEquals(4, $this->exam->participants()->count());

        $response = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.exams.participants.destroy-all', $this->exam));

        $response->assertRedirect(route('teacher.exams.participants.index', $this->exam));
        $this->assertEquals(0, $this->exam->participants()->count());
    }

    public function test_other_teacher_cannot_manage_participants(): void
    {
        $response = $this->actingAs($this->otherTeacherUser)
            ->get(route('teacher.exams.participants.index', $this->exam));

        $response->assertForbidden();

        $responseStore = $this->actingAs($this->otherTeacherUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'classroom',
                'classroom_ids' => [$this->classroomA->id],
            ]);

        $responseStore->assertForbidden();
    }

    public function test_admin_can_manage_participants_for_any_exam(): void
    {
        $student = Student::factory()->create([
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('teacher.exams.participants.store', $this->exam), [
                'assign_mode' => 'individual',
                'student_ids' => [$student->id],
            ]);

        $response->assertRedirect(route('teacher.exams.participants.index', $this->exam));
        $this->assertEquals(1, $this->exam->participants()->count());
    }

    public function test_student_cannot_access_participant_management(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('teacher.exams.participants.index', $this->exam));

        $response->assertForbidden();
    }

    public function test_participant_filtering_by_classroom_and_search(): void
    {
        $studentA = Student::factory()->create([
            'name' => 'Budi Pratama',
            'nis' => '10001',
            'classroom_id' => $this->classroomA->id,
            'is_active' => true,
        ]);

        $studentB = Student::factory()->create([
            'name' => 'Siti Nurhaliza',
            'nis' => '20002',
            'classroom_id' => $this->classroomB->id,
            'is_active' => true,
        ]);

        ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $studentA->id,
            'classroom_id' => $this->classroomA->id,
            'status' => 'registered',
        ]);

        ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $studentB->id,
            'classroom_id' => $this->classroomB->id,
            'status' => 'registered',
        ]);

        // Filter search Budi
        $res = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.participants.index', [$this->exam, 'search' => 'Budi']));
        $res->assertOk();
        $res->assertSee('Budi Pratama');
        $res->assertDontSee('Siti Nurhaliza');

        // Filter classroom B
        $res2 = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.participants.index', [$this->exam, 'classroom_id' => $this->classroomB->id]));
        $res2->assertOk();
        $res2->assertSee('Siti Nurhaliza');
        $res2->assertDontSee('Budi Pratama');
    }
}
