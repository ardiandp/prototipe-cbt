<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $studentUser;
    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

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
        $classA = Classroom::create(['department_id' => $dept->id, 'academic_year_id' => $year->id, 'name' => 'XII RPL 1', 'level' => '12', 'is_active' => true]);

        // Users
        $this->adminUser = User::create([
            'username' => 'admin_test',
            'name' => 'Admin CBT',
            'email' => 'admin@cbt.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->teacherUser = User::create([
            'username' => 'guru_budi',
            'name' => 'Budi Santoso, S.Kom',
            'email' => 'budi@cbt.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'name' => 'Budi Santoso, S.Kom',
            'nip' => '198501012010011001',
            'is_active' => true,
        ]);

        $this->otherTeacherUser = User::create([
            'username' => 'guru_siti',
            'name' => 'Siti Rahma, M.Pd',
            'email' => 'siti@cbt.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);
        $this->otherTeacher = Teacher::create([
            'user_id' => $this->otherTeacherUser->id,
            'name' => 'Siti Rahma, M.Pd',
            'nip' => '198802022012012002',
            'is_active' => true,
        ]);

        $this->studentUser = User::create([
            'username' => 'siswa_ahmad',
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@cbt.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
        ]);
        $student = Student::create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $classA->id,
            'name' => 'Ahmad Fauzi',
            'nis' => '2026001',
            'nisn' => '0061234567',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $subject = Subject::create(['code' => 'PROG', 'name' => 'Pemrograman Web', 'is_active' => true]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Asesmen Harian Pemrograman',
            'code' => 'EXAM-LOG-01',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(2),
            'passing_grade' => 75,
            'status' => 'draft',
            'total_questions' => 1,
            'is_results_published' => false,
        ]);

        \App\Models\ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $student->id,
            'status' => 'enrolled',
        ]);

        $q = ExamQuestion::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'Apa tag HTML untuk tautan?',
            'question_type' => 'multiple_choice',
            'weight' => 100,
            'order_index' => 1,
        ]);
        ExamQuestionOption::create(['exam_question_id' => $q->id, 'label' => 'A', 'option_text' => 'a', 'is_correct' => true, 'order_index' => 1]);
        ExamQuestionOption::create(['exam_question_id' => $q->id, 'label' => 'B', 'option_text' => 'link', 'is_correct' => false, 'order_index' => 2]);
    }

    public function test_user_login_records_audit_log(): void
    {
        $response = $this->post(route('login'), [
            'username' => 'guru_budi',
            'password' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'login',
        ]);
    }

    public function test_user_logout_records_audit_log(): void
    {
        $this->actingAs($this->teacherUser);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'logout',
        ]);
    }

    public function test_exam_publish_unpublish_and_cancel_records_audit_log(): void
    {
        $this->actingAs($this->teacherUser);

        // 1. Publish
        $response = $this->post(route('teacher.exams.publish', $this->exam));
        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'publish_exam',
            'auditable_type' => Exam::class,
            'auditable_id' => $this->exam->id,
        ]);

        // 2. Unpublish
        $response = $this->post(route('teacher.exams.unpublish', $this->exam));
        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'unpublish_exam',
            'auditable_type' => Exam::class,
            'auditable_id' => $this->exam->id,
        ]);

        // 3. Cancel
        $response = $this->post(route('teacher.exams.cancel', $this->exam), [
            'reason' => 'Ujian dibatalkan karena pemadaman listrik di sekolah.',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'cancel_exam',
            'auditable_type' => Exam::class,
            'auditable_id' => $this->exam->id,
            'reason' => 'Ujian dibatalkan karena pemadaman listrik di sekolah.',
        ]);
    }

    public function test_admin_can_view_all_audit_logs(): void
    {
        // Create logs from different users
        AuditLog::record('login', 'Login admin', null, [], $this->adminUser);
        AuditLog::record('login', 'Login guru budi', null, [], $this->teacherUser);
        AuditLog::record('login', 'Login guru siti', null, [], $this->otherTeacherUser);

        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Audit Log &amp; Riwayat Aktivitas', false);
        $response->assertSee('Login admin');
        $response->assertSee('Login guru budi');
        $response->assertSee('Login guru siti');
    }

    public function test_teacher_can_only_view_their_own_or_their_exam_audit_logs(): void
    {
        // Teacher Budi's log
        AuditLog::record('login', 'Aktivitas guru budi', null, [], $this->teacherUser);
        AuditLog::record('publish_exam', 'Publikasi ujian budi', $this->exam, [], $this->teacherUser);

        // Teacher Siti's log (unrelated)
        AuditLog::record('login', 'Aktivitas guru siti', null, [], $this->otherTeacherUser);

        $this->actingAs($this->teacherUser);

        $response = $this->get(route('teacher.audit-logs.index'));

        $response->assertOk();
        $response->assertSee('Aktivitas guru budi');
        $response->assertSee('Publikasi ujian budi');
        $response->assertDontSee('Aktivitas guru siti');
    }

    public function test_student_is_forbidden_from_viewing_audit_logs(): void
    {
        $this->actingAs($this->studentUser);

        $responseAdmin = $this->get(route('admin.audit-logs.index'));
        $responseAdmin->assertForbidden();

        $responseTeacher = $this->get(route('teacher.audit-logs.index'));
        $responseTeacher->assertForbidden();
    }

    public function test_filtering_audit_logs_by_action_and_search_keyword(): void
    {
        AuditLog::record('publish_exam', 'Publikasi jadwal semester ganjil', $this->exam, [], $this->adminUser);
        AuditLog::record('force_finish', 'Siswa mengalami kendala jaringan LAN', null, [], $this->adminUser);
        AuditLog::record('login', 'Login rutin pagi', null, [], $this->adminUser);

        $this->actingAs($this->adminUser);

        // Filter by action
        $resAction = $this->get(route('admin.audit-logs.index', ['action' => 'publish_exam']));
        $resAction->assertOk();
        $resAction->assertSee('Publikasi jadwal semester ganjil');
        $resAction->assertDontSee('Siswa mengalami kendala jaringan LAN');

        // Filter by search keyword
        $resSearch = $this->get(route('admin.audit-logs.index', ['search' => 'kendala jaringan']));
        $resSearch->assertOk();
        $resSearch->assertSee('Siswa mengalami kendala jaringan LAN');
        $resSearch->assertDontSee('Publikasi jadwal semester ganjil');
    }
}
