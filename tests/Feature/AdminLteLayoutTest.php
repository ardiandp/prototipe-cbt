<?php

namespace Tests\Feature;

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLteLayoutTest extends TestCase
{
    use RefreshDatabase;

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

        // Ensure no harmful flex overrides destroy AdminLTE native CSS Grid
        $response->assertDontSee('.app-wrapper { min-height: 100vh; display: flex; flex-direction: column; }', false);

        // Reusable components
        $response->assertSee('card-title', false);
        $response->assertSee('table-responsive', false);
        $response->assertSee('modal fade', false);
        $response->assertSee('CBT School', false);
    }

    /**
     * Test that school profile page renders cleanly with AdminLTE layout and active sidebar menu.
     */
    public function test_school_profile_renders_with_adminlte_layout_and_active_sidebar(): void
    {
        $this->seed(\Database\Seeders\SchoolProfileSeeder::class);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.school.profile'));

        $response->assertStatus(200);
        $response->assertSee('app-wrapper', false);
        $response->assertSee('app-sidebar', false);
        $response->assertSee('app-main', false);
        $response->assertSee('app-header', false);
        $response->assertSee('Profil Sekolah');
        $response->assertSee('SMA Negeri 1 CBT Indonesia');
        $response->assertSee('active');
        $response->assertDontSee('.app-wrapper { min-height: 100vh; display: flex; flex-direction: column; }', false);
    }

    /**
     * Test that FAQ knowledge base page renders and includes guides for all roles.
     */
    public function test_faq_page_renders_with_knowledge_base(): void
    {
        $response = $this->get(route('faq'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Bantuan & FAQ');
        $response->assertSee('Admin Sekolah');
        $response->assertSee('Guru Pengampu');
        $response->assertSee('Siswa Peserta');
        $response->assertSee('Pengawas / Proktor');
        $response->assertSee('Troubleshooting LAN');
        $response->assertSee('app-wrapper', false);
    }
}
