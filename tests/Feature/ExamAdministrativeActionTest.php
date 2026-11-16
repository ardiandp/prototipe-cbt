<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
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

class ExamAdministrativeActionTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $adminUser;
    private User $studentUser;
    private Student $student;
    private Classroom $classroom;
    private Exam $exam;
    private ExamParticipant $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $this->teacherUser = User::factory()->teacher()->create();
        $this->teacher = Teacher::create(['user_id' => $this->teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Guru Pengawas CBT', 'is_active' => true]);
        $this->adminUser = User::factory()->admin()->create();

        $subject = Subject::create(['code' => 'PWPB', 'name' => 'Pemrograman Web', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);
        $category = QuestionCategory::create(['subject_id' => $subject->id, 'code' => 'KAT-ADM', 'name' => 'Kategori Admin Action', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'EXAM-ADM-01',
            'title' => 'Ujian Akhir Semester dengan Aksi Administratif',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'total_questions' => 2,
            'passing_grade' => 75.00,
            'status' => 'published',
            'random_question' => false,
            'random_answer' => false,
        ]);

        $this->studentUser = User::factory()->student()->create(['username' => 'siswa_action_test']);
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'nis' => '88801',
            'name' => 'Siswa Uji Administratif',
            'gender' => 'L',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->participant = ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'assigned',
        ]);

        // Create 2 questions
        for ($i = 1; $i <= 2; $i++) {
            $bankQ = Question::create([
                'question_category_id' => $category->id,
                'teacher_id' => $this->teacher->id,
                'code' => "Q-ADM-0{$i}",
                'question_text' => "Pertanyaan Admin Soal Ke-{$i}",
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

    private function createInProgressAttempt(): ExamAttempt
    {
        $attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(15),
            'expires_at' => Carbon::now()->addMinutes(45),
            'status' => 'in_progress',
            'total_answered' => 1,
        ]);

        $order = 1;
        foreach ($this->exam->questions as $eq) {
            $attQ = ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $eq->id,
                'order' => $order++,
                'selected_exam_question_option_id' => $order === 2 ? $eq->options->first()->id : null,
                'is_doubtful' => false,
            ]);
        }

        $this->participant->update(['status' => 'present']);

        return $attempt;
    }

    public function test_proctor_can_force_finish_in_progress_attempt_with_valid_reason(): void
    {
        $attempt = $this->createInProgressAttempt();

        $reason = 'Siswa mendadak sakit dan harus dievakuasi ke UKS sekolah';

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => $reason,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
        ]);

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertNotNull($attempt->completed_at);

        $this->participant->refresh();
        $this->assertEquals('completed', $this->participant->status);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_finalize',
            'user_id' => $this->teacherUser->id,
            'auditable_type' => ExamAttempt::class,
            'auditable_id' => $attempt->id,
            'reason' => $reason,
        ]);
    }

    public function test_force_finish_fails_if_reason_is_missing_or_too_short(): void
    {
        $attempt = $this->createInProgressAttempt();

        // Empty reason
        $response1 = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => '',
            ]);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors(['reason']);

        // Reason too short
        $response2 = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => 'abc',
            ]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['reason']);

        $attempt->refresh();
        $this->assertEquals('in_progress', $attempt->status);
    }

    public function test_force_finish_fails_if_attempt_is_already_completed(): void
    {
        $attempt = $this->createInProgressAttempt();
        $attempt->markAsCompleted();

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => 'Siswa sudah menyelesaikan ujian lebih awal',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_proctor_can_authorize_retake_with_valid_reason(): void
    {
        $attempt = $this->createInProgressAttempt();
        $attempt->markAsCompleted();

        $reason = 'PC laboratorium mati total akibat korsleting listrik meja 12';

        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.participants.retake', [$this->exam, $this->participant]), [
                'reason' => $reason,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->participant->refresh();
        $this->assertTrue($this->participant->can_retake);
        $this->assertEquals($reason, $this->participant->retake_reason);
        $this->assertEquals($this->teacherUser->id, $this->participant->retake_authorized_by);
        $this->assertNotNull($this->participant->retake_authorized_at);
        $this->assertEquals('assigned', $this->participant->status);

        // Verify Audit Log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'retake_authorized',
            'user_id' => $this->teacherUser->id,
            'auditable_type' => ExamParticipant::class,
            'auditable_id' => $this->participant->id,
            'reason' => $reason,
        ]);
    }

    public function test_retake_authorization_fails_if_reason_is_missing(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.participants.retake', [$this->exam, $this->participant]), [
                'reason' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);

        $this->participant->refresh();
        $this->assertFalse($this->participant->can_retake);
    }

    public function test_student_can_start_new_attempt_after_retake_authorization(): void
    {
        // 1. Initial attempt is completed
        $attempt1 = $this->createInProgressAttempt();
        $attempt1->markAsCompleted();

        // Student tries to start again before retake is authorized -> blocked
        $blockedResp = $this->actingAs($this->studentUser)
            ->post(route('student.exams.start', $this->exam));
        $blockedResp->assertRedirect(route('student.exams.show', $this->exam));
        $blockedResp->assertSessionHas('error');

        // 2. Proctor authorizes retake
        $reason = 'Browser hang fatal dan tidak dapat dikembalikan ke sesi lama';
        $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.participants.retake', [$this->exam, $this->participant]), [
                'reason' => $reason,
            ]);

        // 3. Student views portal and sees retake banner
        $portalResp = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $this->exam));
        $portalResp->assertStatus(200);
        $portalResp->assertSee('Izin Ujian Ulang (Retake) Diberikan Pengawas');
        $portalResp->assertSee($reason);
        $portalResp->assertSee('Mulai Ujian Ulang (Retake)');

        // 4. Student starts retake attempt
        $startResp = $this->actingAs($this->studentUser)
            ->post(route('student.exams.start', $this->exam));
        $startResp->assertRedirect();

        // 5. Verify attempt 2 was created
        $attempt2 = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->where('attempt_number', 2)
            ->first();

        $this->assertNotNull($attempt2);
        $this->assertEquals('in_progress', $attempt2->status);

        // Verify retake flag was cleared on participant
        $this->participant->refresh();
        $this->assertFalse($this->participant->can_retake);
        $this->assertEquals('present', $this->participant->status);
    }

    public function test_audit_logs_endpoint_returns_administrative_history_for_exam(): void
    {
        $attempt = $this->createInProgressAttempt();

        // Perform force finish
        $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => 'Alasan finalisasi administratif pengawas',
            ]);

        // Perform retake
        $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.monitoring.participants.retake', [$this->exam, $this->participant]), [
                'reason' => 'Alasan izin ujian ulang peserta',
            ]);

        // Query audit logs
        $response = $this->actingAs($this->teacherUser)
            ->getJson(route('teacher.monitoring.audit-logs', $this->exam));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $response->assertJsonFragment([
            'action' => 'admin_finalize',
            'action_label' => 'Finalisasi Administratif',
            'reason' => 'Alasan finalisasi administratif pengawas',
        ]);

        $response->assertJsonFragment([
            'action' => 'retake_authorized',
            'action_label' => 'Izin Ujian Ulang (Retake)',
            'reason' => 'Alasan izin ujian ulang peserta',
        ]);
    }

    public function test_unauthorized_teacher_cannot_perform_administrative_actions_on_other_exams(): void
    {
        $attempt = $this->createInProgressAttempt();

        $otherTeacherUser = User::factory()->teacher()->create();
        Teacher::create([
            'user_id' => $otherTeacherUser->id,
            'nip' => '199002022015022002',
            'name' => 'Guru Lain',
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherTeacherUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => 'Coba finalisasi oleh guru lain',
            ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_perform_administrative_actions(): void
    {
        $attempt = $this->createInProgressAttempt();

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('teacher.monitoring.attempts.force-finish', [$this->exam, $attempt]), [
                'reason' => 'Coba finalisasi oleh siswa',
            ]);

        $response->assertStatus(403);
    }
}
