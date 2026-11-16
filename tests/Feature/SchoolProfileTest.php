<?php

namespace Tests\Feature;

use App\Models\SchoolProfile;
use App\Models\User;
use Database\Seeders\SchoolProfileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view the school profile page.
     */
    public function test_admin_can_view_school_profile_page(): void
    {
        $this->seed(SchoolProfileSeeder::class);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.school.profile'));

        $response->assertStatus(200);
        $response->assertSee('SMA Negeri 1 CBT Indonesia');
        $response->assertSee('20109999');
        $response->assertSee('Drs. H. Mulyono, M.Pd.');
    }

    /**
     * Test admin can update the school profile with valid data.
     */
    public function test_admin_can_update_school_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $updateData = [
            'name' => 'SMK Negeri 5 Unggulan CBT',
            'npsn' => '60201234',
            'address' => 'Jl. Teknologi Cerdas No. 45, Bandung',
            'email' => 'kontak@smkn5cbt.sch.id',
            'phone' => '022-7778899',
            'principal_name' => 'Prof. Dr. Ir. Ridwan, M.T.',
        ];

        $response = $this->actingAs($admin)
            ->put(route('admin.school.profile.update'), $updateData);

        $response->assertRedirect(route('admin.school.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('school_profiles', [
            'name' => 'SMK Negeri 5 Unggulan CBT',
            'npsn' => '60201234',
            'principal_name' => 'Prof. Dr. Ir. Ridwan, M.T.',
        ]);
    }

    /**
     * Test update validation requires name.
     */
    public function test_school_profile_update_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->put(route('admin.school.profile.update'), [
                'name' => '',
                'email' => 'invalid-email-format',
            ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Test teacher cannot access or update school profile.
     */
    public function test_teacher_cannot_access_school_profile(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('admin.school.profile'));
        $response->assertStatus(403);

        $response = $this->actingAs($teacher)->put(route('admin.school.profile.update'), [
            'name' => 'Hacker High School',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test student cannot access or update school profile.
     */
    public function test_student_cannot_access_school_profile(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('admin.school.profile'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->put(route('admin.school.profile.update'), [
            'name' => 'Student High School',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.school.profile'));
        $response->assertRedirect(route('login'));
    }
}
