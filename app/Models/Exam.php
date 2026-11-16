<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'semester_id',
        'code',
        'title',
        'description',
        'start_at',
        'end_at',
        'duration',
        'passing_grade',
        'total_questions',
        'random_question',
        'random_answer',
        'token',
        'status',
        'is_results_published',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'duration' => 'integer',
            'passing_grade' => 'float',
            'total_questions' => 'integer',
            'random_question' => 'boolean',
            'random_answer' => 'boolean',
            'is_results_published' => 'boolean',
        ];
    }

    /**
     * Get the subject that this exam belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher who created / manages this exam.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the academic year for this exam.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester for this exam.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the snapshot questions assigned to this exam.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    /**
     * Calculate the total weight of all snapshot questions in this exam.
     */
    public function getTotalWeightAttribute(): float
    {
        return (float) $this->questions()->sum('weight');
    }

    /**
     * Determine if the exam is in draft state.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Determine if the exam is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Determine if the exam is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Determine if the exam is currently open based on server time and schedule window.
     */
    public function isOpen(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $now = Carbon::now();

        return $now->greaterThanOrEqualTo($this->start_at) && $now->lessThan($this->end_at);
    }

    /**
     * Get the computed operational status based on administrative status and time window.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'draft') {
            return 'draft';
        }

        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $now = Carbon::now();

        if ($now->lessThan($this->start_at)) {
            return 'scheduled';
        }

        if ($now->greaterThanOrEqualTo($this->end_at)) {
            return 'completed';
        }

        return 'ongoing';
    }

    /**
     * Get the human-readable Indonesian label for the computed status.
     */
    public function getComputedStatusLabelAttribute(): string
    {
        return match ($this->computed_status) {
            'draft' => 'Draft',
            'cancelled' => 'Dibatalkan',
            'scheduled' => 'Terjadwal',
            'ongoing' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the Bootstrap badge color class for the computed status.
     */
    public function getComputedStatusBadgeClassAttribute(): string
    {
        return match ($this->computed_status) {
            'draft' => 'secondary',
            'cancelled' => 'danger',
            'scheduled' => 'info text-white',
            'ongoing' => 'success',
            'completed' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Scope a query to only include published exams.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include exams belonging to a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to exams currently ongoing / open.
     */
    public function scopeActiveSchedule(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('status', 'published')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>', $now);
    }
}

