<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Question $question) {
            if ($question->question_image && Storage::disk('public')->exists($question->question_image)) {
                Storage::disk('public')->delete($question->question_image);
            }

            foreach ($question->options as $option) {
                if ($option->option_image && Storage::disk('public')->exists($option->option_image)) {
                    Storage::disk('public')->delete($option->option_image);
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'question_category_id',
        'teacher_id',
        'code',
        'question_text',
        'question_image',
        'difficulty',
        'weight',
        'status',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'order' => 'integer',
        ];
    }

    /**
     * Get the category that this question belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    /**
     * Get the teacher who created this question.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('label');
    }

    /**
     * Get the single correct option for this question.
     */
    public function correctOption(): HasOne
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    /**
     * Get the label of the correct answer (e.g. 'A', 'B', 'C', 'D', 'E').
     */
    public function getCorrectAnswerLabel(): ?string
    {
        return $this->correctOption?->label;
    }

    /**
     * Determine whether this question has an attached image.
     */
    public function hasImage(): bool
    {
        return ! empty($this->question_image);
    }

    /**
     * Get the publicly accessible URL for the question image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->hasImage()) {
            return null;
        }

        return asset('storage/' . $this->question_image);
    }

    /**
     * Set a specific option as the correct answer and mark all others as false.
     */
    public function setCorrectOption(string $label): void
    {
        $this->options()->update(['is_correct' => false]);
        $this->options()->where('label', strtoupper($label))->update(['is_correct' => true]);
    }

    /**
     * Scope a query to only include active questions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query by difficulty level.
     */
    public function scopeDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty', $difficulty);
    }
}
