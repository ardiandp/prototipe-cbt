<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamQuestionController extends Controller
{
    /**
     * Display a listing of snapshot questions in this exam.
     */
    public function index(Request $request, Exam $exam): View
    {
        $this->authorizeExamAccess($request, $exam);

        $exam->load(['subject', 'teacher', 'questions.options']);
        $questions = $exam->questions()->with('options')->paginate(15);

        return view('teacher.exams.questions.index', compact('exam', 'questions'));
    }

    /**
     * Show the question selector interface to pick questions from question bank.
     */
    public function create(Request $request, Exam $exam): View
    {
        $this->authorizeExamAccess($request, $exam);

        // Categories for filter (matching exam subject)
        $categories = QuestionCategory::where('subject_id', $exam->subject_id)
            ->active()
            ->orderBy('name')
            ->get();

        // Already assigned question IDs in this exam
        $existingQuestionIds = $exam->questions()->whereNotNull('question_id')->pluck('question_id')->toArray();

        // Query available questions from question bank
        $query = Question::with(['category', 'options'])
            ->whereHas('category', function ($q) use ($exam) {
                $q->where('subject_id', $exam->subject_id);
            })
            ->whereNotIn('id', $existingQuestionIds)
            ->active();

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('question_category_id', $categoryId);
        }

        // Filter by difficulty
        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        // Search in question text or code
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $availableQuestions = $query->orderBy('order')->orderBy('id')->paginate(15)->withQueryString();

        return view('teacher.exams.questions.create', compact('exam', 'categories', 'availableQuestions', 'existingQuestionIds'));
    }

    /**
     * Store selected questions as immutable snapshots into the exam.
     */
    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExamAccess($request, $exam);

        $validated = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['required', 'exists:questions,id'],
        ], [
            'question_ids.required' => 'Silakan pilih minimal satu butir soal dari bank soal.',
            'question_ids.min' => 'Silakan pilih minimal satu butir soal dari bank soal.',
        ]);

        $selectedQuestions = Question::with('options')
            ->whereIn('id', $validated['question_ids'])
            ->get();

        DB::transaction(function () use ($exam, $selectedQuestions) {
            $currentOrder = $exam->questions()->max('order') ?? 0;

            foreach ($selectedQuestions as $q) {
                $currentOrder++;

                $examQuestion = ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_id' => $q->id,
                    'order' => $currentOrder,
                    'code' => $q->code,
                    'question_text' => $q->question_text,
                    'question_image' => $q->question_image,
                    'difficulty' => $q->difficulty,
                    'weight' => $q->weight,
                ]);

                foreach ($q->options as $opt) {
                    ExamQuestionOption::create([
                        'exam_question_id' => $examQuestion->id,
                        'label' => $opt->label,
                        'option_text' => $opt->option_text,
                        'option_image' => $opt->option_image,
                        'is_correct' => $opt->is_correct,
                        'order' => $opt->order,
                    ]);
                }
            }

            // Sync total questions count in exam
            $exam->update([
                'total_questions' => $exam->questions()->count(),
            ]);
        });

        $count = count($validated['question_ids']);

        return redirect()->route('teacher.exams.questions.index', $exam)
            ->with('success', "Sebanyak {$count} butir soal berhasil di-snapshot ke dalam jadwal ujian.");
    }

    /**
     * Remove a snapshot question from the exam.
     */
    public function destroy(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorizeExamAccess($request, $exam);

        if ($question->exam_id !== $exam->id) {
            abort(404);
        }

        DB::transaction(function () use ($exam, $question) {
            $question->delete();

            // Re-sequence remaining question orders
            $remaining = $exam->questions()->orderBy('order')->get();
            foreach ($remaining as $idx => $remQ) {
                $remQ->update(['order' => $idx + 1]);
            }

            $exam->update([
                'total_questions' => $remaining->count(),
            ]);
        });

        return redirect()->route('teacher.exams.questions.index', $exam)
            ->with('success', 'Butir soal berhasil dihapus dari jadwal ujian.');
    }

    /**
     * Authorize that the current user has rights to access/manage this exam.
     */
    private function authorizeExamAccess(Request $request, Exam $exam): void
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isAnyAdmin()) {
            return;
        }

        if ($user->isTeacher() && $exam->teacher_id === $user->teacherProfile?->id) {
            return;
        }

        abort(403, 'Anda tidak memiliki hak akses untuk mengelola soal pada jadwal ujian ini.');
    }
}

