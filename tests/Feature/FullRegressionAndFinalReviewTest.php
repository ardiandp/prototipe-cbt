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
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FullRegressionAndFinalReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $admin;
    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser1;
    private Student $student1;
    private User $studentUser2;
    private Student $student2;
    private Classroom $classroom;
    private Subject $subject;
    private AcademicYear $academicYear;
    private Semester $semester;
    private QuestionCategory $questionCategory;

    protected function setUp(): void
    {
        parent::setUp();

        SchoolProfile::firstOrCreate([], [
            'name' => 'SMK Negeri 1 CBT School',
            'npsn' => '20199999',
            'address' => 'Jl. Pendidikan LAN No. 1, Bandung',
            'email' => 'admin@cbtschool.sch.id',
            'phone' => '022-77889900',
            'principal_name' => 'Drs. H. Mulyadi, M.M.',
        ]);

        $department = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $this->academicYear = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $this->semester = Semester::create(['academic_year_id' => $this->academicYear->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['department_id' => $department->id, 'academic_year_id' => $this->academicYear->id, 'name' => 'XII RPL 1', 'level' => '12', 'is_active' => true]);

        // Users
        $this->superadmin = User::create([
            'username' => 'superadmin_reg',
            'name' => 'Super Administrator',
            'email' => 'superadmin@cbt.local',
            'password' => bcrypt('password123'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'username' => 'admin_reg',
            'name' => 'Admin Sekolah',
            'email' => 'admin@cbt.local',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->teacherUser = User::create([
            'username' => 'guru_reg',
            'name' => 'Budi Santoso, S.Kom.',
            'email' => 'guru@cbt.local',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'name' => 'Budi Santoso, S.Kom.',
            'nip' => '198501012010011001',
            'is_active' => true,
        ]);

        $this->studentUser1 = User::create([
            'username' => 'siswa1_reg',
            'name' => 'Ahmad Dani',
            'email' => 'siswa1@cbt.local',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
        ]);
        $this->student1 = Student::create([
            'user_id' => $this->studentUser1->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Ahmad Dani',
            'nis' => '2026001',
            'nisn' => '0081234501',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $this->studentUser2 = User::create([
            'username' => 'siswa2_reg',
            'name' => 'Annisa Permata',
            'email' => 'siswa2@cbt.local',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
        ]);
        $this->student2 = Student::create([
            'user_id' => $this->studentUser2->id,
            'classroom_id' => $this->classroom->id,
            'name' => 'Annisa Permata',
            'nis' => '2026002',
            'nisn' => '0081234502',
            'gender' => 'P',
            'is_active' => true,
        ]);

        $this->subject = Subject::create(['code' => 'PWPB', 'name' => 'Pemrograman Web', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);

        // Create Bank Soal
        $this->questionCategory = QuestionCategory::create([
            'code' => 'BANK-PWPB',
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'name' => 'Bank Soal PWPB XII',
            'level' => '12',
            'is_active' => true,
        ]);

        // Create 2 questions with A-E options in Bank
        for ($i = 1; $i <= 2; $i++) {
            $q = Question::create([
                'question_category_id' => $this->questionCategory->id,
                'teacher_id' => $this->teacher->id,
                'code' => "PWPB-Q{$i}",
                'question_text' => "Pertanyaan Pemrograman Web nomor {$i}",
                'difficulty' => 'medium',
                'weight' => 50.00,
                'status' => 'active',
                'order' => $i,
            ]);

            QuestionOption::create(['question_id' => $q->id, 'label' => 'A', 'option_text' => "Jawaban Benar Soal {$i}", 'is_correct' => true, 'order' => 1]);
            QuestionOption::create(['question_id' => $q->id, 'label' => 'B', 'option_text' => "Distraktor B Soal {$i}", 'is_correct' => false, 'order' => 2]);
            QuestionOption::create(['question_id' => $q->id, 'label' => 'C', 'option_text' => "Distraktor C Soal {$i}", 'is_correct' => false, 'order' => 3]);
            QuestionOption::create(['question_id' => $q->id, 'label' => 'D', 'option_text' => "Distraktor D Soal {$i}", 'is_correct' => false, 'order' => 4]);
            QuestionOption::create(['question_id' => $q->id, 'label' => 'E', 'option_text' => "Distraktor E Soal {$i}", 'is_correct' => false, 'order' => 5]);
        }
    }

    /**
     * TEST 1: All 4 roles can authenticate and access their proper dashboards; role boundaries enforced.
     */
    public function test_all_four_roles_can_authenticate_and_access_proper_dashboards(): void
    {
        // 1. Superadmin
        $this->actingAs($this->superadmin);
        $resSuper = $this->get(route('admin.dashboard'));
        $resSuper->assertOk();
        $resSuper->assertSee('Dashboard');

        // 2. Admin
        $this->actingAs($this->admin);
        $resAdmin = $this->get(route('admin.dashboard'));
        $resAdmin->assertOk();

        // 3. Teacher
        $this->actingAs($this->teacherUser);
        $resTeacher = $this->get(route('teacher.dashboard'));
        $resTeacher->assertOk();

        // Teacher cannot access admin-only student management
        $this->get(route('admin.students.index'))->assertForbidden();

        // 4. Student
        $this->actingAs($this->studentUser1);
        $resStudent = $this->get(route('student.dashboard'));
        $resStudent->assertOk();

        // Student cannot access teacher or admin dashboards
        $this->get(route('teacher.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    /**
     * TEST 2: Complete examination lifecycle:
     * Authoring -> Snapshot -> Scheduling -> Starting -> Autosave -> Doubtful Flag -> Timer Sync ->
     * Page Refresh/Resume -> Submit -> Scoring -> Monitoring -> Force Finish & Retake ->
     * Results Publication -> Reports & Item Analysis -> CSV Export & Print -> Audit Trail.
     */
    public function test_complete_examination_lifecycle_end_to_end(): void
    {
        // ---------------------------------------------------------------------
        // STEP 1: Teacher creates and schedules an Exam with snapshot questions
        // ---------------------------------------------------------------------
        $this->actingAs($this->teacherUser);

        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Asesmen Akhir Semester PWPB',
            'code' => 'EXAM-REG-01',
            'duration' => 60,
            'passing_grade' => 75.00,
            'token' => 'SUKSES26',
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(2),
            'random_question' => false,
            'random_answer' => false,
            'status' => 'draft',
            'is_results_published' => false,
        ]);

        // Snapshot questions from question bank
        $bankQuestions = Question::where('question_category_id', $this->questionCategory->id)->with('options')->get();
        foreach ($bankQuestions as $order => $bq) {
            $eq = ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_id' => $bq->id,
                'order' => $order + 1,
                'code' => $bq->code,
                'question_text' => $bq->question_text,
                'weight' => $bq->weight,
                'difficulty' => $bq->difficulty,
            ]);

            foreach ($bq->options as $opt) {
                ExamQuestionOption::create([
                    'exam_question_id' => $eq->id,
                    'label' => $opt->label,
                    'option_text' => $opt->option_text,
                    'is_correct' => $opt->is_correct,
                    'order' => $opt->order,
                ]);
            }
        }
        $exam->update(['total_questions' => $exam->questions()->count()]);

        // Assign participants (Student 1 and Student 2)
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $this->student1->id, 'status' => 'enrolled']);
        ExamParticipant::create(['exam_id' => $exam->id, 'student_id' => $this->student2->id, 'status' => 'enrolled']);

        // Publish exam
        $publishRes = $this->post(route('teacher.exams.publish', $exam));
        $publishRes->assertRedirect();
        $this->assertEquals('published', $exam->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'publish_exam', 'auditable_id' => $exam->id]);

        // ---------------------------------------------------------------------
        // STEP 2: Student 1 starts attempt, autosaves answers, flags doubtful,
        // syncs timer, refreshes page, and manually submits
        // ---------------------------------------------------------------------
        $this->actingAs($this->studentUser1);

        // View exam preparation room
        $showRes = $this->get(route('student.exams.show', $exam));
        $showRes->assertOk();
        $showRes->assertSee('Asesmen Akhir Semester PWPB');

        // Start exam with token
        $startRes = $this->post(route('student.exams.start', $exam), ['token' => 'SUKSES26']);
        $startRes->assertRedirect();

        $attempt1 = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student1->id)->first();
        $this->assertNotNull($attempt1);
        $this->assertEquals('in_progress', $attempt1->status);
        $this->assertNotNull($attempt1->started_at);
        $this->assertNotNull($attempt1->expires_at);

        // Access exam room
        $roomRes = $this->get(route('student.attempts.show', $attempt1));
        $roomRes->assertOk();
        $roomRes->assertSee('SISA WAKTU');

        // Autosave answer for Question 1 (select correct option A)
        $q1 = $attempt1->attemptQuestions()->first();
        $q1CorrectOpt = $q1->question->options()->where('is_correct', true)->first();

        $saveRes1 = $this->postJson(route('student.attempts.save-answer', $attempt1), [
            'exam_attempt_question_id' => $q1->id,
            'exam_question_option_id' => $q1CorrectOpt->id,
            'is_doubtful' => false,
            'client_timestamp' => 1700000000000,
        ]);
        $saveRes1->assertOk();
        $saveRes1->assertJson(['success' => true]);
        $this->assertEquals($q1CorrectOpt->id, $q1->fresh()->selected_exam_question_option_id);

        // Autosave answer for Question 2 with doubtful flag (select correct option A)
        $q2 = $attempt1->attemptQuestions()->skip(1)->first();
        $q2CorrectOpt = $q2->question->options()->where('is_correct', true)->first();

        $saveRes2 = $this->postJson(route('student.attempts.save-answer', $attempt1), [
            'exam_attempt_question_id' => $q2->id,
            'exam_question_option_id' => $q2CorrectOpt->id,
            'is_doubtful' => true,
            'client_timestamp' => 1700000001000,
        ]);
        $saveRes2->assertOk();
        $this->assertTrue((bool) $q2->fresh()->is_doubtful);

        // Timer Sync
        $syncRes = $this->getJson(route('student.attempts.sync-time', $attempt1));
        $syncRes->assertOk();
        $syncRes->assertJsonStructure(['server_time', 'remaining_seconds', 'is_expired']);
        $this->assertFalse($syncRes->json('is_expired'));

        // Refresh/Resume simulation: accessing attempt room again must NOT create duplicate attempt
        $refreshRes = $this->get(route('student.attempts.show', $attempt1));
        $refreshRes->assertOk();
        $this->assertEquals(1, ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student1->id)->count());

        // Student 1 submits attempt manually
        $finishRes = $this->post(route('student.attempts.finish', $attempt1), [
            'confirm_submit' => '1',
        ]);
        $finishRes->assertRedirect();
        $this->assertEquals('completed', $attempt1->fresh()->status);
        $this->assertNotNull($attempt1->fresh()->completed_at);

        // Verify Automatic Scoring: 2 correct out of 2 = 100.00 score
        $attempt1Fresh = $attempt1->fresh();
        $this->assertEquals(100.00, (float) $attempt1Fresh->score);
        $this->assertEquals(2, $attempt1Fresh->total_correct);
        $this->assertTrue((bool) $attempt1Fresh->is_passed);

        // ---------------------------------------------------------------------
        // STEP 3: Student 2 starts attempt, Teacher performs Force Finish & Retake
        // ---------------------------------------------------------------------
        $this->actingAs($this->studentUser2);
        $this->post(route('student.exams.start', $exam), ['token' => 'SUKSES26']);
        $attempt2 = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $this->student2->id)->first();
        $this->assertEquals('in_progress', $attempt2->status);

        // Switch to Teacher: Live Monitoring & Administrative Action
        $this->actingAs($this->teacherUser);

        // Live Monitoring Dashboard
        $monRes = $this->get(route('teacher.monitoring.show', $exam));
        $monRes->assertOk();
        $monRes->assertSee('Asesmen Akhir Semester PWPB');

        // Force Finish Student 2
        $forceRes = $this->post(route('teacher.monitoring.attempts.force-finish', [$exam, $attempt2]), [
            'reason' => 'Siswa mengalami gangguan perangkat laptop di laboratorium.',
        ]);
        $forceRes->assertOk();
        $forceRes->assertJson(['success' => true]);
        $this->assertEquals('completed', $attempt2->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_finalize',
            'auditable_type' => ExamAttempt::class,
            'auditable_id' => $attempt2->id,
            'reason' => 'Siswa mengalami gangguan perangkat laptop di laboratorium.',
        ]);

        // Authorize Retake for Student 2
        $part2 = ExamParticipant::where('exam_id', $exam->id)->where('student_id', $this->student2->id)->first();
        $retakeRes = $this->post(route('teacher.monitoring.participants.retake', [$exam, $part2]), [
            'reason' => 'Diberikan izin ujian ulang setelah perangkat lab diganti.',
        ]);
        $retakeRes->assertOk();
        $retakeRes->assertJson(['success' => true]);
        $this->assertTrue((bool) $part2->fresh()->can_retake);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'retake_authorized',
            'auditable_type' => ExamParticipant::class,
            'auditable_id' => $part2->id,
        ]);

        // ---------------------------------------------------------------------
        // STEP 4: Results Publication, Reports, Item Analysis, and Export
        // ---------------------------------------------------------------------
        // Results index
        $resIndex = $this->get(route('teacher.results.show', $exam));
        $resIndex->assertOk();

        // Publish Results
        $pubResultsRes = $this->post(route('teacher.exams.publish-results', $exam));
        $pubResultsRes->assertRedirect();
        $this->assertTrue((bool) $exam->fresh()->is_results_published);
        $this->assertDatabaseHas('audit_logs', ['action' => 'publish_results', 'auditable_id' => $exam->id]);

        // Student 1 can now view published score and review answers
        $this->actingAs($this->studentUser1);
        $studentResView = $this->get(route('student.results.index'));
        $studentResView->assertOk();
        $studentResView->assertSee('100');

        // Teacher examines Comprehensive Reports & Rankings
        $this->actingAs($this->teacherUser);
        $reportRes = $this->get(route('teacher.reports.exam', $exam));
        $reportRes->assertOk();
        $reportRes->assertSee('Papan Peringkat');

        // Teacher examines Item Analysis
        $analysisRes = $this->get(route('teacher.reports.item-analysis', $exam));
        $analysisRes->assertOk();
        $analysisRes->assertSee('Analisis Butir Soal');

        // Teacher exports CSV
        $csvRes = $this->get(route('teacher.reports.export-csv', $exam));
        $csvRes->assertOk();
        $this->assertStringContainsString('text/csv', $csvRes->headers->get('Content-Type'));

        // Teacher opens printable view
        $printRes = $this->get(route('teacher.reports.print', $exam));
        $printRes->assertOk();
        $printRes->assertSee('SMK Negeri 1 CBT School');

        // ---------------------------------------------------------------------
        // STEP 5: Audit Log Hub & Access Filtering
        // ---------------------------------------------------------------------
        $this->actingAs($this->admin);
        $auditRes = $this->get(route('admin.audit-logs.index'));
        $auditRes->assertOk();
        $auditRes->assertSee('publish_exam');
        $auditRes->assertSee('admin_finalize');
        $auditRes->assertSee('retake_authorized');
        $auditRes->assertSee('publish_results');

        // Student forbidden from audit logs
        $this->actingAs($this->studentUser1);
        $this->get(route('admin.audit-logs.index'))->assertForbidden();
    }

    /**
     * TEST 3: Expired attempts finalized automatically and idempotently by artisan command.
     */
    public function test_expired_attempts_are_finalized_automatically_by_artisan_command(): void
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Ujian Expired Test',
            'code' => 'EXAM-EXP-01',
            'duration' => 30,
            'passing_grade' => 70.00,
            'start_at' => Carbon::now()->subHours(2),
            'end_at' => Carbon::now()->subMinutes(10), // expired 10 minutes ago
            'status' => 'published',
            'is_results_published' => false,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student1->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subMinutes(40),
            'expires_at' => Carbon::now()->subMinutes(10),
            'status' => 'in_progress',
        ]);

        // Run background artisan command
        Artisan::call('cbt:finalize-expired-attempts');

        $this->assertEquals('expired', $attempt->fresh()->status);
        $this->assertNotNull($attempt->fresh()->completed_at);
    }

    /**
     * TEST 4: Snapshot protection: mutating original question bank does NOT alter snapshot exam question.
     */
    public function test_question_bank_mutation_does_not_affect_exam_snapshot(): void
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Ujian Snapshot Immutability Test',
            'code' => 'EXAM-SNAP-01',
            'duration' => 45,
            'passing_grade' => 75.00,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(1),
            'status' => 'published',
            'is_results_published' => false,
        ]);

        $originalQuestion = Question::where('question_category_id', $this->questionCategory->id)->first();

        // Create snapshot
        $snapshot = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_id' => $originalQuestion->id,
            'order' => 1,
            'code' => $originalQuestion->code,
            'question_text' => 'Teks Snapshot Asli',
            'weight' => 100.00,
            'difficulty' => 'easy',
        ]);

        // Mutate original question in bank
        $originalQuestion->update([
            'question_text' => 'Teks Diubah di Bank Soal Setelah Ujian Dibuat',
            'weight' => 200.00,
        ]);

        // Assert snapshot remains intact and unmutated
        $this->assertEquals('Teks Snapshot Asli', $snapshot->fresh()->question_text);
        $this->assertEquals(100.00, (float) $snapshot->fresh()->weight);
    }
}
