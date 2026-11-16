<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamAttemptQuestionOption;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamRoomUiTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;
    private Teacher $teacher;
    private User $studentUser;
    private Student $student;
    private Subject $subject;
    private AcademicYear $academicYear;
    private Semester $semester;
    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::factory()->create(['is_active' => true]);
        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->classroom = Classroom::factory()->create(['name' => 'XII-TKJ-1', 'level' => 12]);

        $this->teacherUser = User::factory()->create([
            'role' => UserRole::TEACHER,
            'is_active' => true,
        ]);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);

        $this->studentUser = User::factory()->create([
            'role' => UserRole::STUDENT,
            'is_active' => true,
        ]);
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'classroom_id' => $this->classroom->id,
            'is_active' => true,
        ]);

        $this->subject = Subject::factory()->create(['name' => 'Administrasi Sistem Jaringan']);
    }

    private function createAttemptWithQuestions(int $questionCount = 3): ExamAttempt
    {
        $exam = Exam::create([
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'code' => 'EXAM-' . uniqid(),
            'title' => 'Ujian ASJ Server',
            'duration' => 60,
            'start_at' => Carbon::now()->subMinutes(10),
            'end_at' => Carbon::now()->addHours(2),
            'passing_grade' => 75.00,
            'status' => 'published',
        ]);

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'classroom_id' => $this->student->classroom_id,
            'status' => 'present',
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes(60),
            'status' => 'in_progress',
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = ExamQuestion::create([
                'exam_id' => $exam->id,
                'order' => $i,
                'question_text' => "Soal Jaringan #{$i}: Port default SSH adalah?",
                'weight' => 10.0,
                'difficulty' => 'easy',
            ]);

            $attemptQuestion = ExamAttemptQuestion::create([
                'exam_attempt_id' => $attempt->id,
                'exam_question_id' => $question->id,
                'order' => $i,
            ]);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                $opt = ExamQuestionOption::create([
                    'exam_question_id' => $question->id,
                    'label' => $label,
                    'option_text' => $label === 'A' ? 'Port 22' : 'Port ' . (80 + $index),
                    'is_correct' => $label === 'A',
                    'order' => $index + 1,
                ]);

                ExamAttemptQuestionOption::create([
                    'exam_attempt_question_id' => $attemptQuestion->id,
                    'exam_question_option_id' => $opt->id,
                    'display_label' => $label,
                    'order' => $index + 1,
                ]);
            }
        }

        return $attempt;
    }

    public function test_student_can_view_exam_room_with_palette_and_nav_components(): void
    {
        $attempt = $this->createAttemptWithQuestions(4);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // Header and Timer
        $response->assertSee('Ruang Pengerjaan Asesmen');
        $response->assertSee('SISA WAKTU');
        $response->assertSee('timerDisplay');

        // Navigation and Palette
        $response->assertSee('NAVIGASI NOMOR SOAL');
        $response->assertSee('paletteGrid');
        $response->assertSee('palette-btn-1');
        $response->assertSee('palette-btn-2');
        $response->assertSee('palette-btn-3');
        $response->assertSee('palette-btn-4');

        // Action Buttons
        $response->assertSee('Soal Sebelumnya');
        $response->assertSee('Soal Berikutnya');
        $response->assertSee('Ragu-ragu');
        $response->assertSee('Selesaikan Ujian');

        // Modal
        $response->assertSee('confirmFinishModal');
        $response->assertSee('Konfirmasi Selesai Ujian');
    }

    public function test_exam_room_displays_proper_summary_counters(): void
    {
        $attempt = $this->createAttemptWithQuestions(3);
        $questions = $attempt->attemptQuestions()->orderBy('order')->get();

        // Soal 1: Terjawab
        $opt = $questions[0]->options()->first()->questionOption;
        $questions[0]->update([
            'selected_exam_question_option_id' => $opt->id,
            'is_doubtful' => false,
            'answered_at' => Carbon::now(),
        ]);

        // Soal 2: Ragu-ragu
        $questions[1]->update([
            'is_doubtful' => true,
        ]);

        // Soal 3: Belum dijawab (default)

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // Pastikan variabel counter di view terisi dengan benar
        $response->assertViewHas('totalQuestions', 3);
        $response->assertViewHas('answeredCount', 1);
        $response->assertViewHas('doubtfulCount', 1);
        $response->assertViewHas('unansweredCount', 1);
    }

    public function test_exam_room_renders_option_choices_without_answer_keys(): void
    {
        $attempt = $this->createAttemptWithQuestions(2);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.attempts.show', $attempt));

        $response->assertOk();

        // Opsi pilihan terlihat
        $response->assertSee('Port 22');

        // Kunci jawaban tidak boleh muncul di HTML
        $response->assertDontSee('is_correct');
        $response->assertDontSee('isCorrect');
        $response->assertDontSee('Kunci Jawaban');
    }

    public function test_preparation_page_links_to_exam_room(): void
    {
        $attempt = $this->createAttemptWithQuestions(3);

        $response = $this->actingAs($this->studentUser)
            ->get(route('student.exams.show', $attempt->exam));

        $response->assertOk();
        $response->assertSee('Ruang Persiapan Ujian');
        $response->assertSee('Sesi Attempt Anda Sedang Berlangsung');
        $response->assertSee(route('student.attempts.show', $attempt));
    }
}
