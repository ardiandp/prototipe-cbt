<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\User;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest and student can view FAQ but cannot see management buttons.
     */
    public function test_guest_and_student_can_view_faq_without_management_actions(): void
    {
        $this->seed(FaqSeeder::class);

        // Guest Access
        $response = $this->get(route('faq'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Bantuan &amp; FAQ', false);
        $response->assertSee('Umum &amp; LAN', false);
        $response->assertDontSee('+ Tambah FAQ', false);
        $response->assertDontSee('btn-edit-faq', false);

        // Student Access
        $student = User::factory()->student()->create();
        $responseStudent = $this->actingAs($student)->get(route('faq'));
        $responseStudent->assertStatus(200);
        $responseStudent->assertDontSee('+ Tambah FAQ', false);
        $responseStudent->assertDontSee('btn-edit-faq', false);
    }

    /**
     * Test that admin can view FAQ and sees management buttons.
     */
    public function test_admin_sees_management_buttons(): void
    {
        $this->seed(FaqSeeder::class);

        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin)->get(route('faq'));

        $response->assertStatus(200);
        $response->assertSee('+ Tambah FAQ', false);
        $response->assertSee('btn-edit-faq', false);
        $response->assertSee('addFaqModal', false);
        $response->assertSee('editFaqModal', false);
    }

    /**
     * Test that admin can create a new FAQ via POST.
     */
    public function test_admin_can_create_new_faq(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'category' => 'tech',
            'question' => 'Bagaimana jika kabel LAN terputus saat ujian?',
            'answer' => 'Sambungkan kembali kabel LAN, lalu muat ulang halaman. Jawaban Anda tetap tersimpan berkat fitur realtime autosave.',
            'order' => 5,
        ];

        $response = $this->actingAs($admin)->post(route('admin.faqs.store'), $payload);

        $response->assertRedirect(route('faq'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', [
            'category' => 'tech',
            'question' => 'Bagaimana jika kabel LAN terputus saat ujian?',
        ]);
    }

    /**
     * Test that admin can update an existing FAQ via PUT.
     */
    public function test_admin_can_update_existing_faq(): void
    {
        $admin = User::factory()->admin()->create();

        $faq = Faq::create([
            'category' => 'student',
            'question' => 'Pertanyaan awal yang belum diedit',
            'answer' => 'Jawaban awal',
            'order' => 1,
        ]);

        $updatePayload = [
            'category' => 'student',
            'question' => 'Pertanyaan revisi yang sudah diedit',
            'answer' => 'Jawaban yang telah disempurnakan oleh admin.',
            'order' => 2,
        ];

        $response = $this->actingAs($admin)->put(route('admin.faqs.update', $faq), $updatePayload);

        $response->assertRedirect(route('faq'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => 'Pertanyaan revisi yang sudah diedit',
        ]);
        $this->assertDatabaseMissing('faqs', [
            'question' => 'Pertanyaan awal yang belum diedit',
        ]);
    }

    /**
     * Test that admin can delete an FAQ via DELETE.
     */
    public function test_admin_can_delete_faq(): void
    {
        $admin = User::factory()->admin()->create();

        $faq = Faq::create([
            'category' => 'admin',
            'question' => 'Pertanyaan yang akan segera dihapus',
            'answer' => 'Jawaban sementara',
            'order' => 10,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.faqs.destroy', $faq));

        $response->assertRedirect(route('faq'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('faqs', [
            'id' => $faq->id,
        ]);
    }

    /**
     * Test that non-admin users cannot create, update, or delete FAQs.
     */
    public function test_non_admin_cannot_manage_faqs(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();

        $faq = Faq::create([
            'category' => 'general',
            'question' => 'Soal terlindungi',
            'answer' => 'Jawaban',
            'order' => 1,
        ]);

        // Student attempt
        $resStudentStore = $this->actingAs($student)->post(route('admin.faqs.store'), ['question' => 'test']);
        $resStudentStore->assertStatus(403);

        $resStudentUpdate = $this->actingAs($student)->put(route('admin.faqs.update', $faq), ['question' => 'hack']);
        $resStudentUpdate->assertStatus(403);

        $resStudentDelete = $this->actingAs($student)->delete(route('admin.faqs.destroy', $faq));
        $resStudentDelete->assertStatus(403);

        // Teacher attempt
        $resTeacherStore = $this->actingAs($teacher)->post(route('admin.faqs.store'), ['question' => 'test']);
        $resTeacherStore->assertStatus(403);

        $resTeacherDelete = $this->actingAs($teacher)->delete(route('admin.faqs.destroy', $faq));
        $resTeacherDelete->assertStatus(403);
    }
}
