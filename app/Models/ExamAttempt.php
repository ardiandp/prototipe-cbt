<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'exam_id',
        'student_id',
        'attempt_number',
        'started_at',
        'expires_at',
        'completed_at',
        'status',
        'score',
        'total_correct',
        'total_answered',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'attempt_number' => 'integer',
            'score' => 'float',
            'total_correct' => 'integer',
            'total_answered' => 'integer',
        ];
    }

    /**
     * Get the exam for this attempt.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student for this attempt.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the ordered questions for this attempt.
     */
    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(ExamAttemptQuestion::class, 'exam_attempt_id')->orderBy('order');
    }

    /**
     * Determine if this attempt is currently in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Determine if this attempt has been completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Determine if this attempt is expired (explicitly marked expired or server time past expires_at).
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->isInProgress() && Carbon::now()->greaterThanOrEqualTo($this->expires_at)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if this attempt is actively running (in_progress and server time before expires_at).
     */
    public function isActive(): bool
    {
        return $this->isInProgress() && Carbon::now()->lessThan($this->expires_at);
    }

    /**
     * Get the remaining time in seconds for this attempt.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if (! $this->isActive()) {
            return 0;
        }

        return max(0, (int) Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Mark this attempt as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
            'completed_at' => $this->completed_at ?? $this->expires_at,
        ]);
    }

    /**
     * Mark this attempt as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Scope a query to only include in-progress attempts.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed attempts.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only active attempts (in_progress and not yet expired).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'in_progress')
            ->where('expires_at', '>', Carbon::now());
    }
}

