<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamParticipant;
use App\Models\SchoolProfile;
use App\Models\Subject;
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
     * Display a listing of exams with result summaries.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Exam::with(['subject', 'teacher.user', 'academicYear', 'semester'])
            ->withCount('participants');

        // Teacher only sees exams they created (unless admin/superadmin)
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            $query->where('teacher_id', $user->teacherProfile->id);
        }

        // Search by keyword
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by subject
        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        // Filter by publication status
        if ($request->has('published') && $request->input('published') !== '') {
            $query->where('is_results_published', (bool) $request->input('published'));
        }

        $exams = $query->orderBy('start_at', 'desc')->paginate(12)->withQueryString();

        // Calculate score summaries for each exam
        foreach ($exams as $exam) {
            $completedAttempts = ExamAttempt::where('exam_id', $exam->id)
                ->whereIn('status', ['completed', 'expired'])
                ->get();

            $exam->completed_attempts_count = $completedAttempts->count();
            $exam->avg_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->avg('score'), 2) : 0;
            $exam->max_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->max('score'), 2) : 0;
            $exam->min_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->min('score'), 2) : 0;
            $exam->passed_count = $completedAttempts->filter(fn ($a) => (float) $a->score >= (float) $exam->passing_grade)->count();
        }

        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher.results.index', compact('exams', 'subjects'));
    }

    /**
     * Display detailed student results and answer sheets for an exam.
     */
    public function show(Request $request, Exam $exam): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak memiliki akses ke hasil ujian ini.');
            }
        }

        // Automatically ensure all completed/expired attempts are scored
        $this->scoringService->scoreExam($exam);

        $exam->load(['subject', 'teacher.user', 'academicYear', 'semester', 'questions']);

        // Fetch participants query
        $participantsQuery = ExamParticipant::where('exam_id', $exam->id)
            ->with(['student.classroom', 'student.user']);

        // Classroom filter
        if ($classroomId = $request->input('classroom_id')) {
            $participantsQuery->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        // Search student name / NIS
        if ($search = $request->input('search')) {
            $participantsQuery->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $participants = $participantsQuery->get();

        // Get all attempts for this exam grouped by student
        $attemptsByStudent = ExamAttempt::where('exam_id', $exam->id)
            ->with(['student.classroom', 'student.user'])
            ->orderBy('attempt_number', 'desc')
            ->get()
            ->groupBy('student_id');

        // Build result rows
        $results = [];
        $scores = [];
        $passedCount = 0;
        $failedCount = 0;

        foreach ($participants as $participant) {
            $student = $participant->student;
            $studentAttempts = $attemptsByStudent->get($student->id, collect());
            $latestAttempt = $studentAttempts->first();

            $statusKkm = 'not_taken';
            if ($latestAttempt && in_array($latestAttempt->status, ['completed', 'expired'])) {
                $scores[] = (float) $latestAttempt->score;
                if ((float) $latestAttempt->score >= (float) $exam->passing_grade) {
                    $statusKkm = 'passed';
                    $passedCount++;
                } else {
                    $statusKkm = 'failed';
                    $failedCount++;
                }
            } elseif ($latestAttempt && $latestAttempt->isInProgress()) {
                $statusKkm = 'in_progress';
            }

            // Status filter
            $statusFilter = $request->input('status');
            if ($statusFilter && $statusFilter !== $statusKkm) {
                continue;
            }

            $results[] = (object) [
                'participant' => $participant,
                'student' => $student,
                'latest_attempt' => $latestAttempt,
                'status_kkm' => $statusKkm,
            ];
        }

        $totalCompleted = count($scores);
        $summary = [
            'total_participants' => $participants->count(),
            'completed_participants' => $totalCompleted,
            'avg_score' => $totalCompleted > 0 ? round(array_sum($scores) / $totalCompleted, 2) : 0,
            'max_score' => $totalCompleted > 0 ? round(max($scores), 2) : 0,
            'min_score' => $totalCompleted > 0 ? round(min($scores), 2) : 0,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'pass_percentage' => $totalCompleted > 0 ? round(($passedCount / $totalCompleted) * 100, 1) : 0,
        ];

        // Fetch classrooms for filter dropdown
        $classrooms = Classroom::orderBy('name')->get();

        return view('teacher.results.show', compact('exam', 'results', 'summary', 'classrooms'));
    }

    /**
     * Publish results to students.
     */
    public function publishResults(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak mempublikasikan hasil ujian ini.');
            }
        }

        // Ensure all attempts are scored
        $this->scoringService->scoreExam($exam);

        $exam->update(['is_results_published' => true]);

        AuditLog::record(
            'publish_results',
            $request->input('reason', "Publikasi hasil ujian {$exam->title} kepada siswa."),
            $exam,
            ['exam_id' => $exam->id, 'exam_title' => $exam->title]
        );

        return back()->with('success', 'Hasil ujian berhasil dipublikasikan! Siswa kini dapat melihat skor dan lembar jawaban.');
    }

    /**
     * Unpublish / hide results from students.
     */
    public function unpublishResults(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak menyembunyikan hasil ujian ini.');
            }
        }

        $exam->update(['is_results_published' => false]);

        AuditLog::record(
            'unpublish_results',
            $request->input('reason', "Penarikan publikasi hasil ujian {$exam->title}."),
            $exam,
            ['exam_id' => $exam->id, 'exam_title' => $exam->title]
        );

        return back()->with('success', 'Publikasi hasil ujian berhasil ditarik. Nilai sekarang disembunyikan dari siswa.');
    }

    /**
     * View detailed student attempt answer sheet.
     */
    public function attemptDetail(Request $request, Exam $exam, ExamAttempt $attempt): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak mengakses rincian lembar jawaban ini.');
            }
        }

        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt tidak sesuai dengan ujian.');
        }

        // Ensure attempt is scored
        $this->scoringService->scoreAttempt($attempt);

        $attempt->load([
            'student.classroom',
            'student.user',
            'attemptQuestions.question.options',
            'attemptQuestions.selectedOption',
            'attemptQuestions.options.questionOption',
        ]);

        return view('teacher.results.attempt_detail', compact('exam', 'attempt'));
    }

    /**
     * Display printable individual exam result and answer sheet for a student attempt.
     */
    public function printAttemptResult(Request $request, Exam $exam, ExamAttempt $attempt): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak mencetak lembar hasil siswa ini.');
            }
        }

        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt tidak sesuai dengan ujian.');
        }

        // Ensure attempt is scored
        $this->scoringService->scoreAttempt($attempt);

        $attempt->load([
            'student.classroom.department',
            'attemptQuestions.question.options',
            'attemptQuestions.selectedOption',
            'attemptQuestions.options.questionOption',
        ]);

        $student = $attempt->student;
        $school = SchoolProfile::current();

        return view('student.results.print', compact('exam', 'attempt', 'student', 'school'));
    }
}
