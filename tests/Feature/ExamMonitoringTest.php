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
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $adminUser;
    private User $studentUser;
    private Classroom $classroom;
    private Subject $subject;
    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Monitoring', 'is_active' => true]);
        $this->adminUser = User::factory()->admin()->create();

        $this->subject = Subject::create(['code' => 'PWPB', 'name' => 'Pemrograman Web', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);
        $category = QuestionCategory::create(['subject_id' => $this->subject->id, 'code' => 'KAT-MON', 'name' => 'Kategori Monitoring', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-MON-01',
            'title' => 'Ujian Monitoring Berkala CBT',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'total_questions' => 2,
            'passing_grade' => 75.00,
            'status' => 'published',
            'random_question' => false,
            'random_answer' => false,
        ]);

        // Create 2 questions
        for ($i = 1; $i <= 2; $i++) {
            $bankQ = Question::create([
                'question_category_id' => $category->id,
                'teacher_id' => $this->teacher->id,
                'code' => "Q-MON-0{$i}",
                'question_text' => "Pertanyaan Monitoring Soal Ke-{$i}",
                'weight' => 50,
                'difficulty' => 'medium',
                'status' => 'active',
                'order' => $i,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $idx => $label) {
                QuestionOption::create([
                    'question_id' => $bankQ->id,
                    'label' => $label,
                    'option_text' => "Pilihan {$label} soal {$i}",
                    'is_correct' => $idx === 0,
                    'order' => $idx + 1,
                ]);
            }

            $eq = ExamQuestion::create([
                'exam_id' => $this->exam->id,
                'question_id' => $bankQ->id,
                'question_text' => $bankQ->question_text,
                'weight' => $bankQ->weight,
                'order' => $i,
            ]);

            foreach ($bankQ->options as $opt) {
                ExamQuestionOption::create([
                    'exam_question_id' => $eq->id,
                    'question_option_id' => $opt->id,
                    'label' => $opt->label,
                    'option_text' => $opt->option_text,
                    'is_correct' => $opt->is_correct,
                    'order' => $opt->order ?? 1,
                ]);
            }
        }
    }

    private function createStudentWithParticipant(string $nis, string $name): Student
    {
        $u = User::factory()->student()->create(['username' => 'siswa_' . $nis]);
        $s = Student::create([
            'user_id' => $u->id,
            'nis' => $nis,
            'name' => $name,
            'gender' => 'L',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s->id,
            'status' => 'assigned',
        ]);

        return $s;
    }

    public function test_teacher_can_view_monitoring_index_with_exam_summary_stats(): void
    {
        $s1 = $this->createStudentWithParticipant('10001', 'Siswa Satu');
        $s2 = $this->createStudentWithParticipant('10002', 'Siswa Dua');

        // S1 has in progress attempt
        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s1->id,
            'started_at' => Carbon::now()->subMinutes(5),
            'expires_at' => Carbon::now()->addMinutes(55),
            'status' => 'in_progress',
            'total_answered' => 1,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.monitoring.index'));

        $response->assertStatus(200);
        $response->assertSee('EXAM-MON-01');
        $response->assertSee('Ujian Monitoring Berkala CBT');
        $response->assertSee('Total: <strong>2</strong>', false);
        $response->assertSee('1 Mengerjakan');
        $response->assertSee('Pantau Ujian');
    }

    public function test_admin_can_view_all_exams_in_monitoring_index(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.monitoring.index'));

        $response->assertStatus(200);
        $response->assertSee('EXAM-MON-01');
    }

    public function test_teacher_cannot_monitor_another_teachers_exam(): void
    {
        $otherTeacherUser = User::factory()->teacher()->create();
        $otherTeacher = Teacher::create([
            'user_id' => $otherTeacherUser->id,
            'nip' => '199002022015022002',
            'name' => 'Guru Lain',
            'is_active' => true,
        ]);

        $otherExam = Exam::create([
            'academic_year_id' => $this->exam->academic_year_id,
            'semester_id' => $this->exam->semester_id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $otherTeacher->id,
            'code' => 'EXAM-OTHER-01',
            'title' => 'Ujian Guru Lain',
            'duration' => 45,
            'start_at' => Carbon::now()->subMinutes(5),
            'end_at' => Carbon::now()->addMinutes(40),
            'total_questions' => 1,
            'passing_grade' => 70.00,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.monitoring.show', $otherExam));

        $response->assertStatus(403);
    }

    public function test_teacher_and_admin_can_view_live_monitoring_dashboard(): void
    {
        $s1 = $this->createStudentWithParticipant('20001', 'Budi Pratama');

        $responseTeacher = $this->actingAs($this->teacherUser)
            ->get(route('teacher.monitoring.show', $this->exam));

        $responseTeacher->assertStatus(200);
        $responseTeacher->assertSee('Live Monitoring Pelaksanaan Ujian');
        $responseTeacher->assertSee('Budi Pratama');
        $responseTeacher->assertSee('Auto-Sync (5s)');
        $responseTeacher->assertSee('summaryTotalAssigned', false);
        $responseTeacher->assertSee('summaryInProgress', false);

        $responseAdmin = $this->actingAs($this->adminUser)
            ->get(route('admin.monitoring.show', $this->exam));

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Live Monitoring Pelaksanaan Ujian');
    }

    public function test_monitoring_live_data_api_endpoint_returns_json_for_polling(): void
    {
        $s1 = $this->createStudentWithParticipant('30001', 'Andi Siswa');
        $s2 = $this->createStudentWithParticipant('30002', 'Beni Siswa');

        // Andi in progress
        $att1 = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s1->id,
            'started_at' => Carbon::now()->subMinutes(10),
            'expires_at' => Carbon::now()->addMinutes(50),
            'status' => 'in_progress',
            'total_answered' => 1,
        ]);

        $firstEq = $this->exam->questions->first();
        $attQ = ExamAttemptQuestion::create([
            'exam_attempt_id' => $att1->id,
            'exam_question_id' => $firstEq->id,
            'order' => 1,
            'selected_exam_question_option_id' => $firstEq->options->first()->id,
            'is_doubtful' => false,
            'answered_at' => Carbon::now()->subMinutes(2),
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->getJson(route('teacher.monitoring.live-data', $this->exam));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_assigned' => 2,
                'not_started' => 1,
                'in_progress' => 1,
                'completed' => 0,
                'expired' => 0,
            ],
        ]);

        $response->assertJsonFragment([
            'student_id' => $s1->id,
            'name' => 'Andi Siswa',
            'status' => 'in_progress',
            'answered_count' => 1,
            'progress_percent' => 50,
        ]);

        $response->assertJsonFragment([
            'student_id' => $s2->id,
            'name' => 'Beni Siswa',
            'status' => 'not_started',
            'answered_count' => 0,
        ]);
    }

    public function test_attempt_detail_endpoint_returns_question_breakdown(): void
    {
        $s1 = $this->createStudentWithParticipant('40001', 'Citra Lestari');

        $attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $s1->id,
            'started_at' => Carbon::now()->subMinutes(15),
            'expires_at' => Carbon::now()->addMinutes(45),
            'status' => 'in_progress',
            'total_answered' => 1,
            'ip_address' => '192.168.1.100',
        ]);

        $eq1 = $this->exam->questions[0];
        $eq2 = $this->exam->questions[1];

        $attQ1 = ExamAttemptQuestion::create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $eq1->id,
            'order' => 1,
            'selected_exam_question_option_id' => $eq1->options[0]->id,
            'is_doubtful' => false,
            'answered_at' => Carbon::now()->subMinutes(5),
        ]);
        $attQ1->options()->create([
            'exam_question_option_id' => $eq1->options[0]->id,
            'display_label' => 'A',
            'order' => 1,
        ]);

        $attQ2 = ExamAttemptQuestion::create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $eq2->id,
            'order' => 2,
            'selected_exam_question_option_id' => null,
            'is_doubtful' => true,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->getJson(route('teacher.monitoring.attempts.detail', [$this->exam, $attempt]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'student' => [
                'name' => 'Citra Lestari',
                'nis' => '40001',
            ],
            'attempt' => [
                'id' => $attempt->id,
                'status' => 'in_progress',
                'ip_address' => '192.168.1.100',
            ],
        ]);

        $response->assertJsonFragment([
            'order' => 1,
            'is_answered' => true,
            'selected_option' => 'A',
            'is_doubtful' => false,
        ]);

        $response->assertJsonFragment([
            'order' => 2,
            'is_answered' => false,
            'selected_option' => '-',
            'is_doubtful' => true,
        ]);
    }

    public function test_student_role_cannot_access_monitoring(): void
    {
        $u = User::factory()->student()->create();

        $response1 = $this->actingAs($u)
            ->get(route('teacher.monitoring.index'));
        $response1->assertStatus(403);

        $response2 = $this->actingAs($u)
            ->get(route('teacher.monitoring.show', $this->exam));
        $response2->assertStatus(403);
    }
}
