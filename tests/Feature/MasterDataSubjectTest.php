<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataSubjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view subjects list.
     */
    public function test_admin_can_view_subjects_list(): void
    {
        $this->seed(SubjectSeeder::class);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.subjects.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika');
        $response->assertSee('MTK');
        $response->assertSee('Pemrograman Web dan Perangkat Bergerak');
    }

    /**
     * Test admin can create a new subject.
     */
    public function test_admin_can_create_subject(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.subjects.store'), [
            'code' => 'IOT',
            'name' => 'Internet of Things',
            'level' => '12',
            'group' => 'Muatan Peminatan Kejuruan',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'code' => 'IOT',
            'name' => 'Internet of Things',
            'level' => '12',
            'group' => 'Muatan Peminatan Kejuruan',
            'is_active' => true,
        ]);
    }

    /**
     * Test subject validation requires unique code and name.
     */
    public function test_subject_validation_rules(): void
    {
        $admin = User::factory()->admin()->create();
        Subject::factory()->create(['code' => 'MTK', 'name' => 'Matematika']);

        $response = $this->actingAs($admin)->post(route('admin.subjects.store'), [
            'code' => 'MTK',
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['code', 'name']);
    }

    /**
     * Test admin can update an existing subject.
     */
    public function test_admin_can_update_subject(): void
    {
        $admin = User::factory()->admin()->create();
        $subject = Subject::factory()->create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.subjects.update', $subject), [
            'code' => 'MTK-WAJIB',
            'name' => 'Matematika Wajib',
            'level' => '10',
            'group' => 'Muatan Umum',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'code' => 'MTK-WAJIB',
            'name' => 'Matematika Wajib',
        ]);
    }

    /**
     * Test admin can delete a subject.
     */
    public function test_admin_can_delete_subject(): void
    {
        $admin = User::factory()->admin()->create();
        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.subjects.destroy', $subject));

        $response->assertRedirect(route('admin.subjects.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    /**
     * Test teacher and student cannot access subject management.
     */
    public function test_unauthorized_roles_cannot_access_subjects(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.subjects.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.subjects.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.subjects.index'));
        $response->assertRedirect(route('login'));
    }
}
