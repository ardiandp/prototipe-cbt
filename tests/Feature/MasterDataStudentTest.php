<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Student;
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

class MasterDataStudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view students list with data.
     */
    public function test_admin_can_view_students_list(): void
    {
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

        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.students.index'));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Dani');
        $response->assertSee('20261001');
        $response->assertSee('Daftar Siswa Peserta CBT');
    }

    /**
     * Test admin can view create student page.
     */
    public function test_admin_can_view_create_student_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Siswa Baru');
        $response->assertSee('Biodata Peserta Didik');
    }

    /**
     * Test admin can create a new student with user account and classroom.
     */
    public function test_admin_can_create_student_with_user_and_classroom(): void
    {
        $admin = User::factory()->admin()->create();
        $classroom = Classroom::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => 'Muhammad Rifqi',
            'nis' => '20269001',
            'nisn' => '0089999001',
            'classroom_id' => $classroom->id,
            'gender' => 'L',
            'birth_date' => '2008-05-15',
            'phone' => '081234567899',
            'address' => 'Jl. Sukajadi No. 12',
            'username' => 'siswa.20269001',
            'email' => 'rifqi@cbtschool.local',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => 'siswa.20269001',
            'email' => 'rifqi@cbtschool.local',
            'role' => UserRole::STUDENT->value,
        ]);

        $user = User::where('username', 'siswa.20269001')->first();

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'classroom_id' => $classroom->id,
            'nis' => '20269001',
            'nisn' => '0089999001',
            'name' => 'Muhammad Rifqi',
        ]);
    }

    /**
     * Test student validation rules prevent duplicate NIS, NISN, username, and email.
     */
    public function test_student_creation_validation_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $existingStudent = Student::factory()->create([
            'nis' => '20260001',
            'nisn' => '0080000001',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => '',
            'nis' => '20260001', // duplicate NIS
            'nisn' => '0080000001', // duplicate NISN
            'classroom_id' => 999999, // non-existent
            'username' => $existingStudent->user->username, // duplicate username
            'email' => $existingStudent->user->email, // duplicate email
            'password' => '123', // min 6
        ]);

        $response->assertSessionHasErrors(['name', 'nis', 'nisn', 'classroom_id', 'username', 'email', 'password']);
    }

    /**
     * Test admin can view edit student page.
     */
    public function test_admin_can_view_edit_student_page(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.edit', $student));

        $response->assertStatus(200);
        $response->assertSee('Edit Data Siswa');
        $response->assertSee($student->name);
    }

    /**
     * Test admin can update student profile and user credentials.
     */
    public function test_admin_can_update_student_profile_and_user(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create([
            'name' => 'Original Student Name',
            'nis' => '20260055',
            'nisn' => '0080000055',
        ]);

        $newClassroom = Classroom::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.students.update', $student), [
            'name' => 'Updated Student Name',
            'nis' => '20260055', // same NIS allowed
            'nisn' => '0080000055', // same NISN allowed
            'classroom_id' => $newClassroom->id,
            'gender' => 'P',
            'birth_date' => '2008-09-20',
            'phone' => '089988776655',
            'address' => 'Jl. Baru No. 20',
            'username' => $student->user->username, // same username allowed
            'email' => 'updated.student@cbtschool.local',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'Updated Student Name',
            'classroom_id' => $newClassroom->id,
            'gender' => 'P',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'email' => 'updated.student@cbtschool.local',
        ]);
    }

    /**
     * Test admin can delete student and its user account.
     */
    public function test_admin_can_delete_student_and_user_account(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $userId = $student->user_id;

        $response = $this->actingAs($admin)->delete(route('admin.students.destroy', $student));

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /**
     * Test teacher and student cannot access student management.
     */
    public function test_unauthorized_roles_cannot_access_students(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.students.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.students.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.students.index'));
        $response->assertRedirect(route('login'));
    }
}

