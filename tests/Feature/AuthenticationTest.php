<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login view can be rendered.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Silakan Masuk');
    }

    /**
     * Test users can authenticate using username and redirected to role dashboard.
     */
    public function test_admin_can_authenticate_using_username(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test teacher can authenticate using email and redirected to teacher dashboard.
     */
    public function test_teacher_can_authenticate_using_email(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->post('/login', [
            'username' => 'guru@cbtschool.local',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard'));
    }

    /**
     * Test student is redirected to student dashboard.
     */
    public function test_student_can_authenticate_and_redirect_to_student_dashboard(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->post('/login', [
            'username' => 'siswa',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test users can not authenticate with invalid password.
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'user_test',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'username' => 'user_test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    /**
     * Test inactive users cannot authenticate.
     */
    public function test_inactive_users_can_not_authenticate(): void
    {
        $inactiveUser = User::factory()->inactive()->create([
            'username' => 'inactive_user',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'username' => 'inactive_user',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'username' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator sekolah.',
        ]);
    }

    /**
     * Test authenticated users can logout.
     */
    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Test login rate limiting after multiple failed attempts.
     */
    public function test_login_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create([
            'username' => 'rate_limited_user',
            'password' => 'secret123',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'rate_limited_user',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'username' => 'rate_limited_user',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }
}
