<?php

namespace App\Observers;

use App\Models\Course;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class CourseObserver
{
    public function created(Course $course): void
    {
        if (auth()->check()) {
            AuditLogger::logModelCreated($course, 'Course', "Course created: {$course->class_code}");
        }
    }

    public function updated(Course $course): void
    {
        DB::table('evaluation_responses')
            ->join('schedules', 'evaluation_responses.schedule_id', '=', 'schedules.id')
            ->join('faculty_courses', 'schedules.faculty_course_id', '=', 'faculty_courses.id')
            ->where('faculty_courses.course_id', $course->id)
            ->update([
                'course_code_snapshot' => $course->class_code,
                'course_name_snapshot' => $course->subject_code,
            ]);

        if (auth()->check()) {
            AuditLogger::logModelUpdated($course, 'Course', "Course updated: {$course->class_code}");
        }
    }

    public function deleted(Course $course): void
    {
        if (auth()->check()) {
            AuditLogger::logModelDeleted($course, 'Course', "Course deleted: {$course->class_code}");
        }
    }
}
