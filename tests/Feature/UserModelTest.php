<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a user with student role and verify password hashing.
     */
    public function test_user_can_be_created_with_hashed_password(): void
    {
        $user = User::create([
            'name' => 'Budi Peserta',
            'username' => 'budi123',
            'email' => 'budi@example.com',
            'role' => UserRole::STUDENT,
            'password' => 'secret123',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'budi123',
            'email' => 'budi@example.com',
            'role' => 'student',
        ]);

        // Password must be hashed, not stored in plain text
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    /**
     * Test all roles and role helper methods on User model.
     */
    public function test_user_role_methods_and_enums(): void
    {
        $superadmin = User::factory()->superadmin()->create();
        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertTrue($superadmin->isAnyAdmin());
        $this->assertEquals('Super Admin', $superadmin->roleLabel());
        $this->assertTrue($superadmin->hasRole('superadmin'));
        $this->assertTrue($superadmin->hasRole(UserRole::SUPERADMIN));

        $admin = User::factory()->admin()->create();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isAnyAdmin());
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertEquals('Admin Sekolah', $admin->roleLabel());
        $this->assertTrue($admin->hasRole(['admin', 'superadmin']));

        $teacher = User::factory()->teacher()->create();
        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAnyAdmin());
        $this->assertEquals('Guru', $teacher->roleLabel());

        $student = User::factory()->student()->create();
        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isTeacher());
        $this->assertEquals('Siswa', $student->roleLabel());
    }

    /**
     * Test UserSeeder populates default multi-role prototype accounts.
     */
    public function test_user_seeder_creates_default_roles(): void
    {
        $this->seed(UserSeeder::class);

        $superadmin = User::where('username', 'superadmin')->first();
        $this->assertNotNull($superadmin);
        $this->assertEquals(UserRole::SUPERADMIN, $superadmin->role);
        $this->assertTrue(Hash::check('password', $superadmin->password));

        $admin = User::where('username', 'admin')->first();
        $this->assertNotNull($admin);
        $this->assertEquals(UserRole::ADMIN, $admin->role);
        $this->assertTrue(Hash::check('password', $admin->password));

        $teacher = User::where('username', 'guru')->first();
        $this->assertNotNull($teacher);
        $this->assertEquals(UserRole::TEACHER, $teacher->role);
        $this->assertTrue(Hash::check('password', $teacher->password));

        $student = User::where('username', 'siswa')->first();
        $this->assertNotNull($student);
        $this->assertEquals(UserRole::STUDENT, $student->role);
        $this->assertTrue(Hash::check('password', $student->password));
    }
}
