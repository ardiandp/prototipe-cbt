<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLteLayoutTest extends TestCase
{
    /**
     * Test that root redirects to login for guests.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test that theme demo renders and includes local AdminLTE assets.
     */
    public function test_theme_demo_renders_with_local_adminlte_assets(): void
    {
        $response = $this->get(route('theme.demo'));

        $response->assertStatus(200);

        // Core local AdminLTE assets loaded without CDN
        $response->assertSee('theme/adminLTE/dist/css/adminlte.min.css', false);
        $response->assertSee('theme/adminLTE/dist/js/adminlte.min.js', false);
        $response->assertSee('theme/adminLTE/dist/assets/img/AdminLTELogo.png', false);

        // Layout structural elements
        $response->assertSee('app-wrapper', false);
        $response->assertSee('app-header navbar', false);
        $response->assertSee('app-sidebar', false);
        $response->assertSee('app-content-header', false);
        $response->assertSee('app-footer', false);

        // Reusable components
        $response->assertSee('card-title', false);
        $response->assertSee('table-responsive', false);
        $response->assertSee('modal fade', false);
        $response->assertSee('CBT School', false);
    }
}
