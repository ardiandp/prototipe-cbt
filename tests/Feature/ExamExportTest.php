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
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamExportTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private Classroom $classA;
    private Classroom $classB;
    private Subject $subject;
    private Exam $exam;
    private Student $student1;
    private Student $student2;
    private Student $student3;
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringService = new ScoringService();

        SchoolProfile::firstOrCreate([], [
            'name' => 'SMK Negeri 1 Contoh CBT',
            'npsn' => '20199887',
            'address' => 'Jl. Merdeka No. 45, Kota Ujian',
            'email' => 'smkn1@cbt.sch.id',
            'phone' => '021-99887766',
            'principal_name' => 'Dr. Bambang Pamungkas, M.Pd.',
        ]);

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);

        $this->classA = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);
        $this->classB = Classroom::create(['name' => 'XII RPL 2', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->adminUser = User::factory()->admin()->create();

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Export', 'is_active' => true]);

        $this->otherTeacherUser = User::factory()->teacher()->create();
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'nip' => '198902022012022002', 'name' => 'Guru Lain', 'is_active' => true]);

        $this->subject = Subject::create(['code' => 'KIM', 'name' => 'Kimia Komputasi', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-EXP-01',
            'title' => 'Asesmen Akhir Semester Kimia',
            'duration' => 60,
            'start_at' => Carbon::now()->subHours(2),
            'end_at' => Carbon::now()->addHour(),
            'total_questions' => 2,
            'passing_grade' => 75.00,
            'status' => 'published',
            'is_results_published' => true,
            'random_question' => false,
            'random_answer' => false,
        ]);

        $eq1 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Rumus molekul air?', 'weight' => 2.0, 'order' => 1]);
        $eq1OptA = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'A', 'option_text' => 'H2O', 'is_correct' => true, 'order' => 1]);
        $eq1OptB = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'B', 'option_text' => 'CO2', 'is_correct' => false, 'order' => 2]);

        $eq2 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Simbol kimia emas?', 'weight' => 2.0, 'order' => 2]);
        $eq2OptA = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'A', 'option_text' => 'Au', 'is_correct' => true, 'order' => 1]);
        $eq2OptB = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'B', 'option_text' => 'Ag', 'is_correct' => false, 'order' => 2]);

        // Student 1 (Class A): Score 100 (Passed)
        $user1 = User::factory()->student()->create();
        $this->student1 = Student::create(['user_id' => $user1->id, 'classroom_id' => $this->classA->id, 'nis' => '92001', 'name' => 'Budi Kimia', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student1->id, 'status' => 'completed']);
        $att1 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student1->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(50),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(20),
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptA->id]);
        $this->scoringService->scoreAttempt($att1);

        // Student 2 (Class A): Score 50 (Failed)
        $user2 = User::factory()->student()->create();
        $this->student2 = Student::create(['user_id' => $user2->id, 'classroom_id' => $this->classA->id, 'nis' => '92002', 'name' => 'Ani Percobaan', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student2->id, 'status' => 'completed']);
        $att2 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student2->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(50),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(25),
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptB->id]);
        $this->scoringService->scoreAttempt($att2);

        // Student 3 (Class B): Score 100 (Passed)
        $user3 = User::factory()->student()->create();
        $this->student3 = Student::create(['user_id' => $user3->id, 'classroom_id' => $this->classB->id, 'nis' => '92003', 'name' => 'Candra Logam', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student3->id, 'status' => 'completed']);
        $att3 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student3->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(50),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(15),
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptA->id]);
        $this->scoringService->scoreAttempt($att3);
    }

    public function test_teacher_and_admin_can_export_exam_results_to_csv(): void
    {
        // Teacher export
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.export-csv', $this->exam));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Rekap_Nilai_' . $this->exam->code, $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('CBT SCHOOL - REKAPITULASI HASIL ASESMEN UJIAN', $content);
        $this->assertStringContainsString('Budi Kimia', $content);
        $this->assertStringContainsString('Ani Percobaan', $content);
        $this->assertStringContainsString('Candra Logam', $content);
        $this->assertStringContainsString('Lulus / Tuntas KKM', $content);
        $this->assertStringContainsString('Remedial / Di Bawah KKM', $content);

        // Admin export
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.export-csv', $this->exam));
        $resAdmin->assertStatus(200);
        $resAdmin->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_export_csv_respects_classroom_and_status_filters(): void
    {
        // Filter by classroom (Class A only)
        $resClass = $this->actingAs($this->teacherUser)->get(route('teacher.reports.export-csv', [
            'exam' => $this->exam,
            'classroom_id' => $this->classA->id,
        ]));
        $contentClass = $resClass->streamedContent();
        $this->assertStringContainsString('Budi Kimia', $contentClass);
        $this->assertStringContainsString('Ani Percobaan', $contentClass);
        $this->assertStringNotContainsString('Candra Logam', $contentClass);

        // Filter by status (Passed only)
        $resPassed = $this->actingAs($this->teacherUser)->get(route('teacher.reports.export-csv', [
            'exam' => $this->exam,
            'status' => 'passed',
        ]));
        $contentPassed = $resPassed->streamedContent();
        $this->assertStringContainsString('Budi Kimia', $contentPassed);
        $this->assertStringContainsString('Candra Logam', $contentPassed);
        $this->assertStringNotContainsString('Ani Percobaan', $contentPassed);

        // Filter by status (Failed only)
        $resFailed = $this->actingAs($this->teacherUser)->get(route('teacher.reports.export-csv', [
            'exam' => $this->exam,
            'status' => 'failed',
        ]));
        $contentFailed = $resFailed->streamedContent();
        $this->assertStringContainsString('Ani Percobaan', $contentFailed);
        $this->assertStringNotContainsString('Budi Kimia', $contentFailed);
        $this->assertStringNotContainsString('Candra Logam', $contentFailed);
    }

    public function test_teacher_and_admin_can_export_item_analysis_to_csv(): void
    {
        // Teacher export
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.export-item-analysis-csv', $this->exam));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Analisis_Butir_' . $this->exam->code, $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('CBT SCHOOL - ANALISIS BUTIR SOAL (ITEM ANALYSIS)', $content);
        $this->assertStringContainsString('Rumus molekul air?', $content);
        $this->assertStringContainsString('Simbol kimia emas?', $content);

        // Admin export
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.export-item-analysis-csv', $this->exam));
        $resAdmin->assertStatus(200);
    }

    public function test_teacher_and_admin_can_view_printable_report_with_school_letterhead(): void
    {
        // Teacher print view
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.print', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('SMK Negeri 1 Contoh CBT');
        $response->assertSee('NPSN: 20199887');
        $response->assertSee('Dr. Bambang Pamungkas, M.Pd.');
        $response->assertSee('LEMBAR REKAPITULASI HASIL ASESMEN UJIAN');
        $response->assertSee('Asesmen Akhir Semester Kimia');
        $response->assertSee('Budi Kimia');
        $response->assertSee('Candra Logam');
        $response->assertSee('Guru Pengampu Mata Pelajaran');

        // Admin print view
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.print', $this->exam));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('SMK Negeri 1 Contoh CBT');
    }

    public function test_teacher_cannot_export_or_print_other_teachers_exam_unless_admin(): void
    {
        // CSV
        $resCsv = $this->actingAs($this->otherTeacherUser)->get(route('teacher.reports.export-csv', $this->exam));
        $resCsv->assertStatus(403);

        // Item Analysis CSV
        $resItem = $this->actingAs($this->otherTeacherUser)->get(route('teacher.reports.export-item-analysis-csv', $this->exam));
        $resItem->assertStatus(403);

        // Print
        $resPrint = $this->actingAs($this->otherTeacherUser)->get(route('teacher.reports.print', $this->exam));
        $resPrint->assertStatus(403);
    }
}
