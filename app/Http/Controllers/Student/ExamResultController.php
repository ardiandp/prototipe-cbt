<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamParticipant;
use App\Models\SchoolProfile;
use App\Models\Semester;
use App\Services\ScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamResultController extends Controller
{
    public function __construct(
        protected ScoringService $scoringService
    ) {}

    /**
     * Display a listing of completed exams and scores for the authenticated student.
     */
    public function index(Request $request): View
    {
        $student = $request->user()->studentProfile;
        if (! $student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $participations = ExamParticipant::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.teacher.user', 'exam.academicYear', 'exam.semester'])
            ->get();

        $examIds = $participations->pluck('exam_id');

        $attemptsByExam = ExamAttempt::whereIn('exam_id', $examIds)
            ->where('student_id', $student->id)
            ->orderBy('attempt_number', 'desc')
            ->get()
            ->groupBy('exam_id');

        $history = [];
        foreach ($participations as $participation) {
            $exam = $participation->exam;
            $attempts = $attemptsByExam->get($exam->id, collect());
            $latestAttempt = $attempts->first();

            $history[] = (object) [
                'participation' => $participation,
                'exam' => $exam,
                'latest_attempt' => $latestAttempt,
                'is_published' => (bool) $exam->is_results_published,
            ];
        }

        return view('student.results.index', compact('history', 'student'));
    }

    /**
     * Display detailed score and answer breakdown for an exam attempt if published.
     */
    public function show(Request $request, Exam $exam): View|RedirectResponse
    {
        $student = $request->user()->studentProfile;
        if (! $student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        // Verify that student is assigned to this exam
        $participation = ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Security / Business rule: Student cannot see results if not published
        if (! $exam->is_results_published) {
            return redirect()->route('student.results.index')
                ->with('error', 'Hasil dan pembahasan ujian ini belum dipublikasikan oleh guru/pengawas.');
        }

        // Get student's completed or expired attempt
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['completed', 'expired'])
            ->orderBy('attempt_number', 'desc')
            ->first();

        if (! $attempt) {
            return redirect()->route('student.results.index')
                ->with('warning', 'Anda belum menyelesaikan sesi pengerjaan ujian ini.');
        }

        // Ensure attempt is scored
        $this->scoringService->scoreAttempt($attempt);

        $attempt->load([
            'attemptQuestions.question.options',
            'attemptQuestions.selectedOption',
            'attemptQuestions.options.questionOption',
        ]);

        return view('student.results.show', compact('exam', 'attempt', 'student'));
    }

    /**
     * Display printable comprehensive report card (Rapor Asesmen / Transkrip Hasil Ujian) for student.
     */
    public function printRaport(Request $request): View
    {
        $student = $request->user()->studentProfile;
        if (! $student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $student->load(['classroom.department', 'classroom.academicYear', 'user']);

        $participations = ExamParticipant::where('student_id', $student->id)
            ->with(['exam.subject', 'exam.teacher.user', 'exam.academicYear', 'exam.semester'])
            ->get();

        $examIds = $participations->pluck('exam_id');

        $attempts = ExamAttempt::whereIn('exam_id', $examIds)
            ->where('student_id', $student->id)
            ->whereIn('status', ['completed', 'expired'])
            ->orderBy('attempt_number', 'desc')
            ->get()
            ->groupBy('exam_id');

        $grades = [];
        $scores = [];
        $passedCount = 0;

        foreach ($participations as $participation) {
            $exam = $participation->exam;
            // Only include in raport if results are published
            if (! $exam->is_results_published) {
                continue;
            }

            $attempt = $attempts->get($exam->id, collect())->first();
            if (! $attempt) {
                continue;
            }

            // Ensure attempt is scored
            $this->scoringService->scoreAttempt($attempt);

            $score = (float) $attempt->score;
            $scores[] = $score;
            $isPassed = $score >= (float) $exam->passing_grade;
            if ($isPassed) {
                $passedCount++;
            }

            // Determine predicate
            $predicate = match (true) {
                $score >= 90 => 'A (Sangat Baik)',
                $score >= 80 => 'B (Baik)',
                $score >= 75 => 'C (Cukup)',
                default => 'D (Kurang)',
            };

            $grades[] = (object) [
                'exam' => $exam,
                'attempt' => $attempt,
                'score' => $score,
                'passing_grade' => (float) $exam->passing_grade,
                'is_passed' => $isPassed,
                'predicate' => $predicate,
            ];
        }

        $totalExams = count($grades);
        $avgScore = $totalExams > 0 ? round(array_sum($scores) / $totalExams, 2) : 0;
        $maxScore = $totalExams > 0 ? max($scores) : 0;
        $minScore = $totalExams > 0 ? min($scores) : 0;

        $overallPredicate = match (true) {
            $avgScore >= 90 => 'A (Sangat Baik)',
            $avgScore >= 80 => 'B (Baik)',
            $avgScore >= 75 => 'C (Cukup)',
            default => 'D (Kurang)',
        };

        $school = SchoolProfile::current();
        $academicYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::first();
        $semester = Semester::where('is_active', true)->first() ?? Semester::first();

        return view('student.results.raport', compact(
            'student',
            'grades',
            'totalExams',
            'avgScore',
            'maxScore',
            'minScore',
            'passedCount',
            'overallPredicate',
            'school',
            'academicYear',
            'semester'
        ));
    }

    /**
     * Display printable individual exam result and answer sheet (Lembar Hasil Ujian Siswa).
     */
    public function printReport(Request $request, Exam $exam): View|RedirectResponse
    {
        $student = $request->user()->studentProfile;
        if (! $student) {
            abort(403, 'Profil siswa tidak ditemukan.');
        }

        $participation = ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if (! $exam->is_results_published) {
            return redirect()->route('student.results.index')
                ->with('error', 'Hasil ujian ini belum dipublikasikan oleh guru/pengawas.');
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['completed', 'expired'])
            ->orderBy('attempt_number', 'desc')
            ->first();

        if (! $attempt) {
            return redirect()->route('student.results.index')
                ->with('warning', 'Anda belum menyelesaikan sesi pengerjaan ujian ini.');
        }

        $this->scoringService->scoreAttempt($attempt);

        $attempt->load([
            'attemptQuestions.question.options',
            'attemptQuestions.selectedOption',
            'attemptQuestions.options.questionOption',
        ]);

        $student->load(['classroom.department']);
        $school = SchoolProfile::current();

        return view('student.results.print', compact('exam', 'attempt', 'student', 'school'));
    }
}
