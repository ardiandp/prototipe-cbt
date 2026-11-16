<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Teacher;
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

class QuestionSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test question category can be created with subject and teacher relation.
     */
    public function test_question_category_creation_and_relations(): void
    {
        $subject = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS']);
        $teacher = Teacher::factory()->create();

        $category = QuestionCategory::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'code' => 'FIS-X-01',
            'name' => 'Bank Soal Fisika Mekanika',
            'level' => '10',
            'description' => 'Materi gerak lurus dan hukum Newton',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('question_categories', [
            'id' => $category->id,
            'code' => 'FIS-X-01',
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->assertEquals('Fisika', $category->subject->name);
        $this->assertEquals($teacher->id, $category->teacher->id);
    }

    /**
     * Test question creation with options A-E and single correct answer.
     */
    public function test_question_creation_with_options_and_single_correct_answer(): void
    {
        $category = QuestionCategory::factory()->create();
        $teacher = Teacher::factory()->create();

        $question = Question::create([
            'question_category_id' => $category->id,
            'teacher_id' => $teacher->id,
            'code' => 'QST-001',
            'question_text' => 'Berapakah hasil dari 10 + 25?',
            'difficulty' => 'easy',
            'weight' => 2.50,
            'status' => 'active',
            'order' => 1,
        ]);

        $options = [
            ['label' => 'A', 'text' => '25', 'is_correct' => false],
            ['label' => 'B', 'text' => '30', 'is_correct' => false],
            ['label' => 'C', 'text' => '35', 'is_correct' => true],
            ['label' => 'D', 'text' => '40', 'is_correct' => false],
            ['label' => 'E', 'text' => '45', 'is_correct' => false],
        ];

        foreach ($options as $opt) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $opt['label'],
                'option_text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
            ]);
        }

        $this->assertCount(5, $question->options);
        $this->assertEquals('C', $question->getCorrectAnswerLabel());
        $this->assertEquals('35', $question->correctOption->option_text);
        $this->assertEquals(2.50, $question->weight);
        $this->assertGreaterThan(0, $question->weight);
    }

    /**
     * Test setCorrectOption helper changes the active answer correctly.
     */
    public function test_set_correct_option_updates_active_answer(): void
    {
        $question = Question::factory()->withOptions('A')->create();

        $this->assertEquals('A', $question->fresh()->getCorrectAnswerLabel());

        // Switch correct answer to D
        $question->setCorrectOption('D');

        $this->assertEquals('D', $question->fresh()->getCorrectAnswerLabel());
        $this->assertEquals(1, $question->options()->where('is_correct', true)->count());
    }

    /**
     * Test cascade deletion: deleting question removes its options.
     */
    public function test_deleting_question_cascades_to_options(): void
    {
        $question = Question::factory()->withOptions('B')->create();
        $questionId = $question->id;

        $this->assertDatabaseHas('questions', ['id' => $questionId]);
        $this->assertEquals(5, QuestionOption::where('question_id', $questionId)->count());

        $question->delete();

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
        $this->assertEquals(0, QuestionOption::where('question_id', $questionId)->count());
    }

    /**
     * Test cascade deletion: deleting category removes its questions and options.
     */
    public function test_deleting_category_cascades_to_questions_and_options(): void
    {
        $category = QuestionCategory::factory()->create();
        $question = Question::factory()->withOptions('C')->create([
            'question_category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('question_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
        $this->assertEquals(5, QuestionOption::where('question_id', $question->id)->count());

        $category->delete();

        $this->assertDatabaseMissing('question_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertEquals(0, QuestionOption::where('question_id', $question->id)->count());
    }

    /**
     * Test question scopes for active and difficulty.
     */
    public function test_question_scopes(): void
    {
        $category = QuestionCategory::factory()->create();

        Question::factory()->create([
            'question_category_id' => $category->id,
            'difficulty' => 'easy',
            'status' => 'active',
        ]);

        Question::factory()->create([
            'question_category_id' => $category->id,
            'difficulty' => 'hard',
            'status' => 'draft',
        ]);

        $this->assertEquals(1, Question::active()->count());
        $this->assertEquals(1, Question::difficulty('easy')->count());
        $this->assertEquals(1, Question::difficulty('hard')->count());
    }

    /**
     * Test QuestionBankSeeder populates realistic questions and options.
     */
    public function test_question_bank_seeder_populates_data(): void
    {
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

        $this->assertDatabaseHas('question_categories', ['code' => 'PWPB-XI-2026']);
        $this->assertDatabaseHas('question_categories', ['code' => 'MTK-X-2026']);

        $pwpbCat = QuestionCategory::where('code', 'PWPB-XI-2026')->first();
        $this->assertGreaterThanOrEqual(3, $pwpbCat->questions()->count());

        foreach ($pwpbCat->questions as $q) {
            $this->assertCount(5, $q->options);
            $this->assertEquals(1, $q->options()->where('is_correct', true)->count());
            $this->assertGreaterThan(0, $q->weight);
        }
    }
}
