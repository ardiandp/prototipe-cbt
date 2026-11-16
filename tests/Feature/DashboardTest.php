<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ClassroomSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            SchoolProfileSeeder::class,
            AcademicPeriodSeeder::class,
            DepartmentSeeder::class,
            ClassroomSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
        ]);
    }

    /**
     * Test admin dashboard displays real data from database.
     */
    public function test_admin_dashboard_displays_real_master_data_statistics(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('SMA Negeri 1 CBT Indonesia'); // school profile
        $response->assertSee('2026/2027'); // active academic year
        $response->assertSee('Total Siswa Aktif');
        $response->assertSee('Total Tenaga Pendidik');
        $response->assertSee('Rombongan Belajar');
        $response->assertSee('Mata Pelajaran');
        $response->assertSee('Budi Santoso'); // recent teacher
        $response->assertSee('Ahmad Dani'); // recent student
    }

    /**
     * Test teacher dashboard displays assigned subjects and classrooms.
     */
    public function test_teacher_dashboard_displays_assigned_subjects_and_classes(): void
    {
        $guru = User::where('username', 'guru')->first();

        $response = $this->actingAs($guru)->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('198005122005011003'); // NIP
        $response->assertSee('Mata Pelajaran Diampu');
        $response->assertSee('PWPB');
        $response->assertSee('PBO');
        $response->assertSee('Rombongan Belajar (Kelas) yang Diajar');
    }

    /**
     * Test student dashboard displays student profile and classroom.
     */
    public function test_student_dashboard_displays_student_profile_and_classroom(): void
    {
        $siswa = User::where('username', 'siswa')->first();

        $response = $this->actingAs($siswa)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Dani');
        $response->assertSee('20261001'); // NIS
        $response->assertSee('X RPL 1'); // classroom
        $response->assertSee('Petunjuk & Tata Tertib Ujian CBT');
    }

    /**
     * Test /dashboard endpoint dispatches users to their respective role dashboards.
     */
    public function test_dashboard_dispatch_by_role(): void
    {
        $admin = User::where('username', 'admin')->first();
        $guru = User::where('username', 'guru')->first();
        $siswa = User::where('username', 'siswa')->first();

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($guru)->get(route('dashboard'));
        $response->assertRedirect(route('teacher.dashboard'));

        $response = $this->actingAs($siswa)->get(route('dashboard'));
        $response->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test role protection for dashboards.
     */
    public function test_role_protection_for_dashboards(): void
    {
        $guru = User::where('username', 'guru')->first();
        $siswa = User::where('username', 'siswa')->first();

        // Teacher cannot access admin dashboard
        $response = $this->actingAs($guru)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Student cannot access admin or teacher dashboard
        $response = $this->actingAs($siswa)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response = $this->actingAs($siswa)->get(route('teacher.dashboard'));
        $response->assertStatus(403);
    }
}

