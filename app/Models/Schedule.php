<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FacultyCourse; // Import FacultyCourse model for relationship definition faculty_id, faculty_course_id

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_course_id', // Foreign key to link the schedule to a specific faculty course assignment
        'time', // string: "07:00a - 08:30a"
        'day', // string: "M, T, W"
        'status', // string: "scheduled", "completed", "cancelled"
    ];

    /**
     * Get the faculty course assignment that owns the schedule.
     */
    public function facultyCourse(): BelongsTo
    {
        return $this->belongsTo(FacultyCourse::class);
    }

    /**
     * Get the faculty through the faculty course assignment.
     */
    public function faculty()
    {
        return $this->facultyCourse()->with('faculty');
    }

    /**
     * Get the course through the faculty course assignment.
     */
    public function course()
    {
        return $this->facultyCourse()->with('course');
    }

    /**
     * Scope to get schedules for current academic year and semester
     */
    public function scopeCurrentTerm($query, $academicYear = null, $semester = null)
    {
        return $query->whereHas('facultyCourse', function ($q) use ($academicYear, $semester) {
            if ($academicYear) {
                $q->where('academic_year', $academicYear);
            }
            if ($semester) {
                $q->where('semester', $semester);
            }
        });
    }

    /**
     * Scope to get schedules by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the faculty course with all details including section.
     * Returns the FacultyCourse relationship which includes the section column.
     */
    public function facultyCourseWithDetails(): BelongsTo
    {
        return $this->belongsTo(FacultyCourse::class, 'faculty_course_id');
    }
}