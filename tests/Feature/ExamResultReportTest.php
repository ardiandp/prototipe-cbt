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
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamResultReportTest extends TestCase
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
    private Student $student4;
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
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Laporan', 'is_active' => true]);

        $this->otherTeacherUser = User::factory()->teacher()->create();
        $this->otherTeacher = Teacher::create(['user_id' => $this->otherTeacherUser->id, 'nip' => '198902022012022002', 'name' => 'Guru Lain', 'is_active' => true]);

        $this->subject = Subject::create(['code' => 'PWPB', 'name' => 'Pemrograman Web', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-REP-01',
            'title' => 'Asesmen Komprehensif Web',
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

        // Questions
        $eq1 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Soal 1 Web', 'weight' => 2.0, 'order' => 1]);
        $eq1OptA = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'A', 'option_text' => 'Kunci 1', 'is_correct' => true, 'order' => 1]);
        $eq1OptB = ExamQuestionOption::create(['exam_question_id' => $eq1->id, 'label' => 'B', 'option_text' => 'Salah 1', 'is_correct' => false, 'order' => 2]);

        $eq2 = ExamQuestion::create(['exam_id' => $this->exam->id, 'question_text' => 'Soal 2 Web', 'weight' => 2.0, 'order' => 2]);
        $eq2OptA = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'A', 'option_text' => 'Kunci 2', 'is_correct' => true, 'order' => 1]);
        $eq2OptB = ExamQuestionOption::create(['exam_question_id' => $eq2->id, 'label' => 'B', 'option_text' => 'Salah 2', 'is_correct' => false, 'order' => 2]);

        // Student 1: Class A, Score 100, Duration 15m (Rank 1)
        $user1 = User::factory()->student()->create();
        $this->student1 = Student::create(['user_id' => $user1->id, 'classroom_id' => $this->classA->id, 'nis' => '90001', 'name' => 'Achmad Juara', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student1->id, 'status' => 'completed']);
        $att1 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student1->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(60),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(45), // 15 mins
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att1->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptA->id]);
        $this->scoringService->scoreAttempt($att1);

        // Student 2: Class A, Score 50, Duration 20m (Rank 2 between 50s because faster)
        $user2 = User::factory()->student()->create();
        $this->student2 = Student::create(['user_id' => $user2->id, 'classroom_id' => $this->classA->id, 'nis' => '90002', 'name' => 'Bambang Cepat', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student2->id, 'status' => 'completed']);
        $att2 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student2->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(60),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(40), // 20 mins
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att2->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptB->id]);
        $this->scoringService->scoreAttempt($att2);

        // Student 3: Class B, Score 50, Duration 30m (Rank 3)
        $user3 = User::factory()->student()->create();
        $this->student3 = Student::create(['user_id' => $user3->id, 'classroom_id' => $this->classB->id, 'nis' => '90003', 'name' => 'Citra Santai', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student3->id, 'status' => 'completed']);
        $att3 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student3->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(60),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(30), // 30 mins
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptA->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att3->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptB->id]);
        $this->scoringService->scoreAttempt($att3);

        // Student 4: Class B, Score 0, Duration 10m (Rank 4)
        $user4 = User::factory()->student()->create();
        $this->student4 = Student::create(['user_id' => $user4->id, 'classroom_id' => $this->classB->id, 'nis' => '90004', 'name' => 'Doni Kurang', 'is_active' => true]);
        ExamParticipant::create(['exam_id' => $this->exam->id, 'student_id' => $this->student4->id, 'status' => 'completed']);
        $att4 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student4->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(60),
            'expires_at' => Carbon::now(),
            'completed_at' => Carbon::now()->subMinutes(50), // 10 mins
            'status' => 'completed',
        ]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att4->id, 'exam_question_id' => $eq1->id, 'order' => 1, 'selected_exam_question_option_id' => $eq1OptB->id]);
        ExamAttemptQuestion::create(['exam_attempt_id' => $att4->id, 'exam_question_id' => $eq2->id, 'order' => 2, 'selected_exam_question_option_id' => $eq2OptB->id]);
        $this->scoringService->scoreAttempt($att4);
    }

    public function test_teacher_and_admin_can_view_reports_hub_index(): void
    {
        // Teacher
        $resTeacher = $this->actingAs($this->teacherUser)->get(route('teacher.reports.index'));
        $resTeacher->assertStatus(200);
        $resTeacher->assertSee('Asesmen Komprehensif Web');
        $resTeacher->assertSee('EXAM-REP-01');
        $resTeacher->assertSee('XII RPL 1');
        $resTeacher->assertSee('XII RPL 2');

        // Admin
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.index'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Asesmen Komprehensif Web');
    }

    public function test_teacher_can_view_exam_report_with_summary_and_statistics(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.exam', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('Asesmen Komprehensif Web');
        $response->assertSee('EXAM-REP-01');

        // Summary Statistics (Scores: 100, 50, 50, 0 => Avg: 50.0, Max: 100.0, Min: 0.0)
        $response->assertSee('50.0'); // Average
        $response->assertSee('100.0'); // Max
        $response->assertSee('0.0'); // Min
        $response->assertSee('25%'); // Pass rate (1 of 4 passed >= 75)
    }

    public function test_exam_report_displays_score_distribution(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.exam', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('Distribusi Frekuensi Nilai');
        $response->assertSee('0 - 49');
        $response->assertSee('50 - 69');
        $response->assertSee('70 - 79');
        $response->assertSee('80 - 89');
        $response->assertSee('90 - 100');
    }

    public function test_exam_report_displays_student_rankings_with_top_medals_and_duration(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.exam', $this->exam));
        $response->assertStatus(200);

        // Rank 1: Achmad Juara (Gold medal)
        $response->assertSee('Achmad Juara');
        $response->assertSee('90001');
        $response->assertSee('15m 00s');

        // Rank 2: Bambang Cepat (Faster than Citra)
        $response->assertSee('Bambang Cepat');
        $response->assertSee('20m 00s');

        // Rank 3: Citra Santai
        $response->assertSee('Citra Santai');
        $response->assertSee('30m 00s');

        // Rank 4: Doni Kurang
        $response->assertSee('Doni Kurang');
        $response->assertSee('10m 00s');
    }

    public function test_exam_report_displays_class_breakdown_and_filtering(): void
    {
        // View all classes
        $response = $this->actingAs($this->teacherUser)->get(route('teacher.reports.exam', $this->exam));
        $response->assertStatus(200);
        $response->assertSee('XII RPL 1');
        $response->assertSee('XII RPL 2');

        // Filter only XII RPL 1
        $filterResponse = $this->actingAs($this->teacherUser)->get(route('teacher.reports.exam', [
            'exam' => $this->exam,
            'classroom_id' => $this->classA->id,
        ]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Achmad Juara');
        $filterResponse->assertSee('Bambang Cepat');
        // Students from Class B should not be in the filtered ranking table
        $filterResponse->assertDontSee('Citra Santai');
        $filterResponse->assertDontSee('Doni Kurang');
    }

    public function test_teacher_and_admin_can_view_classroom_report(): void
    {
        // Teacher
        $resTeacher = $this->actingAs($this->teacherUser)->get(route('teacher.reports.class', $this->classA));
        $resTeacher->assertStatus(200);
        $resTeacher->assertSee('XII RPL 1');
        $resTeacher->assertSee('Asesmen Komprehensif Web');

        // Admin
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.class', $this->classB));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('XII RPL 2');
        $resAdmin->assertSee('Asesmen Komprehensif Web');
    }

    public function test_teacher_cannot_view_exam_report_of_other_teacher_unless_admin(): void
    {
        // Other teacher forbidden
        $resForbid = $this->actingAs($this->otherTeacherUser)->get(route('teacher.reports.exam', $this->exam));
        $resForbid->assertStatus(403);

        // Admin allowed
        $resAdmin = $this->actingAs($this->adminUser)->get(route('admin.reports.exam', $this->exam));
        $resAdmin->assertStatus(200);
    }
}
