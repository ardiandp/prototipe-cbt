<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuestionRequest;
use App\Http\Requests\Teacher\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    /**
     * Show the form for creating a new question in the specified bank.
     */
    public function create(QuestionCategory $questionBank): View
    {
        return view('teacher.questions.create', compact('questionBank'));
    }

    /**
     * Store a newly created question with multiple-choice options.
     */
    public function store(StoreQuestionRequest $request, QuestionCategory $questionBank): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $questionBank, $request) {
            $question = Question::create([
                'question_category_id' => $questionBank->id,
                'teacher_id' => $request->user()?->teacherProfile?->id,
                'code' => $data['code'] ?? null,
                'question_text' => $data['question_text'],
                'difficulty' => $data['difficulty'],
                'weight' => $data['weight'],
                'status' => $data['status'],
                'order' => $questionBank->questions()->count() + 1,
            ]);

            $correctOption = strtoupper($data['correct_option']);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'option_text' => $data['options'][$label] ?? '',
                    'is_correct' => ($label === $correctOption),
                    'order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Butir soal berhasil ditambahkan ke bank soal.');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(QuestionCategory $questionBank, Question $question): View
    {
        $question->load('options');

        $optionsMap = $question->options->keyBy('label');
        $correctOption = $question->getCorrectAnswerLabel() ?? 'A';

        return view('teacher.questions.edit', compact(
            'questionBank',
            'question',
            'optionsMap',
            'correctOption'
        ));
    }

    /**
     * Update the specified question and its options.
     */
    public function update(UpdateQuestionRequest $request, QuestionCategory $questionBank, Question $question): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'code' => $data['code'] ?? null,
                'question_text' => $data['question_text'],
                'difficulty' => $data['difficulty'],
                'weight' => $data['weight'],
                'status' => $data['status'],
            ]);

            $correctOption = strtoupper($data['correct_option']);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'label' => $label,
                    ],
                    [
                        'option_text' => $data['options'][$label] ?? '',
                        'is_correct' => ($label === $correctOption),
                        'order' => $index + 1,
                    ]
                );
            }
        });

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Butir soal berhasil diperbarui.');
    }

    /**
     * Remove the specified question.
     */
    public function destroy(QuestionCategory $questionBank, Question $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Butir soal berhasil dihapus.');
    }

    /**
     * Duplicate the specified question with all options.
     */
    public function duplicate(QuestionCategory $questionBank, Question $question): RedirectResponse
    {
        DB::transaction(function () use ($questionBank, $question) {
            $question->load('options');

            $newQuestion = $question->replicate();
            $newQuestion->code = $question->code ? $question->code . '-COPY' : null;
            $newQuestion->status = 'draft';
            $newQuestion->order = $questionBank->questions()->count() + 1;
            $newQuestion->save();

            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }
        });

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Butir soal berhasil diduplikasi sebagai draft baru.');
    }
}
