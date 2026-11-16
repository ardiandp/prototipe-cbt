<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
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

class QuestionSeedingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed full base requirements
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

    public function test_question_bank_seeder_creates_expected_categories(): void
    {
        $expectedCodes = [
            'PWPB-XI-2026',
            'BD-XI-2026',
            'MTK-X-2026',
            'BIN-XII-2026',
            'ASJ-XI-2026',
        ];

        foreach ($expectedCodes as $code) {
            $this->assertDatabaseHas('question_categories', [
                'code' => $code,
                'is_active' => true,
            ]);

            $category = QuestionCategory::where('code', $code)->firstOrFail();
            $this->assertNotNull($category->subject_id);
            $this->assertNotNull($category->teacher_id);
            $this->assertGreaterThan(0, $category->questions()->count());
        }
    }

    public function test_all_seeded_questions_have_valid_structure_and_options(): void
    {
        $questions = Question::with('options')->get();

        $this->assertGreaterThanOrEqual(25, $questions->count(), 'Seeded questions should be at least 25 items across subjects');

        foreach ($questions as $q) {
            // Must have exactly 5 options (A - E)
            $this->assertCount(5, $q->options, "Question {$q->code} must have exactly 5 options");

            // Must have labels A, B, C, D, E
            $labels = $q->options->pluck('label')->sort()->values()->all();
            $this->assertEquals(['A', 'B', 'C', 'D', 'E'], $labels);

            // Must have exactly one correct option
            $correctCount = $q->options->where('is_correct', true)->count();
            $this->assertEquals(1, $correctCount, "Question {$q->code} must have exactly 1 correct answer");

            // Weight must be positive
            $this->assertGreaterThan(0, $q->weight, "Question {$q->code} must have positive weight");

            // Difficulty must be easy, medium, or hard
            $this->assertContains($q->difficulty, ['easy', 'medium', 'hard']);

            // Status must be active or draft
            $this->assertContains($q->status, ['active', 'draft']);

            // Non-empty question text and option texts
            $this->assertNotEmpty($q->question_text);
            foreach ($q->options as $opt) {
                $this->assertNotEmpty($opt->option_text);
            }
        }
    }

    public function test_question_bank_seeder_is_reproducible_and_idempotent(): void
    {
        $initialCategoryCount = QuestionCategory::count();
        $initialQuestionCount = Question::count();
        $initialOptionCount = QuestionOption::count();

        // Run seeder again
        $this->seed(QuestionBankSeeder::class);

        $this->assertEquals($initialCategoryCount, QuestionCategory::count(), 'Running seeder twice should not duplicate categories');
        $this->assertEquals($initialQuestionCount, Question::count(), 'Running seeder twice should not duplicate questions');
        $this->assertEquals($initialOptionCount, QuestionOption::count(), 'Running seeder twice should not duplicate options');
    }

    public function test_teacher_can_access_and_preview_seeded_questions(): void
    {
        $teacherUser = User::where('username', 'guru')->firstOrFail();
        $pwpbBank = QuestionCategory::where('code', 'PWPB-XI-2026')->firstOrFail();

        // Check index view
        $indexResponse = $this->actingAs($teacherUser)
            ->get(route('teacher.question-banks.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('PWPB-XI-2026');
        $indexResponse->assertSee('MTK-X-2026');

        // Check show view
        $showResponse = $this->actingAs($teacherUser)
            ->get(route('teacher.question-banks.show', $pwpbBank));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('PWPB-001');
        $showResponse->assertSee('Protokol HTTP');
        $showResponse->assertSee('POST');
        $showResponse->assertSee('Preview');
    }
}

