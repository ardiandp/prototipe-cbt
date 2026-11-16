<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSubmitAndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $studentUser;
    private Student $student;
    private Classroom $classroom;
    private Exam $exam;
    private ExamParticipant $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['code' => 'RPL', 'name' => 'Rekayasa Perangkat Lunak', 'is_active' => true]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $semester = Semester::create(['academic_year_id' => $year->id, 'name' => 'Ganjil', 'semester_type' => 'odd', 'start_date' => '2026-07-01', 'end_date' => '2026-12-31', 'is_active' => true]);
        $this->classroom = Classroom::create(['name' => 'XII RPL 1', 'level' => 'XII', 'department_id' => $dept->id, 'academic_year_id' => $year->id, 'is_active' => true]);

        $teacherUser = User::factory()->teacher()->create();
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'nip' => '198501012010011001', 'name' => 'Budi Santoso, S.Kom', 'is_active' => true]);
        $subject = Subject::create(['code' => 'PWPB', 'name' => 'Pemrograman Web', 'grade_level' => 'XII', 'group' => 'C3', 'is_active' => true]);
        $category = QuestionCategory::create(['subject_id' => $subject->id, 'code' => 'KAT-PWPB', 'name' => 'PHP & Laravel', 'is_active' => true]);

        $this->studentUser = User::factory()->student()->create(['username' => 'siswa_submit_test']);
        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'nis' => '12345',
            'name' => 'Siswa Submit Test',
            'gender' => 'L',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->exam = Exam::create([
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'code' => 'EXAM-SUBMIT-01',
            'title' => 'Ujian Akhir PWPB CBT',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addMinutes(50),
            'total_questions' => 3,
            'passing_grade' => 75.00,
            'status' => 'published',
            'randomize_questions' => true,
            'randomize_options' => true,
        ]);

        $this->participant = ExamParticipant::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'status' => 'assigned',
        ]);

        // Create 3 questions with options
        for ($i = 1; $i <= 3; $i++) {
            $bankQ = Question::create([
                'question_category_id' => $category->id,
                'teacher_id' => $teacher->id,
                'code' => "SOAL-SUB-0{$i}",
                'question_text' => "Pertanyaan Submit Ke-{$i} mengenai Laravel CBT",
                'weight' => 20,
                'difficulty' => 'medium',
                'status' => 'active',
                'order' => $i,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $idx => $label) {
                QuestionOption::create([
                    'question_id' => $bankQ->id,
                    'label' => $label,
                    'option_text' => "Pilihan {$label} untuk soal {$i}",
                    'is_correct' => $idx === 0,
                    'order' => $idx + 1,
                ]);
            }

            $eq = ExamQuestion::create([
                'exam_id' => $this->exam->id,
                'question_id' => $bankQ->id,
                'question_text' => $bankQ->question_text,
                'weight' => $bankQ->weight,
                'order' => $i,
            ]);

            foreach ($bankQ->options as $opt) {
                ExamQuestionOption::create([
                    'exam_question_id' => $eq->id,
                    'question_option_id' => $opt->id,
                    'label' => $opt->label,
                    'option_text' => $opt->option_text,
                    'is_correct' => $opt->is_correct,
                    'order' => $opt->order ?? 1,
                ]);
            }
        }
    }

    private function createAttempt(): ExamAttempt
    {
        $attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'started_at' => Carbon::now()->subMinutes(5),
            'expires_at' => Carbon::now()->addMinutes(55),
            'status' => 'in_progress',
            'total_answered' => 0,
        ]);

        $order = 1;
        foreach ($this->exam->questions as $eq) {
            $attQ = ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $eq->id,
                'order' => $order++,
            ]);

            foreach ($eq->options as $idx => $opt) {
                $attQ->options()->create([
                    'exam_question_option_id' => $opt->id,
                    'display_label' => chr(65 + $idx),
                    'order' => $idx + 1,
                ]);
            }
        }

        $this->participant->update(['status' => 'in_progress']);

        return $attempt;
    }

    public function test_student_can_see_finish_confirmation_modal_and_answer_summary(): void
    {
        $attempt = $this->createAttempt();

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));

        $response->assertStatus(200);
        $response->assertSee('confirmFinishModal', false);
        $response->assertSee('Konfirmasi Selesai Ujian');
        $response->assertSee('confirmFinishCheckbox', false);
        $response->assertSee('confirmFinishSubmitBtn', false);
        $response->assertSee('Ringkasan Status Pengerjaan');
        $response->assertSee('modalAnsweredCount', false);
        $response->assertSee('modalDoubtfulCount', false);
        $response->assertSee('modalUnansweredCount', false);
    }

    public function test_student_can_manually_submit_active_attempt(): void
    {
        $attempt = $this->createAttempt();

        // Answer question 1
        $firstQ = $attempt->attemptQuestions->first();
        $firstOpt = $firstQ->options->first();
        $firstQ->update([
            'selected_exam_question_option_id' => $firstOpt->exam_question_option_id,
            'answered_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('student.attempts.finish', $attempt));

        $response->assertRedirect(route('student.exams.show', $this->exam));
        $response->assertSessionHas('success');

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertNotNull($attempt->completed_at);
        $this->assertEquals(1, $attempt->total_answered);

        $this->participant->refresh();
        $this->assertEquals('completed', $this->participant->status);
    }

    public function test_manual_submit_is_idempotent(): void
    {
        $attempt = $this->createAttempt();
        $firstCompletedAt = Carbon::now()->subMinutes(2);

        $attempt->update([
            'status' => 'completed',
            'completed_at' => $firstCompletedAt,
            'total_answered' => 2,
        ]);

        $response = $this->actingAs($this->studentUser)
            ->post(route('student.attempts.finish', $attempt));

        $response->assertRedirect(route('student.exams.show', $this->exam));

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertEquals($firstCompletedAt->timestamp, $attempt->completed_at->timestamp);
        $this->assertEquals(2, $attempt->total_answered);
    }

    public function test_manual_submit_supports_json_request(): void
    {
        $attempt = $this->createAttempt();

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('student.attempts.finish', $attempt));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('student.exams.show', $this->exam),
        ]);

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
    }

    public function test_unauthorized_student_cannot_submit_another_students_attempt(): void
    {
        $attempt = $this->createAttempt();

        $otherStudentUser = User::factory()->student()->create();
        Student::create([
            'user_id' => $otherStudentUser->id,
            'nis' => '99999',
            'name' => 'Siswa Lain',
            'gender' => 'P',
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($otherStudentUser)
            ->post(route('student.attempts.finish', $attempt));

        $response->assertStatus(403);

        $attempt->refresh();
        $this->assertEquals('in_progress', $attempt->status);
    }

    public function test_answers_cannot_be_saved_after_attempt_is_completed(): void
    {
        $attempt = $this->createAttempt();
        $attempt->markAsCompleted();

        $firstQ = $attempt->attemptQuestions->first();
        $firstOpt = $firstQ->options->first();

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $firstQ->id,
                'selected_exam_question_option_id' => $firstOpt->exam_question_option_id,
                'is_doubtful' => false,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'is_completed' => true,
        ]);

        $firstQ->refresh();
        $this->assertNull($firstQ->selected_exam_question_option_id);
    }

    public function test_student_cannot_reopen_exam_room_after_attempt_is_completed(): void
    {
        $attempt = $this->createAttempt();
        $attempt->markAsCompleted();

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));

        $response->assertRedirect(route('student.exams.show', $this->exam));
        $response->assertSessionHas('info', 'Sesi ujian ini telah diselesaikan.');
    }

    public function test_automatic_submit_when_attempt_expires(): void
    {
        $attempt = $this->createAttempt();
        $attempt->update([
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $response = $this->actingAs($this->studentUser)
            ->postJson(route('student.attempts.finalize-expired', $attempt));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'expired',
            'redirect_url' => route('student.exams.show', $this->exam),
        ]);

        $attempt->refresh();
        $this->assertEquals('expired', $attempt->status);
        $this->assertNotNull($attempt->completed_at);

        $this->participant->refresh();
        $this->assertEquals('completed', $this->participant->status);
    }

    public function test_full_exam_lifecycle_from_start_to_answer_autosave_to_submit(): void
    {
        // 1. Student starts exam
        $responseStart = $this->actingAs($this->studentUser)
            ->post(route('student.exams.start', $this->exam));

        $responseStart->assertRedirect();
        $attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->first();
        $this->assertNotNull($attempt);
        $this->assertEquals('in_progress', $attempt->status);
        $attempt->load('attemptQuestions.options');

        // 2. Student answers Q1 with doubtful flag
        $q1 = $attempt->attemptQuestions[0];
        $opt1 = $q1->options->first();

        $saveResp1 = $this->actingAs($this->studentUser)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $q1->id,
                'selected_exam_question_option_id' => $opt1->exam_question_option_id,
                'is_doubtful' => true,
            ]);
        $saveResp1->assertStatus(200);
        $saveResp1->assertJson([
            'success' => true,
            'answered_count' => 0,
            'doubtful_count' => 1,
            'unanswered_count' => 2,
        ]);

        // 3. Student answers Q2 without doubtful
        $q2 = $attempt->attemptQuestions[1];
        $opt2 = $q2->options->first();

        $saveResp2 = $this->actingAs($this->studentUser)
            ->postJson(route('student.attempts.save-answer', $attempt), [
                'exam_attempt_question_id' => $q2->id,
                'selected_exam_question_option_id' => $opt2->exam_question_option_id,
                'is_doubtful' => false,
            ]);
        $saveResp2->assertStatus(200);
        $saveResp2->assertJson([
            'success' => true,
            'answered_count' => 1,
            'doubtful_count' => 1,
            'unanswered_count' => 1,
        ]);

        // 4. Student reloads exam room -> answers and doubtful flags are restored
        $roomResp = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));
        $roomResp->assertStatus(200);
        $roomResp->assertViewHas('answeredCount', 1);
        $roomResp->assertViewHas('doubtfulCount', 1);
        $roomResp->assertViewHas('unansweredCount', 1);

        // 5. Student submits attempt
        $submitResp = $this->actingAs($this->studentUser)
            ->post(route('student.attempts.finish', $attempt));
        $submitResp->assertRedirect(route('student.exams.show', $this->exam));

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertEquals(2, $attempt->total_answered);

        // 6. Student portal displays completed card
        $portalResp = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $this->exam));
        $portalResp->assertStatus(200);
        $portalResp->assertSee('Sesi Ujian Telah Selesai');
        $portalResp->assertSee('Telah Dikumpulkan');
        $portalResp->assertSee('2 / 3');

        // 7. Student cannot start a new attempt
        $secondStartResp = $this->actingAs($this->studentUser)
            ->post(route('student.exams.start', $this->exam));
        $secondStartResp->assertRedirect(route('student.exams.show', $this->exam));
        $secondStartResp->assertSessionHas('error');
    }
}
