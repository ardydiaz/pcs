<?php

use App\Support\SectionNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('faculty_courses')
            ->select('id', 'section')
            ->orderBy('id')
            ->chunkById(500, function ($facultyCourses) {
                foreach ($facultyCourses as $facultyCourse) {
                    $normalized = SectionNormalizer::normalize($facultyCourse->section);
                    if ($normalized !== '' && $normalized !== $facultyCourse->section) {
                        DB::table('faculty_courses')
                            ->where('id', $facultyCourse->id)
                            ->update(['section' => $normalized]);
                    }
                }
            });

        $duplicateGroups = DB::table('faculty_courses')
            ->select(
                'faculty_id',
                'course_id',
                'section',
                'academic_year',
                'semester',
                DB::raw('MIN(id) as keep_id'),
                DB::raw('COUNT(*) as duplicate_count')
            )
            ->whereNull('deleted_at')
            ->groupBy('faculty_id', 'course_id', 'section', 'academic_year', 'semester')
            ->having('duplicate_count', '>', 1)
            ->get();

        foreach ($duplicateGroups as $group) {
            $duplicateIds = DB::table('faculty_courses')
                ->where('faculty_id', $group->faculty_id)
                ->where('course_id', $group->course_id)
                ->where('section', $group->section)
                ->where('academic_year', $group->academic_year)
                ->where('semester', $group->semester)
                ->whereNull('deleted_at')
                ->where('id', '!=', $group->keep_id)
                ->pluck('id');

            foreach ($duplicateIds as $duplicateId) {
                $duplicateSchedules = DB::table('schedules')
                    ->where('faculty_course_id', $duplicateId)
                    ->whereNull('deleted_at')
                    ->get();

                foreach ($duplicateSchedules as $duplicateSchedule) {
                    $existingSchedule = DB::table('schedules')
                        ->where('faculty_course_id', $group->keep_id)
                        ->where(function ($query) use ($duplicateSchedule) {
                            $duplicateSchedule->day === null
                                ? $query->whereNull('day')
                                : $query->where('day', $duplicateSchedule->day);
                        })
                        ->where(function ($query) use ($duplicateSchedule) {
                            $duplicateSchedule->time === null
                                ? $query->whereNull('time')
                                : $query->where('time', $duplicateSchedule->time);
                        })
                        ->whereNull('deleted_at')
                        ->first();

                    if ($existingSchedule) {
                        DB::table('evaluation_responses')
                            ->where('schedule_id', $duplicateSchedule->id)
                            ->update(['schedule_id' => $existingSchedule->id]);

                        DB::table('schedules')
                            ->where('id', $duplicateSchedule->id)
                            ->update([
                                'deleted_at' => now(),
                                'updated_at' => now(),
                            ]);

                        continue;
                    }

                    DB::table('schedules')
                        ->where('id', $duplicateSchedule->id)
                        ->update([
                            'faculty_course_id' => $group->keep_id,
                            'updated_at' => now(),
                        ]);
                }

                DB::table('faculty_courses')
                    ->where('id', $duplicateId)
                    ->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Section normalization is intentionally not reversible.
    }
};
