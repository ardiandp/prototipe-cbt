<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamMonitoringController extends Controller
{
    /**
     * Display a listing of exams available for monitoring.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Exam::with(['subject', 'teacher.user', 'academicYear', 'semester'])
            ->withCount('participants');

        // Teacher only sees exams they created
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAdmin() && ! $user->isSuperAdmin()) {
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

        // Filter by status (draft, published, cancelled)
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $exams = $query->orderBy('start_at', 'desc')->paginate(10)->withQueryString();

        // Calculate participant counts per status for each exam
        foreach ($exams as $exam) {
            $attempts = ExamAttempt::where('exam_id', $exam->id)->get();
            $exam->in_progress_count = $attempts->filter(fn ($a) => $a->isInProgress() && ! $a->isExpired())->count();
            $exam->completed_count = $attempts->where('status', 'completed')->count();
            $exam->expired_count = $attempts->filter(fn ($a) => $a->status === 'expired' || ($a->isInProgress() && $a->isExpired()))->count();
            $startedStudentIds = $attempts->pluck('student_id')->unique();
            $exam->not_started_count = max(0, $exam->participants_count - $startedStudentIds->count());
        }

        $subjectsQuery = Subject::active();
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAdmin() && ! $user->isSuperAdmin()) {
            $subjectsQuery->whereIn('id', $user->teacherProfile->subjects()->pluck('subjects.id'));
        }
        $subjects = $subjectsQuery->orderBy('name')->get();

        return view('teacher.monitoring.index', compact('exams', 'subjects'));
    }

    /**
     * Display live monitoring dashboard for a specific exam.
     */
    public function show(Request $request, Exam $exam): View
    {
        $this->authorizeExamAccess($request, $exam);

        $exam->load([
            'subject',
            'teacher.user',
            'academicYear',
            'semester',
            'questions',
            'participants.student.classroom',
            'participants.student.user',
        ]);

        $data = $this->buildMonitoringData($exam);

        return view('teacher.monitoring.show', [
            'exam' => $exam,
            'summary' => $data['summary'],
            'participants' => $data['participants'],
        ]);
    }

    /**
     * Polling endpoint returning live JSON monitoring data.
     */
    public function liveData(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeExamAccess($request, $exam);

        $exam->load([
            'questions',
            'participants.student.classroom',
            'participants.student.user',
        ]);

        $data = $this->buildMonitoringData($exam);

        return response()->json([
            'success' => true,
            'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'summary' => $data['summary'],
            'participants' => $data['participants'],
        ]);
    }

    /**
     * Return detailed breakdown of a student attempt.
     */
    public function attemptDetail(Request $request, Exam $exam, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeExamAccess($request, $exam);

        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt tidak terkait dengan ujian ini.');
        }

        $attempt->load([
            'student.classroom',
            'student.user',
            'attemptQuestions.question',
            'attemptQuestions.options',
            'attemptQuestions.selectedOption',
        ]);

        $questionsDetail = $attempt->attemptQuestions->map(function ($aq) {
            return [
                'order' => $aq->order,
                'question_text' => $aq->question?->question_text ?? 'Soal #' . $aq->order,
                'is_answered' => ! is_null($aq->selected_exam_question_option_id),
                'selected_option' => $aq->getSelectedDisplayLabel() ?? '-',
                'is_doubtful' => (bool) $aq->is_doubtful,
                'answered_at' => $aq->answered_at ? $aq->answered_at->format('H:i:s') : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $attempt->student?->name,
                'nis' => $attempt->student?->nis,
                'classroom' => $attempt->student?->classroom?->name ?? '-',
            ],
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at ? $attempt->started_at->format('d/m/Y H:i:s') : '-',
                'expires_at' => $attempt->expires_at ? $attempt->expires_at->format('d/m/Y H:i:s') : '-',
                'completed_at' => $attempt->completed_at ? $attempt->completed_at->format('d/m/Y H:i:s') : '-',
                'total_answered' => $attempt->total_answered,
                'total_questions' => $questionsDetail->count(),
                'ip_address' => $attempt->ip_address ?? '-',
                'user_agent' => $attempt->user_agent ?? '-',
            ],
            'questions' => $questionsDetail,
        ]);
    }

    /**
     * Authorize user access to the exam.
     */
    private function authorizeExamAccess(Request $request, Exam $exam): void
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        if ($user->isTeacher() && $user->teacherProfile) {
            if ($exam->teacher_id === $user->teacherProfile->id) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki hak akses memonitor ujian ini.');
    }

    /**
     * Build unified monitoring data array for both View and JSON API.
     */
    private function buildMonitoringData(Exam $exam): array
    {
        $totalQuestions = $exam->questions()->count() ?: $exam->total_questions;
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->with(['attemptQuestions'])
            ->get()
            ->keyBy('student_id');

        $participantsData = [];
        $now = Carbon::now();

        $countAssigned = 0;
        $countNotStarted = 0;
        $countInProgress = 0;
        $countCompleted = 0;
        $countExpired = 0;

        foreach ($exam->participants as $participant) {
            $countAssigned++;
            $student = $participant->student;
            $attempt = $attempts->get($participant->student_id);

            $statusKey = 'not_started';
            $statusLabel = 'Belum Mulai';
            $statusBadge = 'secondary';
            $answeredCount = 0;
            $doubtfulCount = 0;
            $progressPercent = 0;
            $startedAt = null;
            $expiresAt = null;
            $completedAt = null;
            $lastActivity = null;
            $remainingSeconds = 0;

            if ($attempt) {
                $startedAt = $attempt->started_at ? $attempt->started_at->format('H:i:s') : null;
                $expiresAt = $attempt->expires_at ? $attempt->expires_at->format('H:i:s') : null;
                $completedAt = $attempt->completed_at ? $attempt->completed_at->format('H:i:s') : null;

                $answeredCount = $attempt->attemptQuestions->whereNotNull('selected_exam_question_option_id')->count();
                $doubtfulCount = $attempt->attemptQuestions->where('is_doubtful', true)->count();
                $progressPercent = $totalQuestions > 0 ? (int) round(($answeredCount / $totalQuestions) * 100) : 0;

                // Find latest activity
                $latestAnswer = $attempt->attemptQuestions->whereNotNull('answered_at')->sortByDesc('answered_at')->first();
                $lastActivity = $latestAnswer?->answered_at ? $latestAnswer->answered_at->format('H:i:s') : ($attempt->started_at ? $attempt->started_at->format('H:i:s') : '-');

                if ($attempt->status === 'completed') {
                    $statusKey = 'completed';
                    $statusLabel = 'Selesai';
                    $statusBadge = 'success';
                    $countCompleted++;
                } elseif ($attempt->status === 'expired' || ($attempt->isInProgress() && $attempt->isExpired())) {
                    $statusKey = 'expired';
                    $statusLabel = 'Waktu Habis';
                    $statusBadge = 'danger';
                    $countExpired++;
                } else {
                    $statusKey = 'in_progress';
                    $statusLabel = 'Mengerjakan';
                    $statusBadge = 'warning';
                    $remainingSeconds = max(0, (int) $now->diffInSeconds($attempt->expires_at, false));
                    $countInProgress++;
                }
            } else {
                $countNotStarted++;
            }

            $participantsData[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'classroom' => $student->classroom?->name ?? '-',
                'status' => $statusKey,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'answered_count' => $answeredCount,
                'doubtful_count' => $doubtfulCount,
                'unanswered_count' => max(0, $totalQuestions - $answeredCount),
                'total_questions' => $totalQuestions,
                'progress_percent' => $progressPercent,
                'started_at' => $startedAt ?? '-',
                'expires_at' => $expiresAt ?? '-',
                'completed_at' => $completedAt ?? '-',
                'remaining_seconds' => $remainingSeconds,
                'remaining_formatted' => $this->formatSeconds($remainingSeconds),
                'last_activity' => $lastActivity ?? '-',
                'attempt_id' => $attempt?->id,
            ];
        }

        return [
            'summary' => [
                'total_assigned' => $countAssigned,
                'not_started' => $countNotStarted,
                'in_progress' => $countInProgress,
                'completed' => $countCompleted,
                'expired' => $countExpired,
            ],
            'participants' => $participantsData,
        ];
    }

    /**
     * Format seconds into HH:MM:SS.
     */
    private function formatSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return '00:00:00';
        }

        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
