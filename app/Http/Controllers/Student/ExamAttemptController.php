<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamAttemptQuestionOption;
use App\Models\ExamParticipant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    /**
     * Start a new individual attempt or resume an active one.
     */
    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student) {
            abort(403, 'Profil peserta didik tidak ditemukan.');
        }

        // Verify participant assignment
        $participant = ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $participant) {
            abort(403, 'Anda tidak terdaftar sebagai peserta dalam jadwal ujian ini.');
        }

        // Verify exam is published
        if (! $exam->isPublished()) {
            abort(404, 'Jadwal ujian belum dipublikasikan atau telah dinonaktifkan.');
        }

        // Verify schedule window
        if (! $exam->isOpen()) {
            return redirect()->route('student.exams.show', $exam)
                ->with('error', 'Sesi ujian saat ini sedang ditutup atau belum dibuka.');
        }

        // Check token if required
        if ($exam->token) {
            $tokenInput = strtoupper(trim((string) $request->input('token')));
            if ($tokenInput !== $exam->token) {
                return redirect()->route('student.exams.show', $exam)
                    ->with('error', 'Token masuk ujian yang Anda masukkan tidak valid. Silakan tanyakan kepada pengawas ruangan.');
            }
        }

        // Check existing attempt
        $existingAttempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest('id')
            ->first();

        if ($existingAttempt) {
            // If active: Resume it! (Rule 5: Refresh or re-enter does not reset timer or create new attempt)
            if ($existingAttempt->isActive()) {
                return redirect()->route('student.attempts.show', $existingAttempt)
                    ->with('info', 'Melanjutkan sesi ujian yang sedang berjalan.');
            }

            // If expired
            if ($existingAttempt->isExpired()) {
                if ($existingAttempt->isInProgress()) {
                    $existingAttempt->markAsExpired();
                }

                return redirect()->route('student.exams.show', $exam)
                    ->with('error', 'Batas waktu pengerjaan sesi ujian Anda telah berakhir.');
            }

            // If completed
            if ($existingAttempt->isCompleted()) {
                return redirect()->route('student.exams.show', $exam)
                    ->with('error', 'Anda telah menyelesaikan ujian ini.');
            }
        }

        // Server time is single source of truth
        $startedAt = Carbon::now();
        $expiresAt = $exam->calculateAttemptExpiry($startedAt);

        $attempt = DB::transaction(function () use ($exam, $student, $startedAt, $expiresAt, $request, $participant) {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'attempt_number' => 1,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status' => 'in_progress',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $participant->update(['status' => 'present']);

            // Generate question ordering & option ordering (P6-T02)
            $questions = $exam->questions()->with('options')->get();

            if ($exam->random_question) {
                $questions = $questions->shuffle();
            }

            $labels = ['A', 'B', 'C', 'D', 'E'];

            foreach ($questions->values() as $qIndex => $question) {
                $attemptQuestion = ExamAttemptQuestion::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $question->id,
                    'order' => $qIndex + 1,
                ]);

                $options = $question->options;
                if ($exam->random_answer) {
                    $options = $options->shuffle();
                }

                foreach ($options->values() as $optIndex => $option) {
                    ExamAttemptQuestionOption::create([
                        'exam_attempt_question_id' => $attemptQuestion->id,
                        'exam_question_option_id' => $option->id,
                        'display_label' => $labels[$optIndex] ?? chr(65 + $optIndex),
                        'order' => $optIndex + 1,
                    ]);
                }
            }

            return $attempt;
        });

        return redirect()->route('student.attempts.show', $attempt)
            ->with('success', 'Sesi ujian berhasil dimulai. Selamat mengerjakan!');
    }

    /**
     * Display the attempt examination screen / room.
     */
    public function show(Request $request, ExamAttempt $attempt): View|RedirectResponse
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student || $attempt->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki hak akses ke sesi ujian ini.');
        }

        // Auto-finalize if expired
        if ($attempt->isInProgress() && $attempt->isExpired()) {
            $attempt->markAsExpired();

            return redirect()->route('student.exams.show', $attempt->exam)
                ->with('error', 'Waktu ujian telah habis. Sesi ujian Anda telah otomatis diselesaikan oleh server.');
        }

        $attempt->load([
            'exam.subject',
            'exam.teacher.user',
            'student.user',
            'student.classroom',
            'attemptQuestions.question',
            'attemptQuestions.options.questionOption',
        ]);

        $totalQuestions = $attempt->attemptQuestions->count();
        $doubtfulCount = $attempt->attemptQuestions->where('is_doubtful', true)->count();
        $answeredCount = $attempt->attemptQuestions->whereNotNull('selected_exam_question_option_id')->where('is_doubtful', false)->count();
        $unansweredCount = $attempt->attemptQuestions->whereNull('selected_exam_question_option_id')->where('is_doubtful', false)->count();

        return view('student.attempts.show', compact(
            'attempt',
            'totalQuestions',
            'answeredCount',
            'doubtfulCount',
            'unansweredCount'
        ));
    }

    /**
     * Synchronize client timer with server time and auto-finalize if expired.
     */
    public function syncTime(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student || $attempt->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki hak akses ke sesi ujian ini.');
        }

        $now = Carbon::now();

        // Check if deadline has passed
        if ($attempt->isExpired() || $now->greaterThanOrEqualTo($attempt->expires_at)) {
            if ($attempt->isInProgress()) {
                $attempt->markAsExpired();
            }

            return response()->json([
                'server_time' => $now->format('Y-m-d H:i:s'),
                'server_timestamp' => $now->timestamp,
                'expires_at' => $attempt->expires_at->format('Y-m-d H:i:s'),
                'expires_timestamp' => $attempt->expires_at->timestamp,
                'remaining_seconds' => 0,
                'is_expired' => true,
                'status' => 'expired',
                'redirect_url' => route('student.exams.show', $attempt->exam),
            ]);
        }

        $remaining = max(0, (int) $now->diffInSeconds($attempt->expires_at, false));

        return response()->json([
            'server_time' => $now->format('Y-m-d H:i:s'),
            'server_timestamp' => $now->timestamp,
            'expires_at' => $attempt->expires_at->format('Y-m-d H:i:s'),
            'expires_timestamp' => $attempt->expires_at->timestamp,
            'remaining_seconds' => $remaining,
            'is_expired' => false,
            'status' => $attempt->status,
        ]);
    }

    /**
     * Finalize an expired attempt idempotently.
     */
    public function finalizeExpired(Request $request, ExamAttempt $attempt): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $student = $user->studentProfile;

        if (! $student || $attempt->student_id !== $student->id) {
            abort(403, 'Anda tidak memiliki hak akses ke sesi ujian ini.');
        }

        if ($attempt->isInProgress() && $attempt->isExpired()) {
            $attempt->markAsExpired();
        }

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'status' => $attempt->status,
                'redirect_url' => route('student.exams.show', $attempt->exam),
            ]);
        }

        return redirect()->route('student.exams.show', $attempt->exam)
            ->with('error', 'Waktu ujian telah habis. Sesi ujian Anda telah diselesaikan.');
    }
}

