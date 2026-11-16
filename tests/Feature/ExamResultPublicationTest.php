<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Department;
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
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamResultPublicationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $studentUser;
    private Student $student;
    private Classroom $classroom;
    private Subject $subject;
    private Exam $exam;
    private ExamParticipant $participant;
    private ExamAttempt $attempt;
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringService = new ScoringService();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->adminUser = User::factory()->admin()->create();

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Pengampu', 'is_active' => true]);

        $this->otherTeacherUser = User::factory()->teacher()->create();
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'nip' => '198902022012022002', 'name' => 'Guru Lain', 'is_active' => true]);

        $this->subject = Subject::create(['code' => 'BD', 'name' => 'Basis Data', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-PUB-01',
            'title' => 'Asesmen Akhir Semester Database',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(30),
            'end_at' => Carbon::now()->addMinutes(30),
            'total_questions' => 3,
            'passing_grade' => 70.00,
            'status' => 'published',
            'is_results_published' => false,
            'random_question' => false,
            'random_answer' => false,
        ]);

        $this->studentUser = User::factory()->student()->create(['username' => 'siswa_pub_test']);
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'nis' => '88001',
            'name' => 'Budi Santoso',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->participant = ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'completed',
        ]);

        // Snapshot Questions
        $eq1 = ExamQuestion::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'Apa kepanjangan SQL?',
            'weight' => 2.0,
            'order' => 1,
        ]);
        $eq1OptA = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'A', 'option_text' => 'Structured Query Language', 'is_correct' => true, 'order' => 1]);
        $eq1OptB = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'B', 'option_text' => 'Simple Question List', 'is_correct' => false, 'order' => 2]);

        $eq2 = ExamQuestion::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'Perintah untuk mengambil data di SQL?',
            'weight' => 2.0,
            'order' => 2,
        ]);
        $eq2OptA = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'A', 'option_text' => 'SELECT', 'is_correct' => true, 'order' => 1]);
        $eq2OptB = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'B', 'option_text' => 'INSERT', 'is_correct' => false, 'order' => 2]);

        $eq3 = ExamQuestion::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'Perintah untuk menghapus tabel di SQL?',
            'weight' => 1.0,
            'order' => 3,
        ]);
        $eq3OptA = ExamQuestionOption::create(['exam_question_id' => $eq3->id, 'label' => 'A', 'option_text' => 'DROP TABLE', 'is_correct' => true, 'order' => 1]);
        $eq3OptB = ExamQuestionOption::create(['exam_question_id' => $eq3->id, 'label' => 'B', 'option_text' => 'DELETE TABLE', 'is_correct' => false, 'order' => 2]);

        // Student Attempt
        $this->attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(25),
            'expires_at' => Carbon::now()->addMinutes(35),
            'completed_at' => Carbon::now()->subMinutes(5),
            'status' => 'completed',
        ]);

        // Question 1: Answered Correctly (Option A)
        $aq1 = ExamAttemptQuestion::create([
            'exam_attempt_id' => $this->attempt->id,
            'exam_question_id' => $eq1->id,
            'order' => 1,
            'selected_exam_question_option_id' => $eq1OptA->id,
            'answered_at' => Carbon::now()->subMinutes(20),
        ]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq1->id, 'exam_question_option_id' => $eq1OptA->id, 'display_label' => 'A', 'order' => 1]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq1->id, 'exam_question_option_id' => $eq1OptB->id, 'display_label' => 'B', 'order' => 2]);

        // Question 2: Answered Correctly (Option A)
        $aq2 = ExamAttemptQuestion::create([
            'exam_attempt_id' => $this->attempt->id,
            'exam_question_id' => $eq2->id,
            'order' => 2,
            'selected_exam_question_option_id' => $eq2OptA->id,
            'answered_at' => Carbon::now()->subMinutes(10),
        ]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq2->id, 'exam_question_option_id' => $eq2OptA->id, 'display_label' => 'A', 'order' => 1]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq2->id, 'exam_question_option_id' => $eq2OptB->id, 'display_label' => 'B', 'order' => 2]);

        // Question 3: Answered Incorrectly (Option B - DELETE TABLE)
        $aq3 = ExamAttemptQuestion::create([
            'exam_attempt_id' => $this->attempt->id,
            'exam_question_id' => $eq3->id,
            'order' => 3,
            'selected_exam_question_option_id' => $eq3OptB->id,
            'answered_at' => Carbon::now()->subMinutes(8),
        ]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq3->id, 'exam_question_option_id' => $eq3OptA->id, 'display_label' => 'A', 'order' => 1]);
        ExamAttemptQuestionOption::create(['exam_attempt_question_id' => $aq3->id, 'exam_question_option_id' => $eq3OptB->id, 'display_label' => 'B', 'order' => 2]);

        // Score attempt
        $this->scoringService->scoreAttempt($this->attempt);
    }

    public function test_teacher_and_admin_can_view_results_index(): void
    {
        // Teacher
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.results.index'));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Akhir Semester Database');
        $response->assertSee('EXAM-PUB-01');

        // Admin
        $responseAdmin = $this->actingAs($this->adminUser)->get(route('admin.results.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Asesmen Akhir Semester Database');
    }

    public function test_teacher_can_view_detailed_exam_results_with_statistics(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.results.show', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Akhir Semester Database');
        $response->assertSee('Budi Santoso');
        $response->assertSee('88001');
        $response->assertSee('80.0'); // Score is 80
        $response->assertSee('Tuntas KKM');
    }

    public function test_teacher_can_publish_and_unpublish_exam_results(): void
    {
        $this->assertFalse($this->exam->is_results_published);

        // Publish results
        $publishResponse = $this->actingAs($this->teacherUser)->post(
            route('teacher.exams.publish-results', $this->exam),
            ['reason' => 'Publikasi hasil ujian resmi ke siswa']
        );
        $publishResponse->assertSessionHas('success');
        $this->assertTrue($this->exam->fresh()->is_results_published);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'publish_results',
            'auditable_type' => Exam::class,
            'auditable_id' => $this->exam->id,
            'user_id' => $this->teacherUser->id,
        ]);

        // Unpublish results
        $unpublishResponse = $this->actingAs($this->teacherUser)->post(
            route('teacher.exams.unpublish-results', $this->exam),
            ['reason' => 'Koreksi penilaian sementara']
        );
        $unpublishResponse->assertSessionHas('success');
        $this->assertFalse($this->exam->fresh()->is_results_published);

        // Verify unpublish audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unpublish_results',
            'auditable_type' => Exam::class,
            'auditable_id' => $this->exam->id,
            'user_id' => $this->teacherUser->id,
        ]);
    }

    public function test_other_teacher_cannot_view_or_publish_another_teachers_exam(): void
    {
        // View attempt
        $resView = $this->actingAs($this->otherTeacherUser)->get(route('teacher.results.show', $this->exam));
        $resView->assertStatus(403);

        // Publish attempt
        $resPub = $this->actingAs($this->otherTeacherUser)->post(route('teacher.exams.publish-results', $this->exam));
        $resPub->assertStatus(403);
    }

    public function test_admin_can_view_and_publish_any_exam(): void
    {
        $resView = $this->actingAs($this->adminUser)->get(route('admin.results.show', $this->exam));
        $resView->assertStatus(200);

        $resPub = $this->actingAs($this->adminUser)->post(route('admin.exams.publish-results', $this->exam));
        $resPub->assertSessionHas('success');
        $this->assertTrue($this->exam->fresh()->is_results_published);
    }

    public function test_teacher_can_view_student_attempt_answer_sheet(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(
            route('teacher.results.attempts.detail', ['exam' => $this->exam, 'attempt' => $this->attempt])
        );
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Apa kepanjangan SQL?');
        $response->assertSee('Structured Query Language');
        $response->assertSee('Perintah untuk mengambil data di SQL?');
        $response->assertSee('SELECT');
        $response->assertSee('80.0');
    }

    public function test_student_results_index_masks_score_when_not_published(): void
    {
        $this->exam->update(['is_results_published' => false]);

        $response = $this->actingAs($this->studentUser)->get(route('student.results.index'));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Akhir Semester Database');
        $response->assertSee('Menunggu Pengumuman');
        $response->assertSee('Terkunci');
        // Student cannot see the score number
        $response->assertDontSee('80.0');
    }

    public function test_student_results_index_reveals_score_when_published(): void
    {
        $this->exam->update(['is_results_published' => true]);

        $response = $this->actingAs($this->studentUser)->get(route('student.results.index'));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Akhir Semester Database');
        $response->assertSee('80.0');
        $response->assertSee('Tuntas KKM');
        $response->assertSee(route('student.results.show', $this->exam));
    }

    public function test_student_cannot_view_detailed_result_if_not_published(): void
    {
        $this->exam->update(['is_results_published' => false]);

        $response = $this->actingAs($this->studentUser)->get(route('student.results.show', $this->exam));
        $response->assertRedirect(route('student.results.index'));
        $response->assertSessionHas('error');
    }

    public function test_student_can_view_detailed_result_and_answers_when_published(): void
    {
        $this->exam->update(['is_results_published' => true]);

        $response = $this->actingAs($this->studentUser)->get(route('student.results.show', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Akhir Semester Database');
        $response->assertSee('80.0');
        $response->assertSee('Apa kepanjangan SQL?');
        $response->assertSee('Structured Query Language');
        $response->assertSee('Kunci Jawaban');
    }

    public function test_student_not_assigned_to_exam_cannot_view_results(): void
    {
        $otherStudentUser = User::factory()->student()->create(['username' => 'other_student_test']);
        Student::create([
            'user_id' => $otherStudentUser->id,
            'nis' => '88002',
            'name' => 'Siswa Luar',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->exam->update(['is_results_published' => true]);

        $response = $this->actingAs($otherStudentUser)->get(route('student.results.show', $this->exam));
        $response->assertStatus(404);
    }
}
