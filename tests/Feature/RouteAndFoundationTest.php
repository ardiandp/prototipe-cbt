<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteAndFoundationTest extends TestCase
{
    /**
     * Test that guest can access the login page.
     */
    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('CBT School');
        $response->assertSee('Silakan Masuk');
        $response->assertSee('username');
        $response->assertSee('password');
        $response->assertSee('theme/adminLTE/dist/css/adminlte.min.css', false);
    }

    /**
     * Test that root redirects to login when not authenticated.
     */
    public function test_root_redirects_to_login_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test that dashboard requires authentication and redirects to login.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test theme demo is publicly accessible.
     */
    public function test_theme_demo_is_accessible(): void
    {
        $response = $this->get('/theme-demo');

        $response->assertStatus(200);
        $response->assertSee('AdminLTE 3 Integration Demo');
    }

    /**
     * Test custom AdminLTE 404 error page.
     */
    public function test_custom_404_error_page(): void
    {
        $response = $this->get('/non-existent-url-testing-404');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('theme/adminLTE/dist/css/adminlte.min.css', false);
    }

    /**
     * Test custom AdminLTE 403 error page.
     */
    public function test_custom_403_error_page(): void
    {
        $response = $this->get('/error/403');

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Akses Tidak Diizinkan');
    }

    /**
     * Test custom AdminLTE 500 error page.
     */
    public function test_custom_500_error_page(): void
    {
        $response = $this->get('/error/500');

        $response->assertStatus(500);
        $response->assertSee('500');
        $response->assertSee('Terjadi Gangguan Server');
    }
}
