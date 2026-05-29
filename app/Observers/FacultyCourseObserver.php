<?php

namespace App\Observers;

use App\Models\FacultyCourse;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class FacultyCourseObserver
{
    public function created(FacultyCourse $facultyCourse): void
    {
        if (auth()->check()) {
            AuditLogger::logModelCreated($facultyCourse, 'Faculty Course', "Faculty course assignment created: {$facultyCourse->section}");
        }
    }

    public function updated(FacultyCourse $facultyCourse): void
    {
        $course = DB::table('courses')
            ->where('id', $facultyCourse->course_id)
            ->first(['class_code', 'subject_code']);

        if (!$course) {
            if (auth()->check()) {
                AuditLogger::logModelUpdated($facultyCourse, 'Faculty Course', "Faculty course assignment updated: {$facultyCourse->section}");
            }
            return;
        }

        DB::table('evaluation_responses')
            ->whereIn('schedule_id', function ($query) use ($facultyCourse) {
                $query->select('id')
                    ->from('schedules')
                    ->where('faculty_course_id', $facultyCourse->id);
            })
            ->update([
                'course_code_snapshot' => $course->class_code,
                'course_name_snapshot' => $course->subject_code,
            ]);

        if (auth()->check()) {
            AuditLogger::logModelUpdated($facultyCourse, 'Faculty Course', "Faculty course assignment updated: {$facultyCourse->section}");
        }
    }

    public function deleted(FacultyCourse $facultyCourse): void
    {
        if (auth()->check()) {
            AuditLogger::logModelDeleted($facultyCourse, 'Faculty Course', "Faculty course assignment deleted: {$facultyCourse->section}");
        }
    }
}
