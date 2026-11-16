<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPeriodTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view academic years list.
     */
    public function test_admin_can_view_academic_years_list(): void
    {
        $this->seed(AcademicPeriodSeeder::class);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.academic-years.index'));

        $response->assertStatus(200);
        $response->assertSee('2025/2026');
        $response->assertSee('2026/2027');
        $response->assertSee('Semester Ganjil');
        $response->assertSee('Semester Genap');
        $response->assertSee('Aktif');
    }

    /**
     * Test admin can create a new academic year and its default semesters are generated.
     */
    public function test_admin_can_create_academic_year_with_default_semesters(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.academic-years.store'), [
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('academic_years', [
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $year = AcademicYear::where('name', '2027/2028')->firstOrFail();
        $this->assertCount(2, $year->semesters);
        $this->assertEquals(['Ganjil', 'Genap'], $year->semesters->pluck('name')->toArray());
    }

    /**
     * Test academic year name must be unique.
     */
    public function test_academic_year_validation_requires_unique_name(): void
    {
        $admin = User::factory()->admin()->create();
        AcademicYear::factory()->create(['name' => '2025/2026']);

        $response = $this->actingAs($admin)->post(route('admin.academic-years.store'), [
            'name' => '2025/2026',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Test activating an academic year deactivates others.
     */
    public function test_activating_academic_year_deactivates_others(): void
    {
        $admin = User::factory()->admin()->create();

        $year1 = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);
        $year2 = AcademicYear::factory()->create(['name' => '2026/2027', 'is_active' => false]);

        $year2->semesters()->createMany([
            ['name' => 'Ganjil', 'is_active' => false],
            ['name' => 'Genap', 'is_active' => false],
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.academic-years.activate', $year2));

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($year1->fresh()->is_active);
        $this->assertTrue($year2->fresh()->is_active);
        // First semester of year2 should also be active
        $this->assertTrue($year2->semesters()->where('name', 'Ganjil')->first()->is_active);
    }

    /**
     * Test activating a semester switches active status and sets parent academic year as active.
     */
    public function test_activating_semester_switches_active_semester(): void
    {
        $admin = User::factory()->admin()->create();

        $year = AcademicYear::factory()->create(['name' => '2026/2027', 'is_active' => true]);
        $sem1 = $year->semesters()->create(['name' => 'Ganjil', 'is_active' => true]);
        $sem2 = $year->semesters()->create(['name' => 'Genap', 'is_active' => false]);

        $response = $this->actingAs($admin)
            ->post(route('admin.semesters.activate', $sem2));

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($sem1->fresh()->is_active);
        $this->assertTrue($sem2->fresh()->is_active);
        $this->assertTrue($year->fresh()->is_active);
    }

    /**
     * Test active academic year cannot be deleted.
     */
    public function test_active_academic_year_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();

        $year = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => true]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.academic-years.destroy', $year));

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
    }

    /**
     * Test inactive academic year can be deleted.
     */
    public function test_inactive_academic_year_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();

        $year = AcademicYear::factory()->create(['name' => '2025/2026', 'is_active' => false]);
        $year->semesters()->create(['name' => 'Ganjil', 'is_active' => false]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.academic-years.destroy', $year));

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('academic_years', ['id' => $year->id]);
        $this->assertDatabaseMissing('semesters', ['academic_year_id' => $year->id]);
    }

    /**
     * Test unauthorized users cannot access academic year management.
     */
    public function test_unauthorized_roles_cannot_access_academic_years(): void
    {
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();

        $response = $this->actingAs($teacher)->get(route('admin.academic-years.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($student)->get(route('admin.academic-years.index'));
        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.academic-years.index'));
        $response->assertRedirect(route('login'));
    }
}
