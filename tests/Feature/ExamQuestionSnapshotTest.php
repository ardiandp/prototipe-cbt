<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamQuestionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $otherTeacherUser;
    private Teacher $otherTeacher;
    private User $adminUser;
    private User $studentUser;
    private Subject $subject;
    private QuestionCategory $questionCategory;
    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $semester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $this->teacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);

        $this->otherTeacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->otherTeacher = Teacher::factory()->create([
            'user_id' => $this->otherTeacherUser->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);

        $this->subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
        $this->teacher->subjects()->attach($this->subject);

        $this->questionCategory = QuestionCategory::factory()->create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'name' => 'Bank Soal Web XI',
            'is_active' => true,
        ]);

        $this->exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'title' => 'Ujian Akhir Semester Web',
            'total_questions' => 0,
        ]);
    }

    public function test_teacher_can_view_exam_question_index(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.questions.index', $this->exam));

        $response->assertStatus(200);
        $response->assertSee('Snapshot Butir Soal Ujian');
        $response->assertSee('Belum ada butir soal yang dipilih');
    }

    public function test_teacher_can_view_question_picker_filtered_by_subject(): void
    {
        // 1. Question in same subject
        $qInSubject = Question::factory()->withOptions('B')->create([
            'question_category_id' => $this->questionCategory->id,
            'question_text' => 'Soal Web yang Cocok',
        ]);

        // 2. Question in other subject
        $otherSubject = Subject::factory()->create(['name' => 'Matematika']);
        $otherCategory = QuestionCategory::factory()->create([
            'subject_id' => $otherSubject->id,
            'name' => 'Bank Soal Matematika',
        ]);
        $qInOtherSubject = Question::factory()->withOptions('A')->create([
            'question_category_id' => $otherCategory->id,
            'question_text' => 'Soal Matematika yang Tidak Cocok',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.exams.questions.create', $this->exam));

        $response->assertStatus(200);
        $response->assertSee('Soal Web yang Cocok');
        $response->assertDontSee('Soal Matematika yang Tidak Cocok');
    }

    public function test_teacher_can_snapshot_questions_into_exam(): void
    {
        $q1 = Question::factory()->withOptions('A')->create([
            'question_category_id' => $this->questionCategory->id,
            'code' => 'QST-001',
            'question_text' => 'Pertanyaan Nomor Satu',
            'weight' => 2.00,
            'difficulty' => 'easy',
        ]);

        $q2 = Question::factory()->withOptions('C')->create([
            'question_category_id' => $this->questionCategory->id,
            'code' => 'QST-002',
            'question_text' => 'Pertanyaan Nomor Dua',
            'weight' => 3.00,
            'difficulty' => 'medium',
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.questions.store', $this->exam), [
                'question_ids' => [$q1->id, $q2->id],
            ]);

        $response->assertRedirect(route('teacher.exams.questions.index', $this->exam));
        $response->assertSessionHas('success');

        $this->exam->refresh();
        $this->assertEquals(2, $this->exam->total_questions);
        $this->assertEquals(5.00, $this->exam->total_weight);

        // Verify ExamQuestions exist
        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $this->exam->id,
            'question_id' => $q1->id,
            'code' => 'QST-001',
            'order' => 1,
            'weight' => 2.00,
        ]);

        $this->assertDatabaseHas('exam_questions', [
            'exam_id' => $this->exam->id,
            'question_id' => $q2->id,
            'code' => 'QST-002',
            'order' => 2,
            'weight' => 3.00,
        ]);

        // Verify Options are snapshotted
        $examQ1 = ExamQuestion::where('exam_id', $this->exam->id)->where('order', 1)->firstOrFail();
        $this->assertCount(5, $examQ1->options);
        $this->assertEquals('A', $examQ1->getCorrectAnswerLabel());

        $examQ2 = ExamQuestion::where('exam_id', $this->exam->id)->where('order', 2)->firstOrFail();
        $this->assertCount(5, $examQ2->options);
        $this->assertEquals('C', $examQ2->getCorrectAnswerLabel());
    }

    public function test_snapshot_is_immutable_against_question_bank_modifications(): void
    {
        $q = Question::factory()->withOptions('B')->create([
            'question_category_id' => $this->questionCategory->id,
            'code' => 'ORIG-CODE',
            'question_text' => 'Original Question Text',
            'weight' => 2.50,
            'difficulty' => 'easy',
        ]);

        // Create snapshot in exam
        $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.questions.store', $this->exam), [
                'question_ids' => [$q->id],
            ]);

        $examQ = ExamQuestion::where('exam_id', $this->exam->id)->firstOrFail();
        $this->assertEquals('Original Question Text', $examQ->question_text);
        $this->assertEquals(2.50, $examQ->weight);
        $this->assertEquals('B', $examQ->getCorrectAnswerLabel());

        // Now mutate original question and its options in question bank
        $q->update([
            'code' => 'MUTATED-CODE',
            'question_text' => 'MODIFIED Question Text In Bank',
            'weight' => 99.00,
            'difficulty' => 'hard',
        ]);
        $q->setCorrectOption('E');

        // Refresh snapshot model
        $examQ->refresh();
        $examQ->load('options');

        // Snapshot remains untouched!
        $this->assertEquals('ORIG-CODE', $examQ->code);
        $this->assertEquals('Original Question Text', $examQ->question_text);
        $this->assertEquals(2.50, $examQ->weight);
        $this->assertEquals('easy', $examQ->difficulty);
        $this->assertEquals('B', $examQ->getCorrectAnswerLabel());
    }

    public function test_snapshot_is_preserved_when_original_question_is_deleted(): void
    {
        $q = Question::factory()->withOptions('A')->create([
            'question_category_id' => $this->questionCategory->id,
            'question_text' => 'Question to be deleted in bank',
        ]);

        // Snapshot into exam
        $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.questions.store', $this->exam), [
                'question_ids' => [$q->id],
            ]);

        $examQ = ExamQuestion::where('exam_id', $this->exam->id)->firstOrFail();

        // Delete question from question bank
        $q->delete();
        $this->assertDatabaseMissing('questions', ['id' => $q->id]);

        // Snapshot still exists in exam!
        $examQ->refresh();
        $this->assertNull($examQ->question_id); // foreign key set to null
        $this->assertEquals('Question to be deleted in bank', $examQ->question_text);
        $this->assertCount(5, $examQ->options);
        $this->assertEquals('A', $examQ->getCorrectAnswerLabel());

        $this->exam->refresh();
        $this->assertEquals(1, $this->exam->total_questions);
    }

    public function test_teacher_can_remove_snapshot_question_from_exam(): void
    {
        $q1 = Question::factory()->withOptions('A')->create(['question_category_id' => $this->questionCategory->id]);
        $q2 = Question::factory()->withOptions('B')->create(['question_category_id' => $this->questionCategory->id]);
        $q3 = Question::factory()->withOptions('C')->create(['question_category_id' => $this->questionCategory->id]);

        $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.questions.store', $this->exam), [
                'question_ids' => [$q1->id, $q2->id, $q3->id],
            ]);

        $examQ2 = ExamQuestion::where('exam_id', $this->exam->id)->where('order', 2)->firstOrFail();

        // Remove question #2
        $response = $this->actingAs($this->teacherUser)
            ->delete(route('teacher.exams.questions.destroy', [$this->exam, $examQ2]));

        $response->assertRedirect(route('teacher.exams.questions.index', $this->exam));

        $this->exam->refresh();
        $this->assertEquals(2, $this->exam->total_questions);

        // Verify remaining questions re-sequenced to 1 and 2
        $orders = ExamQuestion::where('exam_id', $this->exam->id)->orderBy('order')->pluck('order')->all();
        $this->assertEquals([1, 2], $orders);
    }

    public function test_teacher_cannot_manage_questions_of_exam_owned_by_another_teacher(): void
    {
        $otherExam = Exam::factory()->create([
            'teacher_id' => $this->otherTeacher->id,
            'subject_id' => $this->subject->id,
        ]);

        $q = Question::factory()->withOptions('A')->create([
            'question_category_id' => $this->questionCategory->id,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->post(route('teacher.exams.questions.store', $otherExam), [
                'question_ids' => [$q->id],
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_manage_questions_for_any_exam(): void
    {
        $q = Question::factory()->withOptions('A')->create([
            'question_category_id' => $this->questionCategory->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('teacher.exams.questions.store', $this->exam), [
                'question_ids' => [$q->id],
            ]);

        $response->assertRedirect(route('teacher.exams.questions.index', $this->exam));

        $this->exam->refresh();
        $this->assertEquals(1, $this->exam->total_questions);
    }

    public function test_student_cannot_access_exam_questions_management(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('teacher.exams.questions.index', $this->exam));

        $response->assertStatus(403);
    }
}

