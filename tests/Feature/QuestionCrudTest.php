<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ClassroomSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\QuestionBankSeeder;
use Database\Seeders\SchoolProfileSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\TeacherSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            SchoolProfileSeeder::class,
            AcademicPeriodSeeder::class,
            DepartmentSeeder::class,
            ClassroomSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
            StudentSeeder::class,
            QuestionBankSeeder::class,
        ]);
    }

    /**
     * Test teacher and admin can view question banks list.
     */
    public function test_teacher_and_admin_can_view_question_banks_list(): void
    {
        $guru = User::where('username', 'guru')->first();
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($guru)->get(route('teacher.question-banks.index'));
        $response->assertStatus(200);
        $response->assertSee('PWPB-XI-2026');
        $response->assertSee('Bank Soal Pemrograman Web');

        $response = $this->actingAs($admin)->get(route('teacher.question-banks.index'));
        $response->assertStatus(200);
        $response->assertSee('PWPB-XI-2026');
    }

    /**
     * Test teacher can create a new question bank.
     */
    public function test_teacher_can_create_question_bank(): void
    {
        $guru = User::where('username', 'guru')->first();
        $subject = Subject::where('code', 'BIN')->first();

        $response = $this->actingAs($guru)->post(route('teacher.question-banks.store'), [
            'code' => 'BIN-XI-2026',
            'name' => 'Bank Soal Bahasa Indonesia Teks Eksplanasi',
            'subject_id' => $subject->id,
            'level' => '11',
            'description' => 'Evaluasi teks eksplanasi ilmiah.',
            'is_active' => true,
        ]);

        $bank = QuestionCategory::where('code', 'BIN-XI-2026')->firstOrFail();
        $response->assertRedirect(route('teacher.question-banks.show', $bank));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('question_categories', [
            'code' => 'BIN-XI-2026',
            'name' => 'Bank Soal Bahasa Indonesia Teks Eksplanasi',
            'subject_id' => $subject->id,
        ]);
    }

    /**
     * Test question bank creation validation rules.
     */
    public function test_question_bank_validation_rules(): void
    {
        $guru = User::where('username', 'guru')->first();

        // Duplicate code and empty name
        $response = $this->actingAs($guru)->post(route('teacher.question-banks.store'), [
            'code' => 'PWPB-XI-2026', // already exists
            'name' => '',
            'subject_id' => 999999, // non-existent
        ]);

        $response->assertSessionHasErrors(['code', 'name', 'subject_id']);
    }

    /**
     * Test teacher can view question bank details with questions list.
     */
    public function test_teacher_can_view_question_bank_details(): void
    {
        $guru = User::where('username', 'guru')->first();
        $bank = QuestionCategory::where('code', 'PWPB-XI-2026')->firstOrFail();

        $response = $this->actingAs($guru)->get(route('teacher.question-banks.show', $bank));

        $response->assertStatus(200);
        $response->assertSee($bank->name);
        $response->assertSee('Protokol HTTP');
        $response->assertSee('POST');
    }

    /**
     * Test teacher can create question with options A-E and correct answer.
     */
    public function test_teacher_can_create_question_with_options_and_correct_answer(): void
    {
        $guru = User::where('username', 'guru')->first();
        $bank = QuestionCategory::where('code', 'PWPB-XI-2026')->firstOrFail();

        $response = $this->actingAs($guru)->post(route('teacher.questions.store', $bank), [
            'code' => 'PWPB-TEST-01',
            'question_text' => 'Manakah tag HTML5 yang digunakan untuk menampilkan video?',
            'difficulty' => 'easy',
            'weight' => 2.50,
            'status' => 'active',
            'options' => [
                'A' => '<media>',
                'B' => '<video>',
                'C' => '<movie>',
                'D' => '<player>',
                'E' => '<stream>',
            ],
            'correct_option' => 'B',
        ]);

        $response->assertRedirect(route('teacher.question-banks.show', $bank));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'question_category_id' => $bank->id,
            'code' => 'PWPB-TEST-01',
            'weight' => 2.50,
            'status' => 'active',
        ]);

        $question = Question::where('code', 'PWPB-TEST-01')->firstOrFail();
        $this->assertCount(5, $question->options);
        $this->assertEquals('B', $question->getCorrectAnswerLabel());
        $this->assertEquals('<video>', $question->correctOption->option_text);
    }

    /**
     * Test question creation validation requires all 5 options and positive weight.
     */
    public function test_create_question_validation_requires_all_options(): void
    {
        $guru = User::where('username', 'guru')->first();
        $bank = QuestionCategory::first();

        $response = $this->actingAs($guru)->post(route('teacher.questions.store', $bank), [
            'question_text' => '',
            'difficulty' => 'invalid_diff',
            'weight' => 0, // must be min 0.01
            'status' => 'active',
            'options' => [
                'A' => 'Option A',
                'B' => '', // empty option
            ],
            'correct_option' => 'Z', // invalid choice
        ]);

        $response->assertSessionHasErrors([
            'question_text',
            'difficulty',
            'weight',
            'options.B',
            'options.C',
            'options.D',
            'options.E',
            'correct_option',
        ]);
    }

    /**
     * Test teacher can update question and change correct option.
     */
    public function test_teacher_can_update_question_and_change_correct_answer(): void
    {
        $guru = User::where('username', 'guru')->first();
        $question = Question::where('code', 'PWPB-001')->firstOrFail();
        $bank = $question->category;

        $response = $this->actingAs($guru)->put(route('teacher.questions.update', [$bank, $question]), [
            'code' => 'PWPB-001-MOD',
            'question_text' => 'Teks soal diperbarui...',
            'difficulty' => 'hard',
            'weight' => 4.00,
            'status' => 'active',
            'options' => [
                'A' => 'GET Baru',
                'B' => 'POST Baru',
                'C' => 'PUT Baru (Kunci Baru)',
                'D' => 'DELETE Baru',
                'E' => 'PATCH Baru',
            ],
            'correct_option' => 'C',
        ]);

        $response->assertRedirect(route('teacher.question-banks.show', $bank));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'code' => 'PWPB-001-MOD',
            'difficulty' => 'hard',
            'weight' => 4.00,
        ]);

        $this->assertEquals('C', $question->fresh()->getCorrectAnswerLabel());
        $this->assertEquals('PUT Baru (Kunci Baru)', $question->fresh()->correctOption->option_text);
    }

    /**
     * Test teacher can duplicate an existing question.
     */
    public function test_teacher_can_duplicate_question(): void
    {
        $guru = User::where('username', 'guru')->first();
        $question = Question::where('code', 'PWPB-001')->firstOrFail();
        $bank = $question->category;

        $initialCount = $bank->questions()->count();

        $response = $this->actingAs($guru)->post(route('teacher.questions.duplicate', [$bank, $question]));

        $response->assertRedirect(route('teacher.question-banks.show', $bank));
        $response->assertSessionHas('success');

        $this->assertEquals($initialCount + 1, $bank->questions()->count());

        $duplicatedQuestion = Question::latest('id')->first();
        $this->assertEquals($question->question_text, $duplicatedQuestion->question_text);
        $this->assertEquals($question->getCorrectAnswerLabel(), $duplicatedQuestion->getCorrectAnswerLabel());
        $this->assertCount(5, $duplicatedQuestion->options);
    }

    /**
     * Test teacher can delete a question.
     */
    public function test_teacher_can_delete_question(): void
    {
        $guru = User::where('username', 'guru')->first();
        $question = Question::where('code', 'PWPB-002')->firstOrFail();
        $bank = $question->category;
        $questionId = $question->id;

        $response = $this->actingAs($guru)->delete(route('teacher.questions.destroy', [$bank, $question]));

        $response->assertRedirect(route('teacher.question-banks.show', $bank));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
        $this->assertEquals(0, QuestionOption::where('question_id', $questionId)->count());
    }

    /**
     * Test student cannot access question bank management.
     */
    public function test_student_cannot_access_question_banks(): void
    {
        $siswa = User::where('username', 'siswa')->first();

        $response = $this->actingAs($siswa)->get(route('teacher.question-banks.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('teacher.question-banks.index'));
        $response->assertRedirect(route('login'));
    }
}
