<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
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

class ExamItemAnalysisTest extends TestCase
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
    private ExamQuestion $q1;
    private ExamQuestion $q2;
    private ExamQuestionOption $q1OptA;
    private ExamQuestionOption $q1OptB;
    private ExamQuestionOption $q1OptC;
    private ExamQuestionOption $q2OptA;
    private ExamQuestionOption $q2OptB;
    private ExamQuestionOption $q2OptC;
    private Student $student1;
    private Student $student2;
    private Student $student3;
    private ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringService = new ScoringService();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);

        $this->classA = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);
        $this->classB = Classroom::create(['name' => 'XII RPL 2', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->adminUser = User::factory()->admin()->create();

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Analisis', 'is_active' => true]);

        $this->otherTeacherUser = User::factory()->teacher()->create();
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'nip' => '198902022012022002', 'name' => 'Guru Lain', 'is_active' => true]);

        $this->subject = Subject::create(['code' => 'PROG', 'name' => 'Pemrograman Dasar', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-ITEM-01',
            'title' => 'Ujian Analisis Butir Soal',
            'duration' => 60,
            'start_at' => Carbon::now()->subHours(2),
            'end_at' => Carbon::now()->addHour(),
            'total_questions' => 2,
            'passing_grade' => 70.00,
            'status' => 'published',
            'is_results_published' => true,
            'random_question' => false,
            'random_answer' => false,
        ]);

        // Question 1 (Easy / Sedang: Key A)
        $this->q1 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Tipe data untuk bilangan bulat?', 'weight' => 2.0, 'order' => 1]);
        $this->q1OptA = ExamQuestionOption::create(['exam_question_id' => $this->q1->id, 'label' => 'A', 'option_text' => 'Integer', 'is_correct' => true, 'order' => 1]);
        $this->q1OptB = ExamQuestionOption::create(['exam_question_id' => $this->q1->id, 'label' => 'B', 'option_text' => 'String', 'is_correct' => false, 'order' => 2]);
        $this->q1OptC = ExamQuestionOption::create(['exam_question_id' => $this->q1->id, 'label' => 'C', 'option_text' => 'Boolean', 'is_correct' => false, 'order' => 3]);

        // Question 2 (Sukar: Key B)
        $this->q2 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Kompleksitas Binary Search?', 'weight' => 2.0, 'order' => 2]);
        $this->q2OptA = ExamQuestionOption::create(['exam_question_id' => $this->q2->id, 'label' => 'A', 'option_text' => 'O(n)', 'is_correct' => false, 'order' => 1]);
        $this->q2OptB = ExamQuestionOption::create(['exam_question_id' => $this->q2->id, 'label' => 'B', 'option_text' => 'O(log n)', 'is_correct' => true, 'order' => 2]);
        $this->q2OptC = ExamQuestionOption::create(['exam_question_id' => $this->q2->id, 'label' => 'C', 'option_text' => 'O(n^2)', 'is_correct' => false, 'order' => 3]);

        // Student 1 (Class A): Q1 -> A (Correct), Q2 -> B (Correct)
        $user1 = User::factory()->student()->create();
        $this->student1 = Student::create(['user_id' => $user1->id, 'classroom_id' => $this->classA->id, 'nis' => '91001', 'name' => 'Siswa Pintar', 'is_active' => true]);
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
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $this->q1->id, 'order' => 1, 'selected_exam_question_option_id' => $this->q1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $this->q2->id, 'order' => 2, 'selected_exam_question_option_id' => $this->q2OptB->id]);
        $this->scoringService->scoreAttempt($att1);

        // Student 2 (Class A): Q1 -> A (Correct), Q2 -> A (Incorrect)
        $user2 = User::factory()->student()->create();
        $this->student2 = Student::create(['user_id' => $user2->id, 'classroom_id' => $this->classA->id, 'nis' => '91002', 'name' => 'Siswa Sedang', 'is_active' => true]);
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
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $this->q1->id, 'order' => 1, 'selected_exam_question_option_id' => $this->q1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $this->q2->id, 'order' => 2, 'selected_exam_question_option_id' => $this->q2OptA->id]);
        $this->scoringService->scoreAttempt($att2);

        // Student 3 (Class B): Q1 -> B (Incorrect), Q2 -> null (Unanswered)
        $user3 = User::factory()->student()->create();
        $this->student3 = Student::create(['user_id' => $user3->id, 'classroom_id' => $this->classB->id, 'nis' => '91003', 'name' => 'Siswa Kurang', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student3->id, 'status' => 'completed']);
        $att3 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student3->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(50),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(30),
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $this->q1->id, 'order' => 1, 'selected_exam_question_option_id' => $this->q1OptB->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $this->q2->id, 'order' => 2, 'selected_exam_question_option_id' => null]);
        $this->scoringService->scoreAttempt($att3);
    }

    public function test_teacher_and_admin_can_view_item_analysis_page(): void
    {
        // Teacher
        $resTeacher = $this->actingAs($this->teacherUser)->get(route('teacher.reports.item-analysis', $this->exam));
        $resTeacher->assertStatus(200);
        $resTeacher->assertSee('Analisis Butir Soal');
        $resTeacher->assertSee('Ujian Analisis Butir Soal');
        $resTeacher->assertSee('Tipe data untuk bilangan bulat?');
        $resTeacher->assertSee('Kompleksitas Binary Search?');

        // Admin
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.item-analysis', $this->exam));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Analisis Butir Soal');
    }

    public function test_item_analysis_calculates_correct_and_incorrect_percentages(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.item-analysis', $this->exam));
        $response->assertStatus(200);

        // Q1: 2 correct out of 3 participants => 66.7%
        // Q1: 1 incorrect out of 3 participants => 33.3%
        $response->assertSee('66.7%');

        // Q2: 1 correct (33.3%), 1 incorrect (33.3%), 1 unanswered (33.3%)
        $response->assertSee('33.3%');
    }

    public function test_item_analysis_calculates_distractor_distribution_for_options_a_to_e(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.item-analysis', $this->exam));
        $response->assertStatus(200);

        // Options for Q1
        $response->assertSee('Integer');
        $response->assertSee('String');
        $response->assertSee('Boolean');

        // Distractor statuses
        $response->assertSee('Kunci Jawaban');
        $response->assertSee('Pengecoh Berfungsi');
        $response->assertSee('Pengecoh Kurang Efektif');

        // Option counts
        // 2 students chose Option A (Integer)
        $response->assertSee('2 Siswa');
        // 1 student chose Option B (String)
        $response->assertSee('1 Siswa');
    }

    public function test_item_analysis_assigns_difficulty_levels(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.item-analysis', $this->exam));
        $response->assertStatus(200);

        // Both Q1 (66.7%) and Q2 (33.3%) fall into Sedang (30% - 69.9%)
        $response->assertSee('Sedang');
        $response->assertSee('Tingkat Kesukaran');
    }

    public function test_item_analysis_classroom_filtering(): void
    {
        // Filter only Class A (student1 and student2)
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.item-analysis', [
            'exam' => $this->exam,
            'classroom_id' => $this->classA->id,
        ]));
        $response->assertStatus(200);

        // In Class A: both student1 and student2 answered Q1 correctly (2/2 => 100%) -> "Mudah"
        $response->assertSee('100%');
        $response->assertSee('Mudah');
    }

    public function test_teacher_cannot_view_item_analysis_of_other_teacher_unless_admin(): void
    {
        // Other teacher forbidden
        $resForbid = $this->actingAs($this->otherTeacherUser)->get(route('teacher.reports.item-analysis', $this->exam));
        $resForbid->assertStatus(403);

        // Admin allowed
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.item-analysis', $this->exam));
        $resAdmin->assertStatus(200);
    }
}
