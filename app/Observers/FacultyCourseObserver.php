<?php

namespace App\Observers;

use App\Models\FacultyCourse;
use Illuminate\Support\Facades\DB;

class FacultyCourseObserver
{
    public function updated(FacultyCourse $facultyCourse): void
    {
        $course = DB::table('courses')
            ->where('id', $facultyCourse->course_id)
            ->first(['class_code', 'subject_code']);

        if (!$course) {
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
    }
}
