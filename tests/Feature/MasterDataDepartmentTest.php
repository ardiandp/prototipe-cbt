<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataDepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view departments list.
     */
    public function test_admin_can_view_departments_list(): void
    {
        $this->seed(DepartmentSeeder::class);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.departments.index'));

        $response->assertStatus(200);
        $response->assertSee('Rekayasa Perangkat Lunak');
        $response->assertSee('RPL');
        $response->assertSee('TKJ');
    }

    /**
     * Test admin can create a new department.
     */
    public function test_admin_can_create_department(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'code' => 'SIJA',
            'name' => 'Sistem Informatika, Jaringan, dan Aplikasi',
            'description' => 'Program keahlian 4 tahun untuk cloud dan jaringan.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'code' => 'SIJA',
            'name' => 'Sistem Informatika, Jaringan, dan Aplikasi',
            'is_active' => true,
        ]);
    }

    /**
     * Test department creation validation rules.
     */
    public function test_department_validation_rules(): void
    {
        $admin = User::factory()->admin()->create();
        Department::factory()->create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak']);

        // Duplicate code and empty name
        $response = $this->actingAs($admin)->post(route('admin.departments.store'), [
            'code' => 'RPL',
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['code', 'name']);
    }

    /**
     * Test admin can update an existing department.
     */
    public function test_admin_can_update_department(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.departments.update', $department), [
            'code' => 'RPL-NEW',
            'name' => 'Pengembangan Perangkat Lunak & Gim',
            'description' => 'Kurikulum Merdeka',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'code' => 'RPL-NEW',
            'name' => 'Pengembangan Perangkat Lunak & Gim',
        ]);
    }

    /**
     * Test department with associated classrooms cannot be deleted.
     */
    public function test_department_with_classrooms_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();
        Classroom::factory()->create(['department_id' => $department->id]);

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $department));

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    /**
     * Test department without classrooms can be deleted.
     */
    public function test_department_without_classrooms_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.departments.destroy', $department));

        $response->assertRedirect(route('admin.departments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    /**
     * Test teacher and student cannot access department management.
     */
    public function test_unauthorized_roles_cannot_access_departments(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.departments.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.departments.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.departments.index'));
        $response->assertRedirect(route('login'));
    }
}

