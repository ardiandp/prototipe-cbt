<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttemptQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'order',
        'selected_exam_question_option_id',
        'is_doubtful',
        'answered_at',
        'client_timestamp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_doubtful' => 'boolean',
            'answered_at' => 'datetime',
            'client_timestamp' => 'integer',
        ];
    }

    /**
     * Get the attempt this ordered question belongs to.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    /**
     * Get the original snapshot question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    /**
     * Get the ordered options mapped for this question in this attempt.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExamAttemptQuestionOption::class, 'exam_attempt_question_id')->orderBy('order');
    }

    /**
     * Get the selected option chosen by the student (if any).
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionOption::class, 'selected_exam_question_option_id');
    }

    /**
     * Determine if this question has been answered.
     */
    public function isAnswered(): bool
    {
        return ! is_null($this->selected_exam_question_option_id);
    }
}
