<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private QuestionCategory $questionBank;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->teacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);

        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);

        $subject = Subject::factory()->create();

        $this->questionBank = QuestionCategory::factory()->create([
            'subject_id' => $subject->id,
            'teacher_id' => $this->teacher->id,
            'code' => 'BNK-MEDIA-01',
            'name' => 'Bank Soal Bergambar',
        ]);
    }

    public function test_teacher_can_create_question_with_image(): void
    {
        $image = UploadedFile::fake()->image('diagram_jaringan.png', 600, 400);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.questions.store', $this->questionBank), [
                'code' => 'SOAL-IMG-01',
                'question_text' => 'Perhatikan skema topologi jaringan berikut ini:',
                'question_image' => $image,
                'difficulty' => 'medium',
                'weight' => 2.50,
                'status' => 'active',
                'options' => [
                    'A' => 'Topologi Star',
                    'B' => 'Topologi Mesh',
                    'C' => 'Topologi Ring',
                    'D' => 'Topologi Bus',
                    'E' => 'Topologi Tree',
                ],
                'correct_option' => 'A',
            ]);

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));
        $response->assertSessionHas('success');

        $question = Question::where('code', 'SOAL-IMG-01')->firstOrFail();
        $this->assertNotNull($question->question_image);
        $this->assertTrue($question->hasImage());
        $this->assertStringStartsWith('questions/', $question->question_image);

        Storage::disk('public')->assertExists($question->question_image);
    }

    public function test_teacher_can_create_question_with_option_images(): void
    {
        $optAImg = UploadedFile::fake()->image('opt_a.png', 200, 200);
        $optBImg = UploadedFile::fake()->image('opt_b.png', 200, 200);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.questions.store', $this->questionBank), [
                'code' => 'SOAL-OPT-IMG',
                'question_text' => 'Pilih logo bahasa pemrograman PHP yang benar:',
                'difficulty' => 'easy',
                'weight' => 2.00,
                'status' => 'active',
                'options' => [
                    'A' => 'Logo Berwarna Ungu',
                    'B' => 'Logo Berwarna Biru',
                    'C' => 'Logo Ular',
                    'D' => 'Logo Ikan Lumba-lumba',
                    'E' => 'Logo Unta',
                ],
                'option_images' => [
                    'A' => $optAImg,
                    'B' => $optBImg,
                ],
                'correct_option' => 'A',
            ]);

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $question = Question::where('code', 'SOAL-OPT-IMG')->firstOrFail();
        $optA = $question->options()->where('label', 'A')->firstOrFail();
        $optB = $question->options()->where('label', 'B')->firstOrFail();
        $optC = $question->options()->where('label', 'C')->firstOrFail();

        $this->assertTrue($optA->hasImage());
        $this->assertTrue($optB->hasImage());
        $this->assertFalse($optC->hasImage());

        Storage::disk('public')->assertExists($optA->option_image);
        Storage::disk('public')->assertExists($optB->option_image);
    }

    public function test_file_validation_rejects_non_image_or_oversized_file(): void
    {
        // 1. Non-image file (e.g. PDF)
        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.questions.store', $this->questionBank), [
                'code' => 'SOAL-INVALID-FILE',
                'question_text' => 'Soal dengan file tidak valid',
                'question_image' => $fakePdf,
                'difficulty' => 'easy',
                'weight' => 2.00,
                'status' => 'active',
                'options' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                ],
                'correct_option' => 'A',
            ]);

        $response->assertSessionHasErrors(['question_image']);

        // 2. Oversized image (> 2MB / 2048 KB)
        $oversizedImg = UploadedFile::fake()->create('huge.png', 3000, 'image/png');

        $response2 = $this->actingAs($this->teacherUser)
            ->post(route('teacher.questions.store', $this->questionBank), [
                'code' => 'SOAL-OVERSIZED-FILE',
                'question_text' => 'Soal dengan gambar terlalu besar',
                'question_image' => $oversizedImg,
                'difficulty' => 'easy',
                'weight' => 2.00,
                'status' => 'active',
                'options' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                ],
                'correct_option' => 'A',
            ]);

        $response2->assertSessionHasErrors(['question_image']);
    }

    public function test_teacher_can_replace_question_image(): void
    {
        $oldFile = UploadedFile::fake()->image('old_image.png');
        $oldPath = $oldFile->store('questions', 'public');

        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
            'question_image' => $oldPath,
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'option_text' => 'A', 'is_correct' => true, 'order' => 1],
            ['label' => 'B', 'option_text' => 'B', 'is_correct' => false, 'order' => 2],
            ['label' => 'C', 'option_text' => 'C', 'is_correct' => false, 'order' => 3],
            ['label' => 'D', 'option_text' => 'D', 'is_correct' => false, 'order' => 4],
            ['label' => 'E', 'option_text' => 'E', 'is_correct' => false, 'order' => 5],
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newImage = UploadedFile::fake()->image('new_image.jpg');

        $response = $this->actingAs($this->teacherUser)
            ->put(route('teacher.questions.update', [$this->questionBank, $question]), [
                'code' => $question->code,
                'question_text' => 'Teks diperbarui dengan gambar baru',
                'question_image' => $newImage,
                'difficulty' => 'hard',
                'weight' => 3.00,
                'status' => 'active',
                'options' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                ],
                'correct_option' => 'B',
            ]);

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $question->refresh();
        $this->assertNotEquals($oldPath, $question->question_image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($question->question_image);
    }

    public function test_teacher_can_remove_existing_question_image(): void
    {
        $file = UploadedFile::fake()->image('to_remove.png');
        $path = $file->store('questions', 'public');

        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
            'question_image' => $path,
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'option_text' => 'A', 'is_correct' => true, 'order' => 1],
            ['label' => 'B', 'option_text' => 'B', 'is_correct' => false, 'order' => 2],
            ['label' => 'C', 'option_text' => 'C', 'is_correct' => false, 'order' => 3],
            ['label' => 'D', 'option_text' => 'D', 'is_correct' => false, 'order' => 4],
            ['label' => 'E', 'option_text' => 'E', 'is_correct' => false, 'order' => 5],
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($this->teacherUser)
            ->put(route('teacher.questions.update', [$this->questionBank, $question]), [
                'code' => $question->code,
                'question_text' => $question->question_text,
                'remove_question_image' => 1,
                'difficulty' => $question->difficulty,
                'weight' => $question->weight,
                'status' => $question->status,
                'options' => [
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                ],
                'correct_option' => 'A',
            ]);

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $question->refresh();
        $this->assertNull($question->question_image);
        $this->assertFalse($question->hasImage());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_teacher_can_remove_existing_option_image(): void
    {
        $optFile = UploadedFile::fake()->image('opt_remove.png');
        $optPath = $optFile->store('question_options', 'public');

        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
        ]);
        $optA = $question->options()->create([
            'label' => 'A',
            'option_text' => 'Opsi A dengan gambar',
            'option_image' => $optPath,
            'is_correct' => true,
            'order' => 1,
        ]);
        foreach (['B', 'C', 'D', 'E'] as $idx => $label) {
            $question->options()->create([
                'label' => $label,
                'option_text' => 'Opsi ' . $label,
                'is_correct' => false,
                'order' => $idx + 2,
            ]);
        }

        Storage::disk('public')->assertExists($optPath);

        $response = $this->actingAs($this->teacherUser)
            ->put(route('teacher.questions.update', [$this->questionBank, $question]), [
                'code' => $question->code,
                'question_text' => $question->question_text,
                'difficulty' => $question->difficulty,
                'weight' => $question->weight,
                'status' => $question->status,
                'options' => [
                    'A' => 'Opsi A tanpa gambar',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                    'E' => 'E',
                ],
                'remove_option_images' => [
                    'A' => 1,
                ],
                'correct_option' => 'A',
            ]);

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $optA->refresh();
        $this->assertNull($optA->option_image);
        $this->assertFalse($optA->hasImage());
        Storage::disk('public')->assertMissing($optPath);
    }

    public function test_duplicate_question_copies_image_files_independently(): void
    {
        $qFile = UploadedFile::fake()->image('original_q.png');
        $qPath = $qFile->store('questions', 'public');

        $optFile = UploadedFile::fake()->image('original_opt.png');
        $optPath = $optFile->store('question_options', 'public');

        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
            'code' => 'DUPL-ORIG',
            'question_image' => $qPath,
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'option_text' => 'Opsi A', 'option_image' => $optPath, 'is_correct' => true, 'order' => 1],
            ['label' => 'B', 'option_text' => 'Opsi B', 'is_correct' => false, 'order' => 2],
            ['label' => 'C', 'option_text' => 'Opsi C', 'is_correct' => false, 'order' => 3],
            ['label' => 'D', 'option_text' => 'Opsi D', 'is_correct' => false, 'order' => 4],
            ['label' => 'E', 'option_text' => 'Opsi E', 'is_correct' => false, 'order' => 5],
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.questions.duplicate', [$this->questionBank, $question]));

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $duplicated = Question::where('code', 'DUPL-ORIG-COPY')->firstOrFail();
        $this->assertNotNull($duplicated->question_image);
        $this->assertNotEquals($qPath, $duplicated->question_image);

        // Both original and duplicate files exist independently
        Storage::disk('public')->assertExists($qPath);
        Storage::disk('public')->assertExists($duplicated->question_image);

        $duplicatedOptA = $duplicated->options()->where('label', 'A')->firstOrFail();
        $this->assertNotNull($duplicatedOptA->option_image);
        $this->assertNotEquals($optPath, $duplicatedOptA->option_image);

        Storage::disk('public')->assertExists($optPath);
        Storage::disk('public')->assertExists($duplicatedOptA->option_image);
    }

    public function test_deleting_question_removes_image_files_from_storage(): void
    {
        $qFile = UploadedFile::fake()->image('to_delete_q.png');
        $qPath = $qFile->store('questions', 'public');

        $optFile = UploadedFile::fake()->image('to_delete_opt.png');
        $optPath = $optFile->store('question_options', 'public');

        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
            'question_image' => $qPath,
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'option_text' => 'A', 'option_image' => $optPath, 'is_correct' => true, 'order' => 1],
            ['label' => 'B', 'option_text' => 'B', 'is_correct' => false, 'order' => 2],
            ['label' => 'C', 'option_text' => 'C', 'is_correct' => false, 'order' => 3],
            ['label' => 'D', 'option_text' => 'D', 'is_correct' => false, 'order' => 4],
            ['label' => 'E', 'option_text' => 'E', 'is_correct' => false, 'order' => 5],
        ]);

        Storage::disk('public')->assertExists($qPath);
        Storage::disk('public')->assertExists($optPath);

        $response = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.questions.destroy', [$this->questionBank, $question]));

        $response->assertRedirect(route('teacher.question-banks.show', $this->questionBank));

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        Storage::disk('public')->assertMissing($qPath);
        Storage::disk('public')->assertMissing($optPath);
    }

    public function test_media_storage_fallback_route_serves_public_image(): void
    {
        Storage::disk('public')->put('questions/sample-diagram.png', 'fake-image-bytes');

        $response = $this->get('/storage/questions/sample-diagram.png');
        $response->assertStatus(200);

        // Missing file returns 404
        $notFoundResponse = $this->get('/storage/questions/non-existent.png');
        $notFoundResponse->assertStatus(404);
    }

    public function test_preview_modal_and_show_page_render_question_and_option_images(): void
    {
        $question = Question::factory()->create([
            'question_category_id' => $this->questionBank->id,
            'question_text' => 'Teks soal dengan gambar preview khusus',
            'question_image' => 'questions/preview-target.png',
        ]);
        $question->options()->createMany([
            ['label' => 'A', 'option_text' => 'Pilihan A Spesial', 'option_image' => 'question_options/opt_target.png', 'is_correct' => true, 'order' => 1],
            ['label' => 'B', 'option_text' => 'Pilihan B', 'is_correct' => false, 'order' => 2],
            ['label' => 'C', 'option_text' => 'Pilihan C', 'is_correct' => false, 'order' => 3],
            ['label' => 'D', 'option_text' => 'Pilihan D', 'is_correct' => false, 'order' => 4],
            ['label' => 'E', 'option_text' => 'Pilihan E', 'is_correct' => false, 'order' => 5],
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.question-banks.show', $this->questionBank));

        $response->assertStatus(200);
        $response->assertSee('Bergambar');
        $response->assertSee('storage/questions/preview-target.png');
        $response->assertSee('storage/question_options/opt_target.png');
    }
}
