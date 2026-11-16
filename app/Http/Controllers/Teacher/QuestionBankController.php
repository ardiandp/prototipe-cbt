<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuestionCategoryRequest;
use App\Http\Requests\Teacher\UpdateQuestionCategoryRequest;
use App\Models\QuestionCategory;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    /**
     * Display a listing of question banks.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $subjectId = $request->input('subject_id');
        $level = $request->input('level');
        $status = $request->input('status');

        $query = QuestionCategory::with(['subject', 'teacher'])
            ->withCount('questions')
            ->latest();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (! empty($subjectId)) {
            $query->where('subject_id', $subjectId);
        }

        if (! empty($level)) {
            $query->where('level', $level);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $questionBanks = $query->paginate(10)->withQueryString();

        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher.question-banks.index', compact(
            'questionBanks',
            'subjects',
            'search',
            'subjectId',
            'level',
            'status'
        ));
    }

    /**
     * Show the form for creating a new question bank.
     */
    public function create(): View
    {
        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher.question-banks.create', compact('subjects'));
    }

    /**
     * Store a newly created question bank.
     */
    public function store(StoreQuestionCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['teacher_id'] = $request->user()?->teacherProfile?->id;

        $questionBank = QuestionCategory::create($data);

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Bank soal berhasil dibuat. Silakan tambahkan butir soal.');
    }

    /**
     * Display the specified question bank and its questions.
     */
    public function show(QuestionCategory $questionBank, Request $request): View
    {
        $questionBank->load(['subject', 'teacher']);

        $search = $request->input('search');
        $difficulty = $request->input('difficulty');
        $status = $request->input('status');

        $questionsQuery = $questionBank->questions()->with('options')->latest();

        if (! empty($search)) {
            $questionsQuery->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (! empty($difficulty)) {
            $questionsQuery->where('difficulty', $difficulty);
        }

        if (! empty($status)) {
            $questionsQuery->where('status', $status);
        }

        $questions = $questionsQuery->paginate(10)->withQueryString();

        return view('teacher.question-banks.show', compact(
            'questionBank',
            'questions',
            'search',
            'difficulty',
            'status'
        ));
    }

    /**
     * Show the form for editing the question bank.
     */
    public function edit(QuestionCategory $questionBank): View
    {
        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher.question-banks.edit', compact('questionBank', 'subjects'));
    }

    /**
     * Update the specified question bank.
     */
    public function update(UpdateQuestionCategoryRequest $request, QuestionCategory $questionBank): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $questionBank->update($data);

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Informasi bank soal berhasil diperbarui.');
    }

    /**
     * Remove the specified question bank.
     */
    public function destroy(QuestionCategory $questionBank): RedirectResponse
    {
        $questionBank->delete();

        return redirect()->route('teacher.question-banks.index')
            ->with('success', 'Bank soal beserta butir soal terkait berhasil dihapus.');
    }
}
