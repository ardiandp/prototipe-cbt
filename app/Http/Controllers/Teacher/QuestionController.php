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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            $questionImagePath = null;
            if ($request->hasFile('question_image')) {
                $questionImagePath = $request->file('question_image')->store('questions', 'public');
            }

            $question = Question::create([
                'question_category_id' => $questionBank->id,
                'teacher_id' => $request->user()?->teacherProfile?->id,
                'code' => $data['code'] ?? null,
                'question_text' => $data['question_text'],
                'question_image' => $questionImagePath,
                'difficulty' => $data['difficulty'],
                'weight' => $data['weight'],
                'status' => $data['status'],
                'order' => $questionBank->questions()->count() + 1,
            ]);

            $correctOption = strtoupper($data['correct_option']);

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                $optionImagePath = null;
                if ($request->hasFile("option_images.{$label}")) {
                    $optionImagePath = $request->file("option_images.{$label}")->store('question_options', 'public');
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'option_text' => $data['options'][$label] ?? '',
                    'option_image' => $optionImagePath,
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

        DB::transaction(function () use ($data, $question, $request) {
            $questionData = [
                'code' => $data['code'] ?? null,
                'question_text' => $data['question_text'],
                'difficulty' => $data['difficulty'],
                'weight' => $data['weight'],
                'status' => $data['status'],
            ];

            if ($request->boolean('remove_question_image')) {
                if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
                    Storage::disk('public')->delete($question->question_image);
                }
                $questionData['question_image'] = null;
            } elseif ($request->hasFile('question_image')) {
                if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
                    Storage::disk('public')->delete($question->question_image);
                }
                $questionData['question_image'] = $request->file('question_image')->store('questions', 'public');
            }

            $question->update($questionData);

            $correctOption = strtoupper($data['correct_option']);
            $existingOptions = $question->options->keyBy('label');

            foreach (['A', 'B', 'C', 'D', 'E'] as $index => $label) {
                $option = $existingOptions->get($label);
                $optImage = $option?->option_image;

                if ($request->boolean("remove_option_images.{$label}")) {
                    if ($optImage && Storage::disk('public')->exists($optImage)) {
                        Storage::disk('public')->delete($optImage);
                    }
                    $optImage = null;
                } elseif ($request->hasFile("option_images.{$label}")) {
                    if ($optImage && Storage::disk('public')->exists($optImage)) {
                        Storage::disk('public')->delete($optImage);
                    }
                    $optImage = $request->file("option_images.{$label}")->store('question_options', 'public');
                }

                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'label' => $label,
                    ],
                    [
                        'option_text' => $data['options'][$label] ?? '',
                        'option_image' => $optImage,
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

            if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
                $ext = pathinfo($question->question_image, PATHINFO_EXTENSION);
                $copiedPath = 'questions/' . Str::random(40) . ($ext ? '.' . $ext : '');
                Storage::disk('public')->copy($question->question_image, $copiedPath);
                $newQuestion->question_image = $copiedPath;
            }

            $newQuestion->save();

            foreach ($question->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;

                if ($option->option_image && Storage::disk('public')->exists($option->option_image)) {
                    $optExt = pathinfo($option->option_image, PATHINFO_EXTENSION);
                    $copiedOptPath = 'question_options/' . Str::random(40) . ($optExt ? '.' . $optExt : '');
                    Storage::disk('public')->copy($option->option_image, $copiedOptPath);
                    $newOption->option_image = $copiedOptPath;
                }

                $newOption->save();
            }
        });

        return redirect()->route('teacher.question-banks.show', $questionBank)
            ->with('success', 'Butir soal berhasil diduplikasi sebagai draft baru.');
    }
}

