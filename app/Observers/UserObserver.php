<?php

namespace App\Observers;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    public function updated(User $user): void
    {
        $faculty = Faculty::where('user_id', $user->id)->first(['id', 'department', 'job_title']);

        if ($faculty && ($user->wasChanged('department') || $user->wasChanged('job_title'))) {
            $normalizedDepartment = Faculty::serializeDepartmentList(
                Faculty::normalizeDepartmentList($user->department ?? '')
            );
            $departmentValue = $normalizedDepartment !== '' ? $normalizedDepartment : null;

            $updates = [];
            if ($user->wasChanged('department') && $faculty->department !== $departmentValue) {
                $updates['department'] = $departmentValue;
            }
            if ($user->wasChanged('job_title') && $faculty->job_title !== $user->job_title) {
                $updates['job_title'] = $user->job_title;
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('faculties')
                    ->where('id', $faculty->id)
                    ->update($updates);
                $faculty = $faculty->refresh();
            }
        }
        $departmentList = array_merge(
            Faculty::normalizeDepartmentList($user->department ?? ''),
            Faculty::normalizeDepartmentList($faculty?->department ?? '')
        );
        $department = $departmentList === []
            ? null
            : Faculty::serializeDepartmentList($departmentList);

        DB::table('evaluations')
            ->where('faculty_id', $user->id)
            ->update([
                'faculty_name_snapshot' => $user->name,
                'faculty_email_snapshot' => $user->email,
                'faculty_department_snapshot' => $department,
            ]);
    }
}
