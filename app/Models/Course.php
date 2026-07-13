<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\FacultyCourse;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'class_code', // e.g 2110001, 213039
        'subject_code', // e.g LRK 002 - Language/Reading, SCK 001 - Science, Computer Programming
        'subject_type',
    ];

    public function facultyCourses(): HasMany
    {
        return $this->hasMany(FacultyCourse::class);
    }

    public function faculties(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class, 'faculty_courses')
            ->withPivot('academic_year', 'semester')
            ->withTimestamps();
    }
}
