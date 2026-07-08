<?php
// Evaluation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'faculty_id',
        'form_link',
        'academic_year',
        'semester',
        'is_active',
        'faculty_name_snapshot',
        'faculty_email_snapshot',
        'faculty_department_snapshot',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    public function getTokenAttribute()
    {
        return last(explode('/', $this->form_link));
    }

    public function getResolvedFacultyNameAttribute(): string
    {
        $name = trim($this->faculty->name ?? $this->faculty_name_snapshot ?? '');

        return $name === '' ? 'Unknown' : $name;
    }

    public function getResolvedFacultyEmailAttribute(): string
    {
        return $this->faculty->email ?? $this->faculty_email_snapshot ?? '';
    }

    public function getResolvedFacultyDepartmentAttribute(): string
    {
        return $this->faculty->department ?? $this->faculty_department_snapshot ?? '';
    }

    public function getResolvedProgramLabelAttribute(): string
    {
        $department = strtolower($this->resolved_faculty_department);
        if (!str_contains($department, 'college of dentistry')) {
            return '';
        }

        $facultyProfile = Faculty::where('user_id', $this->faculty_id)->first();
        if (!$facultyProfile) {
            return '';
        }

        $hasOrthodonticsSchedule = Schedule::whereHas('facultyCourse', function ($query) use ($facultyProfile) {
            $query->where('faculty_id', $facultyProfile->id)
                ->where('academic_year', $this->academic_year)
                ->where('semester', $this->semester)
                ->whereHas('course', function ($courseQuery) {
                    $courseQuery
                        ->where('class_code', 'LIKE', 'MSDO%')
                        ->orWhere('subject_code', 'LIKE', 'MSDO%')
                        ->orWhere('subject_code', 'LIKE', '%Orthodontic%')
                        ->orWhere('subject_code', 'LIKE', '%Orthodontics%');
                });
        })->exists();

        return $hasOrthodonticsSchedule
            ? 'Master of Science in Dentistry with specialization in Orthodontics'
            : '';
    }
}
