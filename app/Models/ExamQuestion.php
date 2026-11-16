<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'question_id',
        'order',
        'code',
        'question_text',
        'question_image',
        'difficulty',
        'weight',
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
            'weight' => 'float',
        ];
    }

    /**
     * Get the exam that this snapshot question belongs to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the original question bank item, if it still exists.
     */
    public function originalQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Get the snapshot options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class)->orderBy('label');
    }

    /**
     * Get the correct answer option.
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(ExamQuestionOption::class)->where('is_correct', true);
    }

    /**
     * Get the label of the correct option (e.g. 'A', 'B', 'C', 'D', 'E').
     */
    public function getCorrectAnswerLabel(): ?string
    {
        return $this->correctOption?->label;
    }

    /**
     * Determine whether this question snapshot has an image attached.
     */
    public function hasImage(): bool
    {
        return ! empty($this->question_image);
    }

    /**
     * Get the public URL for the question image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->hasImage()) {
            return null;
        }

        return asset('storage/' . $this->question_image);
    }
}

