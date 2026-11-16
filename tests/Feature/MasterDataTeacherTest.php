<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ClassroomSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view teachers list.
     */
    public function test_admin_can_view_teachers_list(): void
    {
        $this->seed([
            UserSeeder::class,
            SchoolProfileSeeder::class,
            AcademicPeriodSeeder::class,
            DepartmentSeeder::class,
            ClassroomSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
        ]);

        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.teachers.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('guru');
        $response->assertSee('PWPB');
        $response->assertSee('Daftar Tenaga Pendidik');
    }

    /**
     * Test admin can view create teacher page.
     */
    public function test_admin_can_view_create_teacher_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.teachers.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Guru Baru');
        $response->assertSee('Mata Pelajaran yang Diampu');
    }

    /**
     * Test admin can create a new teacher with user account and assignments.
     */
    public function test_admin_can_create_teacher_with_user_and_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $subject = Subject::factory()->create();
        $classroom = Classroom::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Dr. Hendra Wijaya, M.Kom.',
            'nip' => '197901012002121001',
            'gender' => 'L',
            'phone' => '081234567899',
            'address' => 'Jl. Merdeka No. 10',
            'username' => 'hendra.wijaya',
            'email' => 'hendra@cbtschool.local',
            'password' => 'secret123',
            'is_active' => true,
            'subject_ids' => [$subject->id],
            'classroom_ids' => [$classroom->id],
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => 'hendra.wijaya',
            'email' => 'hendra@cbtschool.local',
            'role' => UserRole::TEACHER->value,
        ]);

        $user = User::where('username', 'hendra.wijaya')->first();

        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
            'name' => 'Dr. Hendra Wijaya, M.Kom.',
            'nip' => '197901012002121001',
        ]);

        $teacher = Teacher::where('user_id', $user->id)->first();
        $this->assertTrue($teacher->subjects->contains($subject));
        $this->assertTrue($teacher->classrooms->contains($classroom));
    }

    /**
     * Test validation requires unique username, email, and nip.
     */
    public function test_teacher_creation_validation_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $existingTeacher = Teacher::factory()->create(['nip' => '198501012010011001']);

        $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => '',
            'nip' => '198501012010011001', // duplicate NIP
            'username' => $existingTeacher->user->username, // duplicate username
            'email' => $existingTeacher->user->email, // duplicate email
            'password' => '123', // min 6
        ]);

        $response->assertSessionHasErrors(['name', 'nip', 'username', 'email', 'password']);
    }

    /**
     * Test admin can view edit teacher page.
     */
    public function test_admin_can_view_edit_teacher_page(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.teachers.edit', $teacher));

        $response->assertStatus(200);
        $response->assertSee('Edit Data Guru');
        $response->assertSee($teacher->name);
    }

    /**
     * Test admin can update teacher profile and sync assignments.
     */
    public function test_admin_can_update_teacher_and_sync_assignments(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create([
            'name' => 'Original Name',
            'nip' => '198001012005011001',
        ]);

        $sub1 = Subject::factory()->create();
        $sub2 = Subject::factory()->create();
        $teacher->subjects()->attach($sub1);

        $cls1 = Classroom::factory()->create();
        $teacher->classrooms()->attach($cls1);

        $response = $this->actingAs($admin)->put(route('admin.teachers.update', $teacher), [
            'name' => 'Updated Teacher Name, M.Pd.',
            'nip' => '198001012005011001', // same NIP allowed
            'gender' => 'P',
            'phone' => '089999888777',
            'address' => 'Updated Address',
            'username' => $teacher->user->username, // same username allowed
            'email' => 'updated.email@cbtschool.local',
            'is_active' => true,
            'subject_ids' => [$sub2->id], // switched to sub2
            'classroom_ids' => [$cls1->id],
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => 'Updated Teacher Name, M.Pd.',
            'gender' => 'P',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $teacher->user_id,
            'email' => 'updated.email@cbtschool.local',
        ]);

        $this->assertFalse($teacher->fresh()->subjects->contains($sub1));
        $this->assertTrue($teacher->fresh()->subjects->contains($sub2));
    }

    /**
     * Test admin can delete teacher and its user account.
     */
    public function test_admin_can_delete_teacher_and_user_account(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create();
        $userId = $teacher->user_id;

        $response = $this->actingAs($admin)->delete(route('admin.teachers.destroy', $teacher));

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /**
     * Test teacher and student cannot access teacher management.
     */
    public function test_unauthorized_roles_cannot_access_teachers(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.teachers.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.teachers.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.teachers.index'));
        $response->assertRedirect(route('login'));
    }
}

