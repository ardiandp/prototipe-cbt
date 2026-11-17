<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanDeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_adminlte_theme_assets_are_loaded_locally_without_external_cdns(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // 1. Verify local AdminLTE assets are present
        $this->assertStringContainsString('theme/adminLTE/dist/css/adminlte.min.css', $content);
        $this->assertStringContainsString('theme/adminLTE/dist/js/adminlte.min.js', $content);

        // 2. Strict check: Zero external CDN dependencies
        $forbiddenCdns = [
            'cdnjs.cloudflare.com',
            'cdn.jsdelivr.net',
            'unpkg.com',
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'stackpath.bootstrapcdn.com',
            'code.jquery.com',
        ];

        foreach ($forbiddenCdns as $cdn) {
            $this->assertStringNotContainsString($cdn, $content, "External CDN [{$cdn}] must not be loaded in master layout.");
        }
    }

    public function test_database_driver_is_configured_for_sqlite_prototype(): void
    {
        $this->assertEquals('sqlite', config('database.default'));
    }

    public function test_server_clock_and_offline_fallbacks_are_present_in_master_layout(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Server time element
        $this->assertStringContainsString('server-clock', $content);
        $this->assertStringContainsString('WIB', $content);

        // Offline vanilla JS fallback handlers
        $this->assertStringContainsString('data-bs-toggle="dropdown"', $content);
        $this->assertStringContainsString('data-bs-dismiss="alert"', $content);
    }
}

