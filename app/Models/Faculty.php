<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // Foreign key to users table to link faculty to a user account
        'employee_no', // Unique employee number for the faculty member
        'department',  // Department(s) the faculty belongs to, stored as a comma-separated string e.g "College of Nursing, College of Medicine"
        'job_title',  // Job title of the faculty member

        // Audit Information
        'created_by', // name of the auth user who created the record
    ];

    public static function normalizeDepartmentList(?string $departments): array
    {
        $departments = trim((string) ($departments ?? ''));
        if ($departments === '') {
            return [];
        }

        $decoded = json_decode($departments, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $items = $decoded;
        } else {
            $items = preg_split('/\s*,\s*/', $departments);
        }

        $normalized = array_map(static fn ($value) => trim((string) $value), $items);
        $filtered = array_filter($normalized, static fn ($value) => $value !== '');
        return array_values(array_unique($filtered));
    }

    public static function serializeDepartmentList(array $departments): string
    {
        $normalized = array_map(static fn ($value) => trim((string) $value), $departments);
        $filtered = array_filter($normalized, static fn ($value) => $value !== '');
        $unique = array_values(array_unique($filtered));
        return implode(', ', $unique);
    }

    public static function mergeDepartments(?string $current, ?string $incoming): ?string
    {
        $incoming = trim((string) ($incoming ?? ''));
        if ($incoming === '') {
            return $current;
        }

        $departments = self::normalizeDepartmentList($current);
        if (!in_array($incoming, $departments, true)) {
            $departments[] = $incoming;
        }

        if (empty($departments)) {
            return null;
        }

        return self::serializeDepartmentList($departments);
    }

    public function scopeForDepartment(Builder $query, string $department): Builder
    {
        return $query->forDepartments([$department]);
    }

    public function scopeForDepartments(Builder $query, array $departments): Builder
    {
        $departments = array_map(static fn ($value) => trim((string) $value), $departments);
        $departments = array_values(array_filter($departments, static fn ($value) => $value !== ''));
        if (empty($departments)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($departments) {
            foreach ($departments as $department) {
                $builder->orWhereRaw(
                    "FIND_IN_SET(?, REPLACE(department, ', ', ','))",
                    [$department]
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function facultyCourses(): HasMany
    {
        return $this->hasMany(FacultyCourse::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'faculty_courses')
            ->withPivot('academic_year', 'semester')
            ->withTimestamps();
    }
}
