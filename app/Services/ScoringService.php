<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    /**
     * Calculate and persist the score for a single exam attempt based on snapshot data.
     * Idempotent: Can be run multiple times safely with identical results.
     */
    public function scoreAttempt(ExamAttempt $attempt): ExamAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->loadMissing([
                'exam.questions.options',
                'attemptQuestions.question.options',
                'attemptQuestions.selectedOption',
            ]);

            $totalWeight = 0.0;
            $totalEarnedWeight = 0.0;
            $totalCorrect = 0;
            $totalAnswered = 0;
            $totalIncorrect = 0;
            $totalUnanswered = 0;

            foreach ($attempt->attemptQuestions as $attemptQuestion) {
                $examQuestion = $attemptQuestion->question;
                $questionWeight = (float) ($examQuestion?->weight ?? 0);
                $totalWeight += $questionWeight;

                // Find snapshot correct option
                $correctOption = $examQuestion?->options->firstWhere('is_correct', true);
                $selectedOptionId = $attemptQuestion->selected_exam_question_option_id;

                $isCorrect = false;
                $earnedWeight = 0.0;

                if (! is_null($selectedOptionId)) {
                    $totalAnswered++;

                    if ($correctOption && $selectedOptionId === $correctOption->id) {
                        $isCorrect = true;
                        $earnedWeight = $questionWeight;
                        $totalCorrect++;
                        $totalEarnedWeight += $earnedWeight;
                    } else {
                        $totalIncorrect++;
                    }
                } else {
                    $totalUnanswered++;
                }

                $attemptQuestion->update([
                    'is_correct' => $isCorrect,
                    'earned_weight' => $earnedWeight,
                ]);
            }

            // Calculate final scale score (0 - 100)
            $score = $totalWeight > 0 
                ? round(($totalEarnedWeight / $totalWeight) * 100, 2) 
                : 0.00;

            $passingGrade = (float) ($attempt->exam?->passing_grade ?? 0);
            $isPassed = $score >= $passingGrade;

            $attempt->update([
                'score' => $score,
                'total_correct' => $totalCorrect,
                'total_answered' => $totalAnswered,
                'total_incorrect' => $totalIncorrect,
                'total_unanswered' => $totalUnanswered,
                'total_weight' => $totalWeight,
                'earned_weight' => $totalEarnedWeight,
                'is_passed' => $isPassed,
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Batch score all finished or expired attempts for an exam.
     */
    public function scoreExam(Exam $exam): int
    {
        $attempts = $exam->attempts()
            ->whereIn('status', ['completed', 'expired'])
            ->get();

        $count = 0;
        foreach ($attempts as $attempt) {
            $this->scoreAttempt($attempt);
            $count++;
        }

        return $count;
    }
}

