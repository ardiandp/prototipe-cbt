<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptQuestionOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_attempt_question_id',
        'exam_question_option_id',
        'display_label',
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
            'order' => 'integer',
        ];
    }

    /**
     * Get the attempt question this option is mapped to.
     */
    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamAttemptQuestion::class, 'exam_attempt_question_id');
    }

    /**
     * Get the underlying snapshot question option record.
     */
    public function questionOption(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionOption::class, 'exam_question_option_id');
    }

    /**
     * Get the option text from the snapshot.
     */
    public function getOptionTextAttribute(): ?string
    {
        return $this->questionOption?->option_text;
    }

    /**
     * Get the option image URL from the snapshot (if any).
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->questionOption?->image_url;
    }

    /**
     * Determine if this option has an image.
     */
    public function hasImage(): bool
    {
        return (bool) $this->questionOption?->hasImage();
    }
}
