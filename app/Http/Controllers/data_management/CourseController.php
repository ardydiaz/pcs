<?php

namespace App\Http\Controllers\data_management;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\FacultyCourse;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Imports\CourseImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $accessLevels = collect($user?->access_level ?? []);
        $canManageCourses = $accessLevels->contains('Manage Courses');
        $shouldFilter = $user && $user->role !== 'Admin' && !$canManageCourses;
        $department = trim($user?->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);

        if ($shouldFilter && empty($departmentFilters)) {
            $courses = collect();
            $faculties = collect();
            $facultyCourses = collect();
        } else {
          $facultyCoursesQuery = FacultyCourse::with(['faculty.user', 'course'])
            ->whereHas('course', function ($query) {
                $query->where('subject_type', 'major');
            });

             //$facultyCoursesQuery = FacultyCourse::with(['faculty.user', 'course']);

        
            $facultiesQuery = Faculty::with('user');
            $coursesQuery = Course::where('subject_type', 'major');
            //$coursesQuery = Course::query();

            if ($shouldFilter) {
                $facultyCoursesQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                    $query->forDepartments($departmentFilters);
                });
                $facultiesQuery->forDepartments($departmentFilters);
                $coursesQuery->whereHas('facultyCourses.faculty', function ($query) use ($departmentFilters) {
                    $query->forDepartments($departmentFilters);
                })->withCount(['facultyCourses' => function ($query) use ($departmentFilters) {
                    $query->whereHas('faculty', function ($facultyQuery) use ($departmentFilters) {
                        $facultyQuery->forDepartments($departmentFilters);
                    });
                }]);
            } else {
                $coursesQuery->withCount('facultyCourses');
            }

            $courses = $coursesQuery->get();
            $faculties = $facultiesQuery->get();
            $facultyCourses = $facultyCoursesQuery->get();
        }
        

        return view('content.data-management.dm-courses', compact('courses', 'faculties', 'facultyCourses'));
    }

    // public function minorCoursesList(Request $request)
    // {
    //     $draw = intval($request->input('draw'));
    //     $start = intval($request->input('start'));
    //     $length = intval($request->input('length'));
    //     $search = $request->input('search.value');

    //     // BASE QUERY (MINOR ONLY)
    //     $query = Course::withCount('facultyCourses')
    //         ->where('subject_type', 'minor');

    //     // SEARCH FILTER
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('class_code', 'like', "%{$search}%")
    //             ->orWhere('subject_code', 'like', "%{$search}%");
    //         });
    //     }

    //     $recordsTotal = Course::where('subject_type', 'minor')->count();
    //     $recordsFiltered = $query->count();

    //     $courses = $query
    //         ->orderBy('created_at', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();

    //     $data = [];
    //     $counter = $start + 1;

    //     foreach ($courses as $course) {

    //         // SUBJECT TYPE BADGE
    //         $subjectType = '<span class="badge rounded-pill text-bg-warning">Minor</span>';

    //         // ASSIGNMENT COUNT
    //         $assignments = $course->faculty_courses_count ?? 0;

    //         // ACTION BUTTONS
    //         $actions = '
    //            <div class="dropdown">
    //                 <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
    //                 <div class="dropdown-menu">
    //                     <a href="javascript:void(0);" onclick="list_methods.updateMinorCourse(this)" data-id="'.$course->id.'" data-toggle="tooltip" data-placement="top" class="dropdown-item"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
    //                     <a class="dropdown-item" href="javascript:void(0);" onclick="list_methods.deleteMinorCourse(this)" data-id="'.$course->id.'"><i class="icon-base bx bx-trash me-1"></i> Delete</a>
    //                 </div>
    //             </div>
    //         ';

    //         $data[] = [
    //             $course->class_code,
    //             $course->subject_code,
    //             $subjectType,
    //             '<span class="evaluation-count-pill">'.$assignments.'</span>',
    //             $actions
    //         ];

    //         $counter++;
    //     }

    //     return response()->json([
    //         "draw" => $draw,
    //         "recordsTotal" => $recordsTotal,
    //         "recordsFiltered" => $recordsFiltered,
    //         "data" => $data
    //     ]);
    // }
    public function minorCoursesList(Request $request)
    {
        $draw = intval($request->input('draw'));
        $start = intval($request->input('start'));
        $length = intval($request->input('length'));
        $search = $request->input('search.value');

        // =========================
        // COLUMN SORTING
        // =========================
        $columns = [
            0 => 'class_code',
            1 => 'subject_code',
            2 => 'subject_type',
        ];

        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir', 'desc');

        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

        // =========================
        // BASE QUERY
        // =========================
        $query = Course::withCount('facultyCourses')
            ->where('subject_type', 'minor');

        // =========================
        // SEARCH FILTER
        // =========================
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('class_code', 'like', "%{$search}%")
                ->orWhere('subject_code', 'like', "%{$search}%");

            });
        }

        // =========================
        // TOTAL COUNTS
        // =========================
        $recordsTotal = Course::where('subject_type', 'minor')->count();

        $recordsFiltered = $query->count();

        // =========================
        // APPLY SORTING
        // =========================
        $query->orderBy($orderColumn, $orderDirection);

        // =========================
        // PAGINATION
        // =========================
        $courses = $query
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];

        foreach ($courses as $course) {

            $subjectType =
                '<span class="badge rounded-pill text-bg-warning">GendEd Course</span>';
            $assignments =
                $course->faculty_courses_count ?? 0;
            $actions = '
                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                    </button>

                    <div class="dropdown-menu">

                        <a href="javascript:void(0);"
                        onclick="list_methods.updateMinorCourse(this)"
                        data-id="'.$course->id.'"
                        class="dropdown-item">

                            <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                        </a>

                        <a class="dropdown-item"
                        href="javascript:void(0);"
                        onclick="list_methods.deleteMinorCourse(this)"
                        data-id="'.$course->id.'">

                            <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>

                    </div>
                </div>
            ';

            $data[] = [
                '<span class="evaluation-pill course-pill--class" data-pill-palette="purple" data-pill-value="course-code" style="--pill-bg: #e4c7ff; --pill-color: #4c1d95;">'.$course->class_code.'</span>',
                $course->subject_code,
                $subjectType,
                '<span class="evaluation-count-pill">'.$assignments.'</span>',
                $actions
            ];
        }

        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }
    public function saveAddMinorCourse(Request $request){
         try {

            $request->validate([
                'class_code' => 'required|string',
                'subject_code' => 'required|string',
            ]);

            Course::create([
                'class_code' => $request->class_code,
                'subject_code' => $request->subject_code,
                'subject_type' => 'minor', // DEFAULT VALUE
            ]);

            return response()->json([
                'success' => true,
                'title' => 'Success',
                'message' => 'Minor course added successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showMinorCourse($id){
        $row = Course::find($id);
        if (!$row) {
            return response()->json(['error' => 'Data not found'], 404);
        }
        return response()->json([
            'class_code' => $row->class_code,
            'subject_code' => $row->subject_code
        ]);
    }

    public function SaveUpdateMinorCourse(Request $request, $id){
         try {

            // =========================
            // VALIDATION
            // =========================
            $validatedData = $request->validate([
                'class_code' => 'required|string|max:255',
                'subject_code' => 'required|string|max:255',
            ]);

            // =========================
            // FIND COURSE
            // =========================
            $course = Course::findOrFail($id);

            // =========================
            // OPTIONAL SAFETY
            // =========================
            if ($course->subject_type !== 'minor') {
                return response()->json([
                    'title' => 'Invalid Course',
                    'message' => 'Only minor subjects can be updated here.',
                    'success' => false
                ], 422);
            }

            // =========================
            // UPDATE
            // =========================
            $course->update([
                'class_code' => $validatedData['class_code'],
                'subject_code' => $validatedData['subject_code'],
            ]);

            // =========================
            // SUCCESS RESPONSE
            // =========================
            return response()->json([
                'title' => 'Course Updated',
                'message' => 'Minor course updated successfully!',
                'success' => true
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'title' => 'Course Not Found',
                'message' => 'The requested course does not exist.',
                'success' => false
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'title' => 'Validation Error',
                'message' => $e->errors(),
                'success' => false
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'title' => 'Update Failed',
                'message' => 'Something went wrong while updating the course.',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function deletMinorCourse(Request $request){
        $id = $request->input('id');
        try{
            $row = Course::findOrFail($id);
            $row->delete(); // Save the updated record
            return response()->json([
                'success' => true,
                'message' => 'Course was successfull deleted!.',
            ], 200);
        }catch (\Exception $e) {
                return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the course.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // public function minorAssignmentsList(Request $request)
    // {
    //     $draw = intval($request->input('draw'));
    //     $start = intval($request->input('start'));
    //     $length = intval($request->input('length'));
    //     $search = $request->input('search.value');

    //     // =========================
    //     // BASE QUERY (MINOR ONLY)
    //     // =========================
    //     $query = FacultyCourse::with(['faculty.user', 'course'])
    //         ->whereHas('course', function ($q) {
    //             $q->where('subject_type', 'minor');
    //         });

    //     // =========================
    //     // SEARCH
    //     // =========================
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('faculty.user', function ($sub) use ($search) {
    //                 $sub->where('name', 'like', "%{$search}%");
    //             })
    //             ->orWhereHas('course', function ($sub) use ($search) {
    //                 $sub->where('class_code', 'like', "%{$search}%")
    //                     ->orWhere('subject_code', 'like', "%{$search}%");
    //             });
    //         });
    //     }

    //     // =========================
    //     // COUNTS
    //     // =========================
    //     $recordsTotal = FacultyCourse::whereHas('course', function ($q) {
    //         $q->where('subject_type', 'minor');
    //     })->count();

    //     $recordsFiltered = $query->count();

    //     // =========================
    //     // PAGINATION
    //     // =========================
    //     $list = $query
    //         ->orderBy('created_at', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();

    //     $data = [];

    //     foreach ($list as $row) {

    //         $facultyName = optional($row->faculty->user)->name ?? 'N/A';
    //         $facultyEmail = optional($row->faculty->user)->email ?? '';

    //         $courseCode = $row->course->class_code ?? 'N/A';
    //         $subjectCode = $row->course->subject_code ?? 'N/A';

    //         $data[] = [

    //             // FACULTY
    //             '
    //                 <span class="text-dark d-block">'.$facultyName.'</span>
    //                 <small class="text-muted">'.$facultyEmail.'</small>
    //             ',

    //             // COURSE
    //             '
    //                 <span class="evaluation-pill course-pill--class">
    //                     '.$courseCode.'
    //                 </span>
    //                 <small class="text-muted d-block mt-1">'.$subjectCode.'</small>
    //             ',

    //             // SUBJECT TYPE (always minor here)
    //             '<span class="badge bg-warning">Minor</span>',

    //             // SECTION
    //             '<span class="evaluation-pill">'.$row->section.'</span>',

    //             // ACADEMIC YEAR
    //             '<span class="evaluation-pill">'.$row->academic_year.'</span>',

    //             // SEMESTER
    //             '<span class="evaluation-pill">'.$row->semester.'</span>',

    //             // ACTIONS
    //             '
    //                 <div class="dropdown">
    //                     <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
    //                     <div class="dropdown-menu">
    //                         <a class="dropdown-item" href="javascript:void(0);" onclick="list_methods.updateAssignMinor(this)" data-id="'.$row->id.'"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
    //                         <a class="dropdown-item" href="javascript:void(0);" onclick="list_methods.deleteAssignMinorCourse(this)" data-id="'.$row->id.'"><i class="icon-base bx bx-trash me-1"></i> Delete</a>
    //                     </div>
    //                 </div>
    //             '
    //         ];
    //     }

    //     return response()->json([
    //         "draw" => $draw,
    //         "recordsTotal" => $recordsTotal,
    //         "recordsFiltered" => $recordsFiltered,
    //         "data" => $data
    //     ]);
    // }
    public function minorAssignmentsList(Request $request)
    {
        $draw = intval($request->input('draw'));
        $start = intval($request->input('start'));
        $length = intval($request->input('length'));
        $search = $request->input('search.value');

        // =========================
        // COLUMN SORTING
        // =========================
        $columns = [
            0 => 'faculties.employee_no', // Faculty
            1 => 'courses.class_code',    // Course
            2 => 'courses.subject_type',  // Subject Type
            3 => 'faculty_courses.section',
            4 => 'faculty_courses.academic_year',
            5 => 'faculty_courses.semester',
        ];

        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir', 'desc');

        $orderColumn = $columns[$orderColumnIndex] ?? 'faculty_courses.created_at';

        // =========================
        // BASE QUERY
        // =========================
        $query = FacultyCourse::with(['faculty.user', 'course'])
            ->join('courses', 'faculty_courses.course_id', '=', 'courses.id')
            ->join('faculties', 'faculty_courses.faculty_id', '=', 'faculties.id')
            ->where('courses.subject_type', 'minor')
            ->select('faculty_courses.*');
        
        // =========================
        // FILTERS
        // =========================
        $facultyId = $request->input('faculty_id');
        $academicYear = $request->input('academic_year');
        $semester = $request->input('semester');

        if (!empty($facultyId) && $facultyId !== 'all') {
            $query->where('faculty_courses.faculty_id', $facultyId);
        }

        if (!empty($academicYear) && $academicYear !== 'all') {
            $query->where('faculty_courses.academic_year', $academicYear);
        }

        if (!empty($semester) && $semester !== 'all') {
            $query->where('faculty_courses.semester', $semester);
        }

        // =========================
        // SEARCH
        // =========================
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->whereHas('faculty.user', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })

                ->orWhereHas('course', function ($sub) use ($search) {
                    $sub->where('class_code', 'like', "%{$search}%")
                        ->orWhere('subject_code', 'like', "%{$search}%");
                })

                ->orWhere('faculty_courses.section', 'like', "%{$search}%")
                ->orWhere('faculty_courses.academic_year', 'like', "%{$search}%")
                ->orWhere('faculty_courses.semester', 'like', "%{$search}%");
            });
        }

        // =========================
        // COUNTS
        // =========================
        $recordsTotal = FacultyCourse::whereHas('course', function ($q) {
            $q->where('subject_type', 'minor');
        })->count();

        $recordsFiltered = $query->count();

        // =========================
        // PAGINATION + SORT
        // =========================
        $list = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = [];

        foreach ($list as $row) {

            if (!$row) continue;

            $facultyName = optional(optional($row->faculty)->user)->name ?? 'N/A';
            $facultyEmail = optional(optional($row->faculty)->user)->email ?? '';

            $courseCode = optional($row->course)->class_code ?? 'N/A';
            $subjectCode = optional($row->course)->subject_code ?? 'N/A';

            $data[] = [
                // FACULTY
                '<span class="text-dark d-block">'.$facultyName.'</span>
                <small class="text-muted">'.$facultyEmail.'</small>',

                // COURSE
                '<span class="evaluation-pill course-pill--class" data-pill-palette="purple" data-pill-value="course-code" style="--pill-bg: #e4c7ff; --pill-color: #4c1d95;">'.$courseCode.'</span>
                <small class="text-muted d-block mt-1">'.$subjectCode.'</small>',

                // SUBJECT TYPE
                '<span class="badge bg-warning">Minor</span>',

                // SECTION
                '<span class="evaluation-pill" data-pill-palette="green" data-pill-value="PHD 3" style="--pill-bg: #d1f9e0; --pill-color: #047857;">'.$row->section.'</span>',

                // YEAR
                '<span class="evaluation-pill" data-pill-palette="blue" data-pill-value="2025-2026" style="--pill-bg: #d3e2ff; --pill-color: #1d4ed8;">'.$row->academic_year.'</span>',

                // SEMESTER
                '<span class="evaluation-pill">'.$row->semester.'</span>',

                // ACTIONS
                '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="javascript:void(0);"
                        onclick="list_methods.updateAssignMinor(this)"
                        data-id="'.$row->id.'">
                            <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);"
                        onclick="list_methods.deleteAssignMinorCourse(this)"
                        data-id="'.$row->id.'">
                            <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>
                    </div>
                </div>'
            ];
        }

        return response()->json([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function SaveAddMinorCourseAssign(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer',
        ]);

        // 🔥 Prevent duplicate safely (no race condition)
        $facultyCourse = FacultyCourse::firstOrCreate(
            [
                'faculty_id' => $validated['faculty_id'],
                'course_id' => $validated['course_id'],
                'section' => $validated['section'],
                'academic_year' => $validated['academic_year'],
                'semester' => $validated['semester'],
            ]
        );

        // If already existed (was not newly created)
        if (!$facultyCourse->wasRecentlyCreated) {
            return response()->json([
                'title' => 'Duplicate',
                'message' => 'This course is already assigned to the faculty.',
            ], 422);
        }

        $facultyCourse->load(['faculty.user', 'course']);

        return response()->json([
            'title' => 'Success',
            'message' => 'Course assigned successfully!',
            'data' => $facultyCourse
        ]);
    }

    public function showMinorAssignment($id)
    {
        $data = FacultyCourse::with(['faculty.user', 'course'])
            ->findOrFail($id);

        return response()->json([
            'id' => $data->id,
            'faculty_id' => $data->faculty_id,
            'course_id' => $data->course_id,
            'section' => $data->section,
            'academic_year' => $data->academic_year,
            'semester' => $data->semester,
        ]);
    }

    public function saveUpdateMinorAssign(Request $request, $id)
    {
        try {
            //dd($request->all());
            $request->validate([
                'faculty_id' => 'required',
                'course_id' => 'required',
                'section' => 'required|string',
                'academic_year' => 'required|string',
                'semester' => 'required|string',
            ]);

            $assignment = FacultyCourse::findOrFail($id);

           $assignment->update(array_filter([
                'faculty_id' => $request->faculty_id,
                'course_id' => $request->course_id,
                'section' => $request->section,
                'academic_year' => $request->academic_year,
                'semester' => $request->semester,
            ]));

            return response()->json([
                'title' => 'Updated',
                'message' => 'Assignment updated successfully',
                'success' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'title' => 'Validation Error',
                'message' => $e->errors(),
                'success' => false
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'title' => 'Error',
                'message' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    public function deletAssignMinorCourse(Request $request){
        $id = $request->input('id');
        try{
            $row = FacultyCourse::findOrFail($id);
            $row->delete(); // Save the updated record
            return response()->json([
                'success' => true,
                'message' => 'Assign course was successfull deleted!.',
            ], 200);
        }catch (\Exception $e) {
                return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the course.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function minorSubject(){
        $facultyCoursesQuery = FacultyCourse::with(['faculty.user', 'course'])
            ->whereHas('course', function ($query) {
                $query->where('subject_type', 'minor');
            });
        $faculties = Faculty::with('user')->get();
        $courses = Course::where('subject_type', 'minor')->get();
        $facultyCourses = $facultyCoursesQuery->get();

        return view('content.data-management.dm-minor-course', compact('faculties','courses', 'facultyCourses'));
    }

    // Course Import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);

        //Excel::import(new CourseImport, $request->file('file'));
         $import = new CourseImport;

         Excel::import($import, $request->file('file'));

         // =========================
        // Check errors from import
        // =========================
        if (!empty($import->getErrors())) {
            return back()->with('error', implode('<br>', $import->getErrors()));
        }

        return back()->with('success', 'Courses imported successfully!');
    }

    // Course CRUD
    public function storeCourse(Request $request): JsonResponse
    {
        $subjectType = $request->subject_type ?? 'major';

        $validated = $request->validate([
            'class_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'class_code')
                    ->where('subject_code', $request->subject_code)
                    ->where('subject_type', $subjectType),
            ],
            'subject_code' => 'required|string|max:255',
        ]);

        $validated['subject_type'] = $subjectType;
        $course = Course::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course added successfully!',
            'data' => $course->loadCount('facultyCourses')
        ]);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse
    {
        $subjectType = $request->subject_type ?? $course->subject_type ?? 'major';

        $validated = $request->validate([
            'class_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'class_code')
                    ->where('subject_code', $request->subject_code)
                    ->where('subject_type', $subjectType)
                    ->ignore($course->id),
            ],
            'subject_code' => 'required|string|max:255',
        ]);

        $validated['subject_type'] = $subjectType;
        $course->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully!',
            'data' => $course->loadCount('facultyCourses')
        ]);
    }

    public function destroyCourse(Course $course): JsonResponse
    {
        DB::transaction(function () use ($course) {
            $this->deleteAssignmentsForCourseIds([$course->id]);
            $course->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully!'
        ]);
    }

    public function bulkDestroyCourses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:courses,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values()->all();

        $deleted = DB::transaction(function () use ($ids) {
            $this->deleteAssignmentsForCourseIds($ids);
            return Course::whereIn('id', $ids)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} courses deleted successfully!"
                : 'Course deleted successfully!',
            'deleted' => $ids,
        ]);
    }

    // Faculty Course Assignment CRUD
    public function storeFacultyCourse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);
        // Check for duplicate assignment
        $exists = FacultyCourse::where([
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester']
        ])->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This course is already assigned to the faculty for the same academic year and semester.'
            ], 422);
        }

        $facultyCourse = FacultyCourse::firstOrCreate([
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester'],
        ]);
        $facultyCourse->load(['faculty.user', 'course']);

        return response()->json([
            'success' => true,
            'message' => 'Course assigned successfully!',
            'data' => $facultyCourse
        ]);
    }

    public function updateFacultyCourse(Request $request, FacultyCourse $facultyCourse): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id', // <--- point to faculties.id
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);

        // Check for duplicate assignment (excluding current record)
        $exists = FacultyCourse::where([
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester']
        ])->where('id', '!=', $facultyCourse->id)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This course is already assigned to the faculty for the same academic year and semester.'
            ], 422);
        }

        $facultyCourse->update([
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester'],
        ]);
        $facultyCourse->load(['faculty.user', 'course']);

        return response()->json([
            'success' => true,
            'message' => 'Course assignment updated successfully!',
            'data' => $facultyCourse
        ]);
    }

    public function destroyFacultyCourse(FacultyCourse $facultyCourse): JsonResponse
    {
        DB::transaction(function () use ($facultyCourse) {
            $this->deleteSchedulesByAssignmentIds([$facultyCourse->id]);
            $facultyCourse->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Course assignment removed successfully!'
        ]);
    }

    public function bulkDestroyFacultyCourses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:faculty_courses,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values()->all();

        $deleted = DB::transaction(function () use ($ids) {
            $this->deleteSchedulesByAssignmentIds($ids);
            return FacultyCourse::whereIn('id', $ids)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} course assignments deleted successfully!"
                : 'Course assignment deleted successfully!',
            'deleted' => $ids,
        ]);
    }

    /**
     * Remove faculty course assignments (and their schedules) tied to the provided course ids.
     */
    private function deleteAssignmentsForCourseIds(array $courseIds): void
    {
        $courseIds = collect($courseIds)->filter()->unique()->values();

        if ($courseIds->isEmpty()) {
            return;
        }

        $assignmentIds = FacultyCourse::whereIn('course_id', $courseIds)->pluck('id')->all();

        if (empty($assignmentIds)) {
            return;
        }

        $this->deleteSchedulesByAssignmentIds($assignmentIds);
        FacultyCourse::whereIn('id', $assignmentIds)->delete();
    }

    /**
     * Delete all schedules referencing the provided faculty_course ids.
     */
    private function deleteSchedulesByAssignmentIds(array $assignmentIds): void
    {
        $assignmentIds = collect($assignmentIds)->filter()->unique()->values();

        if ($assignmentIds->isEmpty()) {
            return;
        }

        Schedule::whereIn('faculty_course_id', $assignmentIds)->delete();
    }
}
