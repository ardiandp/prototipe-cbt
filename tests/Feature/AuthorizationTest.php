<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student cannot access admin routes and receives 403.
     */
    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get('/admin/school-profile');
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get('/admin/academic-years');
        $response->assertStatus(403);
    }

    /**
     * Test student cannot access teacher routes and receives 403.
     */
    public function test_student_cannot_access_teacher_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/teacher/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get('/teacher/question-banks');
        $response->assertStatus(403);
    }

    /**
     * Test teacher cannot access admin master data routes and receives 403.
     */
    public function test_teacher_cannot_access_admin_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($teacher)->get('/admin/school-profile');
        $response->assertStatus(403);
    }

    /**
     * Test admin and superadmin can access admin routes.
     */
    public function test_admin_and_superadmin_can_access_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $superadmin = User::factory()->superadmin()->create();
        $response = $this->actingAs($superadmin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test teacher can access teacher routes.
     */
    public function test_teacher_can_access_teacher_routes(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get('/teacher/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($teacher)->get('/teacher/question-banks');
        $response->assertStatus(200);
    }

    /**
     * Test student can access student routes.
     */
    public function test_student_can_access_student_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get('/student/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($student)->get('/student/exams');
        $response->assertStatus(200);
    }

    /**
     * Test that sidebar navigation renders conditionally according to user role.
     */
    public function test_sidebar_renders_correct_menu_by_role(): void
    {
        $student = User::factory()->student()->create();
        $response = $this->actingAs($student)->get('/student/dashboard');
        $response->assertSee('Ujian Tersedia');
        $response->assertDontSee('Data Master');
        $response->assertDontSee('Profil Sekolah');

        $teacher = User::factory()->teacher()->create();
        $response = $this->actingAs($teacher)->get('/teacher/dashboard');
        $response->assertSee('Menu Guru');
        $response->assertSee('Bank Soal');
        $response->assertDontSee('Data Master');

        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertSee('Data Master');
        $response->assertSee('Profil Sekolah');
        $response->assertDontSee('Ujian Tersedia');
    }
}

