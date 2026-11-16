<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\ExamParticipant;
use App\Models\Subject;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamReportController extends Controller
{
    public function __construct(
        protected ScoringService $scoringService
    ) {}

    /**
     * Display the main reports hub: listing of exams with comprehensive report summaries.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Exam::with(['subject', 'teacher.user', 'academicYear', 'semester'])
            ->withCount('participants');

        // Teacher only sees their own exams unless admin/superadmin
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            $query->where('teacher_id', $user->teacherProfile->id);
        }

        // Search by title or code
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

        // Filter by academic year
        if ($academicYearId = $request->input('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        $exams = $query->orderBy('start_at', 'desc')->paginate(12)->withQueryString();

        $allCompletedScores = [];
        $totalCompletedAttempts = 0;
        $totalPassedStudents = 0;

        foreach ($exams as $exam) {
            $completedAttempts = ExamAttempt::where('exam_id', $exam->id)
                ->whereIn('status', ['completed', 'expired'])
                ->get();

            $exam->completed_count = $completedAttempts->count();
            $exam->avg_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->avg('score'), 2) : 0;
            $exam->max_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->max('score'), 2) : 0;
            $exam->min_score = $completedAttempts->isNotEmpty() ? round($completedAttempts->min('score'), 2) : 0;
            
            $passed = $completedAttempts->filter(fn ($a) => (float) $a->score >= (float) $exam->passing_grade)->count();
            $exam->passed_count = $passed;
            $exam->pass_percentage = $exam->completed_count > 0 ? round(($passed / $exam->completed_count) * 100, 1) : 0;

            foreach ($completedAttempts as $att) {
                $allCompletedScores[] = (float) $att->score;
                $totalCompletedAttempts++;
                if ((float) $att->score >= (float) $exam->passing_grade) {
                    $totalPassedStudents++;
                }
            }
        }

        $overallStats = [
            'total_exams' => $exams->total(),
            'total_attempts' => $totalCompletedAttempts,
            'overall_avg' => count($allCompletedScores) > 0 ? round(array_sum($allCompletedScores) / count($allCompletedScores), 2) : 0,
            'overall_pass_rate' => $totalCompletedAttempts > 0 ? round(($totalPassedStudents / $totalCompletedAttempts) * 100, 1) : 0,
        ];

        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();

        return view('teacher.reports.index', compact('exams', 'overallStats', 'subjects', 'academicYears', 'classrooms'));
    }

    /**
     * Display detailed exam report: exam summary, ranking, score distribution, and class breakdown.
     */
    public function examReport(Request $request, Exam $exam): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak mengakses laporan ujian ini.');
            }
        }

        // Ensure all attempts are scored
        $this->scoringService->scoreExam($exam);

        $exam->load(['subject', 'teacher.user', 'academicYear', 'semester']);

        // Participants query
        $participantsQuery = ExamParticipant::where('exam_id', $exam->id)
            ->with(['student.classroom', 'student.user']);

        // Optional classroom filter
        if ($classroomId = $request->input('classroom_id')) {
            $participantsQuery->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $participants = $participantsQuery->get();
        $participantStudentIds = $participants->pluck('student_id');

        // Fetch completed/expired attempts for these participants
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('student_id', $participantStudentIds)
            ->whereIn('status', ['completed', 'expired'])
            ->with(['student.classroom', 'student.user'])
            ->orderBy('attempt_number', 'desc')
            ->get();

        // Get latest attempt per student
        $latestAttempts = $attempts->groupBy('student_id')->map(function ($studentAttempts) {
            $latest = $studentAttempts->first();
            // Calculate actual working duration in seconds
            $latest->duration_seconds = $latest->started_at && $latest->completed_at
                ? max(0, $latest->started_at->diffInSeconds($latest->completed_at))
                : 0;
            return $latest;
        })->values();

        // Sort for Ranking: Score DESC, Duration ASC, Correct DESC, Student Name ASC
        $sortedAttempts = $latestAttempts->sort(function ($a, $b) {
            if ($a->score != $b->score) {
                return $a->score > $b->score ? -1 : 1;
            }
            if ($a->duration_seconds != $b->duration_seconds) {
                return $a->duration_seconds < $b->duration_seconds ? -1 : 1;
            }
            if ($a->total_correct != $b->total_correct) {
                return $a->total_correct > $b->total_correct ? -1 : 1;
            }
            return strcmp($a->student->name, $b->student->name);
        })->values();

        // Assign Rank with Tied Rank Handling
        $rankings = [];
        $currentRank = 1;
        $prevScore = null;
        $prevDuration = null;

        foreach ($sortedAttempts as $idx => $att) {
            if ($idx > 0 && $att->score == $prevScore && $att->duration_seconds == $prevDuration) {
                $att->rank = $currentRank;
            } else {
                $currentRank = $idx + 1;
                $att->rank = $currentRank;
            }
            $prevScore = $att->score;
            $prevDuration = $att->duration_seconds;
            $rankings[] = $att;
        }

        // Statistical calculations
        $scores = $latestAttempts->pluck('score')->map(fn ($s) => (float) $s)->toArray();
        $totalCompleted = count($scores);
        $totalEnrolled = $participants->count();
        $totalAbsent = max(0, $totalEnrolled - $totalCompleted);

        $avgScore = $totalCompleted > 0 ? round(array_sum($scores) / $totalCompleted, 2) : 0;
        $maxScore = $totalCompleted > 0 ? round(max($scores), 2) : 0;
        $minScore = $totalCompleted > 0 ? round(min($scores), 2) : 0;

        // Median calculation
        $medianScore = 0;
        if ($totalCompleted > 0) {
            $sortedScores = $scores;
            sort($sortedScores);
            $middle = (int) floor($totalCompleted / 2);
            if ($totalCompleted % 2 === 0) {
                $medianScore = round(($sortedScores[$middle - 1] + $sortedScores[$middle]) / 2, 2);
            } else {
                $medianScore = round($sortedScores[$middle], 2);
            }
        }

        // Standard Deviation calculation
        $stdDev = 0;
        if ($totalCompleted > 1) {
            $variance = 0;
            foreach ($scores as $s) {
                $variance += pow($s - $avgScore, 2);
            }
            $stdDev = round(sqrt($variance / $totalCompleted), 2);
        }

        $passedCount = collect($scores)->filter(fn ($s) => $s >= (float) $exam->passing_grade)->count();
        $failedCount = $totalCompleted - $passedCount;
        $passPercentage = $totalCompleted > 0 ? round(($passedCount / $totalCompleted) * 100, 1) : 0;

        // Score Distribution Intervals
        $distribution = [
            'very_low' => [
                'label' => '0 - 49',
                'description' => 'Sangat Kurang',
                'badge' => 'danger',
                'count' => collect($scores)->filter(fn ($s) => $s < 50)->count(),
                'percentage' => 0,
            ],
            'low' => [
                'label' => '50 - 69',
                'description' => 'Kurang',
                'badge' => 'warning',
                'count' => collect($scores)->filter(fn ($s) => $s >= 50 && $s < 70)->count(),
                'percentage' => 0,
            ],
            'medium' => [
                'label' => '70 - 79',
                'description' => 'Cukup',
                'badge' => 'info',
                'count' => collect($scores)->filter(fn ($s) => $s >= 70 && $s < 80)->count(),
                'percentage' => 0,
            ],
            'high' => [
                'label' => '80 - 89',
                'description' => 'Baik',
                'badge' => 'primary',
                'count' => collect($scores)->filter(fn ($s) => $s >= 80 && $s < 90)->count(),
                'percentage' => 0,
            ],
            'very_high' => [
                'label' => '90 - 100',
                'description' => 'Sangat Baik',
                'badge' => 'success',
                'count' => collect($scores)->filter(fn ($s) => $s >= 90)->count(),
                'percentage' => 0,
            ],
        ];

        foreach ($distribution as $key => $dist) {
            $distribution[$key]['percentage'] = $totalCompleted > 0
                ? round(($dist['count'] / $totalCompleted) * 100, 1)
                : 0;
        }

        // Class-by-Class Breakdown
        $classBreakdown = [];
        $participantsByClass = $participants->groupBy(fn ($p) => $p->student->classroom_id ?? 0);
        $attemptsByStudentId = $latestAttempts->keyBy('student_id');

        foreach ($participantsByClass as $clsId => $classParticipants) {
            $clsName = $classParticipants->first()->student->classroom->name ?? 'Tanpa Rombel';
            $clsScores = [];
            $clsPassed = 0;

            foreach ($classParticipants as $cp) {
                if ($att = $attemptsByStudentId->get($cp->student_id)) {
                    $clsScores[] = (float) $att->score;
                    if ((float) $att->score >= (float) $exam->passing_grade) {
                        $clsPassed++;
                    }
                }
            }

            $clsCompleted = count($clsScores);
            $classBreakdown[] = (object) [
                'classroom_id' => $clsId,
                'classroom_name' => $clsName,
                'total_enrolled' => $classParticipants->count(),
                'total_completed' => $clsCompleted,
                'avg_score' => $clsCompleted > 0 ? round(array_sum($clsScores) / $clsCompleted, 2) : 0,
                'max_score' => $clsCompleted > 0 ? round(max($clsScores), 2) : 0,
                'min_score' => $clsCompleted > 0 ? round(min($clsScores), 2) : 0,
                'passed_count' => $clsPassed,
                'pass_percentage' => $clsCompleted > 0 ? round(($clsPassed / $clsCompleted) * 100, 1) : 0,
            ];
        }

        $summary = [
            'total_enrolled' => $totalEnrolled,
            'total_completed' => $totalCompleted,
            'total_absent' => $totalAbsent,
            'completion_rate' => $totalEnrolled > 0 ? round(($totalCompleted / $totalEnrolled) * 100, 1) : 0,
            'avg_score' => $avgScore,
            'max_score' => $maxScore,
            'min_score' => $minScore,
            'median_score' => $medianScore,
            'std_dev' => $stdDev,
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'pass_percentage' => $passPercentage,
        ];

        $classrooms = Classroom::orderBy('name')->get();

        return view('teacher.reports.exam', compact(
            'exam',
            'summary',
            'rankings',
            'distribution',
            'classBreakdown',
            'classrooms'
        ));
    }

    /**
     * Display class summary report across exams.
     */
    public function classReport(Request $request, Classroom $classroom): View
    {
        $classroom->load(['department', 'academicYear', 'students']);

        $studentIds = $classroom->students->pluck('id');

        // Fetch all participations for students in this class
        $participations = ExamParticipant::whereIn('student_id', $studentIds)
            ->with(['exam.subject', 'exam.teacher.user'])
            ->get();

        $examIds = $participations->pluck('exam_id')->unique();
        $exams = Exam::whereIn('id', $examIds)
            ->with(['subject', 'teacher.user'])
            ->get();

        $examSummaries = [];
        foreach ($exams as $exam) {
            $attempts = ExamAttempt::where('exam_id', $exam->id)
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', ['completed', 'expired'])
                ->get();

            $scores = $attempts->pluck('score')->map(fn ($s) => (float) $s)->toArray();
            $completedCount = count($scores);
            $passedCount = collect($scores)->filter(fn ($s) => $s >= (float) $exam->passing_grade)->count();

            $examSummaries[] = (object) [
                'exam' => $exam,
                'total_students' => $participations->where('exam_id', $exam->id)->count(),
                'completed_count' => $completedCount,
                'avg_score' => $completedCount > 0 ? round(array_sum($scores) / $completedCount, 2) : 0,
                'max_score' => $completedCount > 0 ? round(max($scores), 2) : 0,
                'min_score' => $completedCount > 0 ? round(min($scores), 2) : 0,
                'passed_count' => $passedCount,
                'pass_percentage' => $completedCount > 0 ? round(($passedCount / $completedCount) * 100, 1) : 0,
            ];
        }

        return view('teacher.reports.classroom', compact('classroom', 'examSummaries'));
    }

    /**
     * Display item analysis for an exam: correct/incorrect %, option A-E distribution, and participant counts per question.
     */
    public function itemAnalysis(Request $request, Exam $exam): View
    {
        $user = $request->user();

        // Authorization check
        if ($user->isTeacher() && $user->teacherProfile && ! $user->isAnyAdmin()) {
            if ($exam->teacher_id !== $user->teacherProfile->id) {
                abort(403, 'Anda tidak berhak mengakses analisis butir soal ujian ini.');
            }
        }

        // Ensure all attempts are scored
        $this->scoringService->scoreExam($exam);

        $exam->load(['subject', 'teacher.user', 'academicYear', 'semester', 'questions.options']);

        // Participants query with optional classroom filter
        $participantsQuery = ExamParticipant::where('exam_id', $exam->id)
            ->with(['student.classroom', 'student.user']);

        if ($classroomId = $request->input('classroom_id')) {
            $participantsQuery->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        $participants = $participantsQuery->get();
        $participantStudentIds = $participants->pluck('student_id');

        // Fetch latest completed/expired attempt per student
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('student_id', $participantStudentIds)
            ->whereIn('status', ['completed', 'expired'])
            ->orderBy('attempt_number', 'desc')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($studentAttempts) => $studentAttempts->first());

        $attemptIds = $attempts->pluck('id');
        $totalParticipants = $attempts->count();

        // Fetch all attempt questions grouped by exam_question_id
        $attemptQuestions = ExamAttemptQuestion::whereIn('exam_attempt_id', $attemptIds)
            ->get()
            ->groupBy('exam_question_id');

        $analysis = [];
        $totalCorrectSum = 0;
        $questionCount = $exam->questions->count();

        foreach ($exam->questions->sortBy('order') as $question) {
            $aqs = $attemptQuestions->get($question->id, collect());
            $answeredAqs = $aqs->filter(fn ($aq) => ! is_null($aq->selected_exam_question_option_id));

            $answeredCount = $answeredAqs->count();
            $correctCount = $aqs->where('is_correct', true)->count();
            $incorrectCount = max(0, $answeredCount - $correctCount);
            $unansweredCount = max(0, $totalParticipants - $answeredCount);

            $correctPercentage = $totalParticipants > 0 ? round(($correctCount / $totalParticipants) * 100, 1) : 0;
            $incorrectPercentage = $totalParticipants > 0 ? round(($incorrectCount / $totalParticipants) * 100, 1) : 0;
            $unansweredPercentage = $totalParticipants > 0 ? round(($unansweredCount / $totalParticipants) * 100, 1) : 0;

            // Difficulty category based on accuracy
            $difficultyLabel = 'Sedang';
            $difficultyBadge = 'primary';
            if ($correctPercentage >= 70) {
                $difficultyLabel = 'Mudah';
                $difficultyBadge = 'success';
            } elseif ($correctPercentage < 30) {
                $difficultyLabel = 'Sukar';
                $difficultyBadge = 'danger';
            }

            // Distractor / Option distribution
            $optionsDistribution = [];
            foreach ($question->options->sortBy('order') as $option) {
                $countSelected = $aqs->where('selected_exam_question_option_id', $option->id)->count();
                $pctSelected = $totalParticipants > 0 ? round(($countSelected / $totalParticipants) * 100, 1) : 0;
                $isKey = (bool) $option->is_correct;

                $distractorStatus = 'Pengecoh Kurang Efektif';
                $distractorBadge = 'secondary';
                if ($isKey) {
                    $distractorStatus = 'Kunci Jawaban';
                    $distractorBadge = 'success';
                } elseif ($countSelected > 0) {
                    $distractorStatus = 'Pengecoh Berfungsi';
                    $distractorBadge = 'info';
                }

                $optionsDistribution[] = (object) [
                    'option_id' => $option->id,
                    'label' => $option->label,
                    'option_text' => $option->option_text,
                    'is_correct' => $isKey,
                    'count' => $countSelected,
                    'percentage' => $pctSelected,
                    'distractor_status' => $distractorStatus,
                    'distractor_badge' => $distractorBadge,
                ];
            }

            $analysis[] = (object) [
                'question' => $question,
                'total_participants' => $totalParticipants,
                'answered_count' => $answeredCount,
                'correct_count' => $correctCount,
                'incorrect_count' => $incorrectCount,
                'unanswered_count' => $unansweredCount,
                'correct_percentage' => $correctPercentage,
                'incorrect_percentage' => $incorrectPercentage,
                'unanswered_percentage' => $unansweredPercentage,
                'difficulty_label' => $difficultyLabel,
                'difficulty_badge' => $difficultyBadge,
                'options_distribution' => $optionsDistribution,
            ];

            $totalCorrectSum += $correctPercentage;
        }

        $overallAccuracy = $questionCount > 0 ? round($totalCorrectSum / $questionCount, 1) : 0;

        $easiestQuestion = collect($analysis)->sortByDesc('correct_percentage')->first();
        $hardestQuestion = collect($analysis)->sortBy('correct_percentage')->first();

        $easyCount = collect($analysis)->where('difficulty_label', 'Mudah')->count();
        $mediumCount = collect($analysis)->where('difficulty_label', 'Sedang')->count();
        $hardCount = collect($analysis)->where('difficulty_label', 'Sukar')->count();

        $summary = [
            'total_questions' => $questionCount,
            'total_participants' => $totalParticipants,
            'overall_accuracy' => $overallAccuracy,
            'easiest_question' => $easiestQuestion,
            'hardest_question' => $hardestQuestion,
            'easy_count' => $easyCount,
            'medium_count' => $mediumCount,
            'hard_count' => $hardCount,
        ];

        $classrooms = Classroom::orderBy('name')->get();

        return view('teacher.reports.item_analysis', compact(
            'exam',
            'analysis',
            'summary',
            'classrooms'
        ));
    }
}

