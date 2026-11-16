<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\ClassroomSeeder;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataClassroomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view classrooms list.
     */
    public function test_admin_can_view_classrooms_list(): void
    {
        $this->seed(DepartmentSeeder::class);
        $this->seed(ClassroomSeeder::class);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.classes.index'));

        $response->assertStatus(200);
        $response->assertSee('X RPL 1');
        $response->assertSee('XI RPL 1');
        $response->assertSee('Daftar Kelas');
    }

    /**
     * Test admin can create a new classroom.
     */
    public function test_admin_can_create_classroom(): void
    {
        $admin = User::factory()->admin()->create();
        $department = Department::factory()->create();
        $academicYear = AcademicYear::factory()->active()->create();

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'X SIJA 1',
            'level' => '10',
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('classrooms', [
            'name' => 'X SIJA 1',
            'level' => '10',
            'department_id' => $department->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }

    /**
     * Test creating classroom defaults to active academic year when none is provided.
     */
    public function test_classroom_defaults_to_active_academic_year(): void
    {
        $admin = User::factory()->admin()->create();
        $activeYear = AcademicYear::factory()->active()->create();

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => 'X Umum 1',
            'level' => '10',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $this->assertDatabaseHas('classrooms', [
            'name' => 'X Umum 1',
            'academic_year_id' => $activeYear->id,
        ]);
    }

    /**
     * Test classroom creation validation rules.
     */
    public function test_classroom_validation_rules(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.classes.store'), [
            'name' => '',
            'level' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'level']);
    }

    /**
     * Test admin can update an existing classroom.
     */
    public function test_admin_can_update_classroom(): void
    {
        $admin = User::factory()->admin()->create();
        $classroom = Classroom::factory()->create([
            'name' => 'X RPL 1',
            'level' => '10',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.classes.update', $classroom), [
            'name' => 'XI RPL 1 (Promoted)',
            'level' => '11',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'name' => 'XI RPL 1 (Promoted)',
            'level' => '11',
        ]);
    }

    /**
     * Test admin can delete classroom.
     */
    public function test_admin_can_delete_classroom(): void
    {
        $admin = User::factory()->admin()->create();
        $classroom = Classroom::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.classes.destroy', $classroom));

        $response->assertRedirect(route('admin.classes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('classrooms', ['id' => $classroom->id]);
    }

    /**
     * Test teacher and student cannot access classroom management.
     */
    public function test_unauthorized_roles_cannot_access_classrooms(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.classes.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.classes.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.classes.index'));
        $response->assertRedirect(route('login'));
    }
}
