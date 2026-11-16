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
     * Force-finish an in-progress attempt administratively.
     */
    public function forceFinish(Request $request, Exam $exam, ExamAttempt $attempt): JsonResponse
    {
        $this->authorizeExamAccess($request, $exam);

        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt tidak terkait dengan ujian ini.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'reason.required' => 'Alasan tindakan administratif wajib diisi.',
            'reason.min' => 'Alasan tindakan minimal 5 karakter.',
        ]);

        if (! $attempt->isInProgress()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya attempt yang sedang berjalan (in-progress) yang dapat difinalisasi paksa.',
            ], 422);
        }

        \DB::transaction(function () use ($attempt, $exam, $validated, $request) {
            $totalAnswered = $attempt->attemptQuestions()->whereNotNull('selected_exam_question_option_id')->count();

            $attempt->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'total_answered' => $totalAnswered,
            ]);

            \App\Models\ExamParticipant::where('exam_id', $exam->id)
                ->where('student_id', $attempt->student_id)
                ->update(['status' => 'completed']);

            \App\Models\AuditLog::record(
                action: 'admin_finalize',
                reason: $validated['reason'],
                auditable: $attempt,
                details: [
                    'student_id' => $attempt->student_id,
                    'student_name' => $attempt->student?->name,
                    'exam_id' => $exam->id,
                    'exam_title' => $exam->title,
                    'total_answered' => $totalAnswered,
                    'previous_status' => 'in_progress',
                ],
                user: $request->user()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian siswa berhasil difinalisasi secara administratif.',
            'status' => 'completed',
        ]);
    }

    /**
     * Authorize a retake for an exam participant.
     */
    public function authorizeRetake(Request $request, Exam $exam, \App\Models\ExamParticipant $participant): JsonResponse
    {
        $this->authorizeExamAccess($request, $exam);

        if ($participant->exam_id !== $exam->id) {
            abort(404, 'Peserta tidak terkait dengan ujian ini.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'reason.required' => 'Alasan pemberian izin retake wajib diisi.',
            'reason.min' => 'Alasan tindakan minimal 5 karakter.',
        ]);

        \DB::transaction(function () use ($participant, $exam, $validated, $request) {
            $prevStatus = $participant->status;

            $participant->update([
                'can_retake' => true,
                'retake_reason' => $validated['reason'],
                'retake_authorized_by' => $request->user()->id,
                'retake_authorized_at' => Carbon::now(),
                'status' => 'assigned',
            ]);

            \App\Models\AuditLog::record(
                action: 'retake_authorized',
                reason: $validated['reason'],
                auditable: $participant,
                details: [
                    'student_id' => $participant->student_id,
                    'student_name' => $participant->student?->name,
                    'exam_id' => $exam->id,
                    'exam_title' => $exam->title,
                    'previous_status' => $prevStatus,
                ],
                user: $request->user()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Izin ujian ulang (retake) berhasil diberikan kepada siswa.',
        ]);
    }

    /**
     * Get audit logs related to this exam.
     */
    public function auditLogs(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeExamAccess($request, $exam);

        $attemptIds = $exam->attempts()->pluck('id');
        $participantIds = $exam->participants()->pluck('id');

        $logs = \App\Models\AuditLog::with('user')
            ->where(function ($q) use ($exam, $attemptIds, $participantIds) {
                $q->where(function ($sub) use ($attemptIds) {
                    $sub->where('auditable_type', ExamAttempt::class)
                        ->whereIn('auditable_id', $attemptIds);
                })
                ->orWhere(function ($sub) use ($participantIds) {
                    $sub->where('auditable_type', \App\Models\ExamParticipant::class)
                        ->whereIn('auditable_id', $participantIds);
                })
                ->orWhere(function ($sub) use ($exam) {
                    $sub->where('details->exam_id', $exam->id);
                });
            })
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                $actionLabel = match ($log->action) {
                    'admin_finalize' => 'Finalisasi Administratif',
                    'retake_authorized' => 'Izin Ujian Ulang (Retake)',
                    default => ucfirst(str_replace('_', ' ', $log->action)),
                };

                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_label' => $actionLabel,
                    'user_name' => $log->user?->name ?? 'System',
                    'reason' => $log->reason,
                    'details' => $log->details,
                    'ip_address' => $log->ip_address ?? '-',
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'logs' => $logs,
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
                'participant_id' => $participant->id,
                'can_retake' => (bool) $participant->can_retake,
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
