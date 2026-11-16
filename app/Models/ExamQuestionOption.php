<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_question_id',
        'label',
        'option_text',
        'option_image',
        'is_correct',
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
            'is_correct' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Get the snapshot question that owns this option.
     */
    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }

    /**
     * Determine whether this option snapshot has an image attached.
     */
    public function hasImage(): bool
    {
        return ! empty($this->option_image);
    }

    /**
     * Get the public URL for the option image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->hasImage()) {
            return null;
        }

        return asset('storage/' . $this->option_image);
    }
}

