<?php

namespace App\Observers;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseObserver
{
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
    }
}
