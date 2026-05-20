<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id', // Foreign key to the faculties table to link a faculty member to their course assignments
        'course_id', // Foreign key to the courses table to link a faculty member to a specific course they are teaching
        'section', // e.g. "BSN 2 Group 4 - 1", "BSN 2 Group 4 - 2", "BSN 2 Group 4 - 3"
        'academic_year', // e.g. "2025-2026"
        'semester' // e.g. "1st", "2nd", "Summer"
    ];

    // public function faculty(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'faculty_id');
    // }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
