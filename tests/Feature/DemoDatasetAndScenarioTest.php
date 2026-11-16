<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamParticipant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ExamDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDatasetAndScenarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_includes_exam_demo_seeder_and_creates_consistent_scenarios(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Verify default demo users
        $this->assertDatabaseHas('users', ['username' => 'superadmin', 'role' => 'superadmin']);
        $this->assertDatabaseHas('users', ['username' => 'admin', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['username' => 'guru', 'role' => 'teacher']);
        $this->assertDatabaseHas('users', ['username' => 'siswa', 'role' => 'student']);

        // 2. Verify Exam 1: Ongoing & Ready for Student
        $exam1 = Exam::where('code', 'EXAM-PWPB-01')->first();
        $this->assertNotNull($exam1);
        $this->assertEquals('published', $exam1->status);
        $this->assertEquals('CBT2026', $exam1->token);
        $this->assertGreaterThan(0, $exam1->questions()->count());
        $this->assertEquals($exam1->total_questions, $exam1->questions()->count());
        $this->assertGreaterThan(0, $exam1->participants()->count());

        // Student 'siswa' is enrolled with no attempt yet
        $studentUser = User::where('username', 'siswa')->first();
        $this->assertDatabaseHas('exam_participants', [
            'exam_id' => $exam1->id,
            'student_id' => $studentUser->studentProfile->id,
            'status' => 'enrolled',
        ]);
        $this->assertDatabaseMissing('exam_attempts', [
            'exam_id' => $exam1->id,
            'student_id' => $studentUser->studentProfile->id,
        ]);

        // Annisa is in progress, Bagas is completed
        $this->assertEquals(1, ExamAttempt::where('exam_id', $exam1->id)->where('status', 'in_progress')->count());
        $this->assertEquals(1, ExamAttempt::where('exam_id', $exam1->id)->where('status', 'completed')->count());

        // 3. Verify Exam 2: Completed & Results Published
        $exam2 = Exam::where('code', 'EXAM-BD-01')->first();
        $this->assertNotNull($exam2);
        $this->assertTrue((bool) $exam2->is_results_published);
        $this->assertGreaterThan(0, $exam2->questions()->count());
        $this->assertGreaterThan(0, ExamAttempt::where('exam_id', $exam2->id)->where('status', 'completed')->count());

        // All completed attempts in Exam 2 must have scores calculated
        $completedAttempts = ExamAttempt::where('exam_id', $exam2->id)->where('status', 'completed')->get();
        foreach ($completedAttempts as $attempt) {
            $this->assertNotNull($attempt->score);
            $this->assertNotNull($attempt->total_correct);
        }

        // 4. Verify Exam 3: Draft
        $exam3 = Exam::where('code', 'EXAM-PWPB-REMIDIAL')->first();
        $this->assertNotNull($exam3);
        $this->assertEquals('draft', $exam3->status);

        // 5. Verify Demo Audit Logs
        $this->assertGreaterThanOrEqual(3, AuditLog::count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'login']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'publish_exam']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'publish_results']);
    }

    public function test_student_can_start_pwpb_demo_exam_with_provided_token(): void
    {
        $this->seed(DatabaseSeeder::class);

        $studentUser = User::where('username', 'siswa')->first();
        $exam = Exam::where('code', 'EXAM-PWPB-01')->first();

        $this->actingAs($studentUser);

        // Access exam show page
        $showRes = $this->get(route('student.exams.show', $exam));
        $showRes->assertOk();
        $showRes->assertSee('Asesmen Sumatif Tengah Semester - Pemrograman Web');

        // Start exam with token
        $startRes = $this->post(route('student.exams.start', $exam), [
            'token' => 'CBT2026',
        ]);

        $startRes->assertRedirect();

        // An attempt is created
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentUser->studentProfile->id)
            ->first();

        $this->assertNotNull($attempt);
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertEquals($exam->total_questions, $attempt->attemptQuestions()->count());

        // Access exam room
        $roomRes = $this->get(route('student.attempts.show', $attempt));
        $roomRes->assertOk();
        $roomRes->assertSee('Ruang Ujian');
        $roomRes->assertSee('SISA WAKTU');
    }

    public function test_cbt_reset_demo_artisan_command_runs_cleanly(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        $this->artisan('cbt:reset-demo', ['--force' => true])
            ->assertSuccessful();

        // Check essential database records exist after command
        $this->assertDatabaseHas('users', ['username' => 'siswa']);
        $this->assertDatabaseHas('exams', ['code' => 'EXAM-PWPB-01']);
        $this->assertDatabaseHas('exams', ['code' => 'EXAM-BD-01']);

        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$inMemoryConnections = [];
    }
}
