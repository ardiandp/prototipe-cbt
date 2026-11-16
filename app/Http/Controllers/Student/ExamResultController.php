<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamParticipant;
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
}
