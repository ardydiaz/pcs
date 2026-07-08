<?php

namespace App\Http\Controllers\data_management;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Faculty;
use App\Models\FacultyCourse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Imports\ScheduleImport;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

        if ($shouldFilter && empty($departmentFilters)) {
            $schedules = collect();
            $facultyCourses = collect();
        } else {
            $schedulesQuery = Schedule::select(['id', 'faculty_course_id', 'time', 'day', 'created_at'])
                ->with([
                    'facultyCourse:id,faculty_id,course_id,section,academic_year,semester',
                    'facultyCourse.faculty:id,user_id,department,job_title',
                    'facultyCourse.faculty.user:id,name,email',
                    'facultyCourse.course:id,class_code,subject_code',
                ])
                ->orderBy('day', 'asc')
                ->orderBy('time', 'asc');
            $facultyCoursesQuery = FacultyCourse::select([
                    'id',
                    'faculty_id',
                    'course_id',
                    'section',
                    'academic_year',
                    'semester',
                ])
                ->with([
                    'faculty:id,user_id,department,job_title',
                    'faculty.user:id,name,email',
                    'course:id,class_code,subject_code',
                ]);

            if ($shouldFilter) {
                $schedulesQuery->whereHas('facultyCourse.faculty', function ($query) use ($departmentFilters) {
                    $query->forDepartments($departmentFilters);
                });
                $facultyCoursesQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                    $query->forDepartments($departmentFilters);
                });
            }

            $schedules = $schedulesQuery->get();
            $facultyCourses = $facultyCoursesQuery->get();
        }

        return view('content.data-management.dm-schedules', compact('schedules', 'facultyCourses'));
    }

    public function import(Request $request)
    {
        $this->authorizeAdminOnly();

        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx'
        ]);

        $import = new ScheduleImport();

        Excel::import($import, $request->file('file'));

        $errors = $import->getErrors();
        $errorCount = count($errors);

        // OPTIONAL: count success (if needed later you can extend)
        $message = $errorCount > 0
            ? "Import completed with {$errorCount} errors."
            : "Schedules imported successfully.";

        return back()->with([
            'success' => $message,
            'errors' => $errors
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'faculty_course_id' => 'required|exists:faculty_courses,id',
            'time' => 'required|string', // e.g. "07:00a - 08:30a"
            'day' => 'required|array',
            'day.*' => 'in:M,T,W,TH,F,S,SU',
        ]);

        $validated['day'] = implode('', $validated['day']); // e.g. ["T","TH"] => "TTH"
        $validated['time'] = $this->normalizeTimeInput($validated['time']);

        $exists = Schedule::where('faculty_course_id', $validated['faculty_course_id'])
            ->where('time', $validated['time'])
            ->where('day', $validated['day'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This schedule already exists for the selected course, time, and day(s).'
            ], 422);
        }

        $schedule = Schedule::create($validated);
        $schedule->load('facultyCourse.course');
        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully',
            'data' => $schedule
        ]);
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $this->authorizeScheduleDepartmentAccess($schedule);

        $validated = $request->validate([
            'faculty_course_id' => 'required|exists:faculty_courses,id',
            'time' => 'required|string', // e.g. "07:00a - 08:30a"
            'day' => 'required|array',
            'day.*' => 'in:M,T,W,TH,F,S,SU', // Validate that each day is one of the allowed values
        ]);

        $validated['day'] = implode('', $validated['day']);
        $validated['time'] = $this->normalizeTimeInput($validated['time']);
        $targetFacultyCourse = FacultyCourse::with('faculty.user')->findOrFail($validated['faculty_course_id']);
        $this->authorizeFacultyCourseDepartmentAccess($targetFacultyCourse);

        $exists = Schedule::where('faculty_course_id', $validated['faculty_course_id'])
            ->where('time', $validated['time'])
            ->where('day', $validated['day'])
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This schedule already exists for the selected course, time, and day(s).'
            ], 422);
        }

        $schedule->update($validated);
        $schedule->load('facultyCourse.course');
        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully!',
            'data' => $schedule
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorizeAdminOnly();

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully!'
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:schedules,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values();
        $deleted = Schedule::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} schedules deleted successfully!"
                : 'Schedule deleted successfully!',
            'deleted' => $ids,
        ]);
    }

    /**
     * Ensure stored time uses "-" instead of "to" regardless of user input.
     */
    private function normalizeTimeInput(string $time): string
    {
        $normalized = preg_replace('/\s*to\s*/i', ' - ', $time);
        return preg_replace('/\s+/', ' ', trim($normalized));
    }

    private function authorizeAdminOnly(): void
    {
        if (auth()->user()?->role !== 'Admin') {
            abort(404);
        }
    }

    private function resolveDepartmentScope($user): array
    {
        if (!$user) {
            return [];
        }

        return array_values(array_unique(array_merge(
            Faculty::normalizeDepartmentList($user->department ?? ''),
            Faculty::normalizeDepartmentList(optional($user->faculty)->department ?? '')
        )));
    }

    private function authorizeFacultyDepartmentAccess(Faculty $faculty): void
    {
        $user = auth()->user();
        if (!$user || $user->role === 'Admin') {
            return;
        }

        $departmentFilters = $this->resolveDepartmentScope($user);
        if (empty($departmentFilters)) {
            abort(404);
        }

        $facultyDepartments = array_values(array_unique(array_merge(
            Faculty::normalizeDepartmentList($faculty->department ?? ''),
            Faculty::normalizeDepartmentList(optional($faculty->user)->department ?? '')
        )));

        if (collect($departmentFilters)->intersect($facultyDepartments)->isEmpty()) {
            abort(404);
        }
    }

    private function authorizeFacultyCourseDepartmentAccess(FacultyCourse $facultyCourse): void
    {
        $facultyCourse->loadMissing('faculty.user');
        if (!$facultyCourse->faculty) {
            abort(404);
        }

        $this->authorizeFacultyDepartmentAccess($facultyCourse->faculty);
    }

    private function authorizeScheduleDepartmentAccess(Schedule $schedule): void
    {
        $schedule->loadMissing('facultyCourse.faculty.user');
        if (!$schedule->facultyCourse) {
            abort(404);
        }

        $this->authorizeFacultyCourseDepartmentAccess($schedule->facultyCourse);
    }
}
