<?php

namespace App\Observers;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FacultyObserver
{
    public function updated(Faculty $faculty): void
    {
        if (!$faculty->user_id) {
            return;
        }

        $user = User::find($faculty->user_id);
        if (!$user) {
            return;
        }

        $departmentValue = Faculty::serializeDepartmentList(
            Faculty::normalizeDepartmentList($faculty->department ?? '')
        );
        $departmentValue = $departmentValue !== '' ? $departmentValue : null;

        if ($faculty->wasChanged('department') || $faculty->wasChanged('job_title')) {
            $updates = [];
            if ($faculty->wasChanged('department') && $user->department !== $departmentValue) {
                $updates['department'] = $departmentValue;
            }
            if ($faculty->wasChanged('job_title') && $user->job_title !== $faculty->job_title) {
                $updates['job_title'] = $faculty->job_title;
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('users')
                    ->where('id', $user->id)
                    ->update($updates);
            }
        }

        $userDepartment = $faculty->wasChanged('department')
            ? $departmentValue
            : $user->department;

        $departmentList = array_merge(
            Faculty::normalizeDepartmentList($userDepartment ?? ''),
            Faculty::normalizeDepartmentList($faculty->department ?? '')
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
