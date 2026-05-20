<?php

namespace App\Observers;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleObserver
{
    public function updated(Schedule $schedule): void
    {
        DB::table('evaluation_responses')
            ->where('schedule_id', $schedule->id)
            ->update([
                'schedule_time_snapshot' => $schedule->time,
                'schedule_days_snapshot' => $schedule->day,
            ]);

        if (!$schedule->faculty_course_id) {
            return;
        }

        $course = DB::table('faculty_courses')
            ->join('courses', 'courses.id', '=', 'faculty_courses.course_id')
            ->where('faculty_courses.id', $schedule->faculty_course_id)
            ->first(['courses.class_code', 'courses.subject_code']);

        if (!$course) {
            return;
        }

        DB::table('evaluation_responses')
            ->where('schedule_id', $schedule->id)
            ->update([
                'course_code_snapshot' => $course->class_code,
                'course_name_snapshot' => $course->subject_code,
            ]);
    }
}
