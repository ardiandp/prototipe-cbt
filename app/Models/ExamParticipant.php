<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamParticipant extends Model
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
        'classroom_id',
        'status',
        'can_retake',
        'retake_reason',
        'retake_authorized_by',
        'retake_authorized_at',
    ];

    protected $casts = [
        'can_retake' => 'boolean',
        'retake_authorized_at' => 'datetime',
    ];

    /**
     * Get the user who authorized retake for this participant.
     */
    public function retakeAuthorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retake_authorized_by');
    }

    /**
     * Get the exam that this participant is assigned to.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student record for this participant.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the classroom record snapshot for this participant.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Scope a query to only include registered participants.
     */
    public function scopeRegistered(Builder $query): Builder
    {
        return $query->where('status', 'registered');
    }

    /**
     * Scope a query by classroom.
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('classroom_id', $classroomId);
    }
}
