<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Semester extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic year that owns this semester.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the active semester.
     */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this semester and its parent academic year.
     */
    public function activate(): void
    {
        DB::transaction(function () {
            // Deactivate all semesters
            static::where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->update(['is_active' => true]);

            // Ensure parent academic year is activated
            $this->academicYear->activate();
        });
    }
}
