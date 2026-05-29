<?php

namespace App\Observers;

use App\Models\Schedule;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class ScheduleObserver
{
    public function created(Schedule $schedule): void
    {
        if (auth()->check()) {
            AuditLogger::logModelCreated($schedule, 'Schedule', "Schedule created: {$schedule->day} {$schedule->time}");
        }
    }

    public function updated(Schedule $schedule): void
    {
        DB::table('evaluation_responses')
            ->where('schedule_id', $schedule->id)
            ->update([
                'schedule_time_snapshot' => $schedule->time,
                'schedule_days_snapshot' => $schedule->day,
            ]);

        if (!$schedule->faculty_course_id) {
            if (auth()->check()) {
                AuditLogger::logModelUpdated($schedule, 'Schedule', "Schedule updated: {$schedule->day} {$schedule->time}");
            }
            return;
        }

        $course = DB::table('faculty_courses')
            ->join('courses', 'courses.id', '=', 'faculty_courses.course_id')
            ->where('faculty_courses.id', $schedule->faculty_course_id)
            ->first(['courses.class_code', 'courses.subject_code']);

        if (!$course) {
            if (auth()->check()) {
                AuditLogger::logModelUpdated($schedule, 'Schedule', "Schedule updated: {$schedule->day} {$schedule->time}");
            }
            return;
        }

        DB::table('evaluation_responses')
            ->where('schedule_id', $schedule->id)
            ->update([
                'course_code_snapshot' => $course->class_code,
                'course_name_snapshot' => $course->subject_code,
            ]);

        if (auth()->check()) {
            AuditLogger::logModelUpdated($schedule, 'Schedule', "Schedule updated: {$schedule->day} {$schedule->time}");
        }
    }

    public function deleted(Schedule $schedule): void
    {
        if (auth()->check()) {
            AuditLogger::logModelDeleted($schedule, 'Schedule', "Schedule deleted: {$schedule->day} {$schedule->time}");
        }
    }
}
