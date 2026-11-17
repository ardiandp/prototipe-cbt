<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExamRaportAndPrintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_can_view_results_index_with_populated_grades(): void
    {
        $studentUser = User::where('username', 'siswa')->firstOrFail();

        $response = $this->actingAs($studentUser)->get(route('student.results.index'));

        $response->assertOk();
        $response->assertSee('Hasil Ujian Saya');
        $response->assertSee('Cetak Rapor Nilai Siswa');
        $response->assertSee('EXAM-BD-01');
        $response->assertSee('EXAM-MTK-01');
        $response->assertSee('EXAM-BIN-01');
        $response->assertSee('Tuntas KKM');
    }

    public function test_student_can_generate_and_print_official_raport(): void
    {
        $studentUser = User::where('username', 'siswa')->firstOrFail();
        $student = $studentUser->studentProfile;

        $response = $this->actingAs($studentUser)->get(route('student.results.raport'));

        $response->assertOk();
        $response->assertSee('LAPORAN HASIL ASESMEN SUMATIF PESERTA DIDIK');
        $response->assertSee($student->name);
        $response->assertSee($student->nis);
        $response->assertSee('Basis Data');
        $response->assertSee('Matematika');
        $response->assertSee('Bahasa Indonesia');
        $response->assertSee('RATA-RATA NILAI ASESMEN CBT');
        $response->assertSee('Wali Kelas');
        $response->assertSee('Kepala Satuan Pendidikan');
    }

    public function test_student_can_print_individual_exam_result_sheet(): void
    {
        $studentUser = User::where('username', 'siswa')->firstOrFail();
        $exam = Exam::where('code', 'EXAM-BD-01')->firstOrFail();

        $response = $this->actingAs($studentUser)->get(route('student.results.print', $exam));

        $response->assertOk();
        $response->assertSee('LEMBAR HASIL ASESMEN UJIAN BERBASIS KOMPUTER');
        $response->assertSee($exam->title);
        $response->assertSee('Ahmad Dani');
        $response->assertSee('Nilai Akhir');
        $response->assertSee('Rincian Lembar Jawaban Peserta');
    }

    public function test_teacher_can_print_student_raport_and_attempt_result(): void
    {
        $teacherUser = User::where('username', 'guru')->firstOrFail();
        $student = Student::where('nis', '20261001')->firstOrFail();
        $exam = Exam::where('code', 'EXAM-BD-01')->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->firstOrFail();

        // 1. Teacher can print student comprehensive raport
        $raportRes = $this->actingAs($teacherUser)->get(route('teacher.students.raport', $student));
        $raportRes->assertOk();
        $raportRes->assertSee('LAPORAN HASIL ASESMEN SUMATIF PESERTA DIDIK');
        $raportRes->assertSee($student->name);

        // 2. Teacher can print individual attempt result sheet
        $printRes = $this->actingAs($teacherUser)->get(route('teacher.results.attempts.print', [$exam, $attempt]));
        $printRes->assertOk();
        $printRes->assertSee('LEMBAR HASIL ASESMEN UJIAN BERBASIS KOMPUTER');
        $printRes->assertSee($student->name);
    }

    public function test_admin_can_print_student_raport_and_attempt_result(): void
    {
        $adminUser = User::where('username', 'admin')->firstOrFail();
        $student = Student::where('nis', '20261001')->firstOrFail();
        $exam = Exam::where('code', 'EXAM-MTK-01')->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->firstOrFail();

        // 1. Admin can print student comprehensive raport
        $raportRes = $this->actingAs($adminUser)->get(route('admin.students.raport', $student));
        $raportRes->assertOk();
        $raportRes->assertSee($student->name);

        // 2. Admin can print individual attempt result sheet
        $printRes = $this->actingAs($adminUser)->get(route('admin.results.attempts.print', [$exam, $attempt]));
        $printRes->assertOk();
        $printRes->assertSee('LEMBAR HASIL ASESMEN UJIAN BERBASIS KOMPUTER');
    }
}

