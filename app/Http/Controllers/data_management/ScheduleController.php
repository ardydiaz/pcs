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
        $accessLevels = collect($user?->access_level ?? []);
        $canManageSchedules = $accessLevels->contains('Manage Schedules');
        $shouldFilter = $user && $user->role !== 'Admin' && !$canManageSchedules;
        $department = trim($user?->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);

        if ($shouldFilter && empty($departmentFilters)) {
            $schedules = collect();
            $facultyCourses = collect();
        } else {
            $schedulesQuery = Schedule::with(['facultyCourse.faculty.user', 'facultyCourse.course'])
                ->orderBy('day', 'asc')
                ->orderBy('time', 'asc');
            $facultyCoursesQuery = FacultyCourse::with(['faculty.user', 'course']);

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
        $validated = $request->validate([
            'faculty_course_id' => 'required|exists:faculty_courses,id',
            'time' => 'required|string', // e.g. "07:00a - 08:30a"
            'day' => 'required|array',
            'day.*' => 'in:M,T,W,TH,F,S,SU', // Validate that each day is one of the allowed values
        ]);

        $validated['day'] = implode('', $validated['day']);
        $validated['time'] = $this->normalizeTimeInput($validated['time']);

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
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully!'
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
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
}
