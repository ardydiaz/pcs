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
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

        if ($shouldFilter && empty($departmentFilters)) {
            $courses = collect();
            $faculties = collect();
            $facultyCourses = collect();
        } else {
          $facultyCoursesQuery = FacultyCourse::select([
                'id',
                'faculty_id',
                'course_id',
                'section',
                'academic_year',
                'semester',
                'created_at',
            ])
            ->with([
                'faculty:id,user_id,employee_no,department,job_title',
                'faculty.user:id,name,email',
                'course:id,class_code,subject_code,subject_type',
                'schedules:id,faculty_course_id,time,day,status',
            ])
            ->whereHas('course', function ($query) {
                $query->where('subject_type', 'major');
            });

             //$facultyCoursesQuery = FacultyCourse::with(['faculty.user', 'course']);

        
            $facultiesQuery = Faculty::select(['id', 'user_id', 'department', 'job_title'])
                ->with('user:id,name,email');
            $coursesQuery = Course::select(['id', 'class_code', 'subject_code', 'subject_type', 'created_at'])
                ->where('subject_type', 'major');
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
        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

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

        if ($shouldFilter) {
            if (empty($departmentFilters)) {
                return response()->json([
                    "draw" => $draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ]);
            }

            $query->whereHas('facultyCourses.faculty', function ($facultyQuery) use ($departmentFilters) {
                $facultyQuery->forDepartments($departmentFilters);
            })->withCount(['facultyCourses' => function ($facultyCourseQuery) use ($departmentFilters) {
                $facultyCourseQuery->whereHas('faculty', function ($facultyQuery) use ($departmentFilters) {
                    $facultyQuery->forDepartments($departmentFilters);
                });
            }]);
        }

        // =========================
        // SEARCH FILTER
        // =========================
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('class_code', 'like', "%{$search}%")
                ->orWhere('subject_code', 'like', "%{$search}%")
                ->orWhereHas('facultyCourses', function ($facultyCourseQuery) use ($search) {
                    $facultyCourseQuery
                        ->where('section', 'like', "%{$search}%")
                        ->orWhere('academic_year', 'like', "%{$search}%")
                        ->orWhere('semester', 'like', "%{$search}%")
                        ->orWhereHas('faculty', function ($facultyQuery) use ($search) {
                            $facultyQuery
                                ->where('employee_no', 'like', "%{$search}%")
                                ->orWhere('department', 'like', "%{$search}%")
                                ->orWhere('job_title', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHas('schedules', function ($scheduleQuery) use ($search) {
                            $scheduleQuery
                                ->where('day', 'like', "%{$search}%")
                                ->orWhere('time', 'like', "%{$search}%");
                        });
                });

            });
        }

        // =========================
        // TOTAL COUNTS
        // =========================
        $recordsTotalQuery = Course::where('subject_type', 'minor');
        if ($shouldFilter) {
            $recordsTotalQuery->whereHas('facultyCourses.faculty', function ($facultyQuery) use ($departmentFilters) {
                $facultyQuery->forDepartments($departmentFilters);
            });
        }
        $recordsTotal = $recordsTotalQuery->count();

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
            $deleteAction = $user?->role === 'Admin'
                ? '<a class="dropdown-item"
                        href="javascript:void(0);"
                        onclick="list_methods.deleteMinorCourse(this)"
                        data-id="'.$course->id.'">

                            <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>'
                : '';
            $handlerButton = $assignments > 0
                ? '<button type="button"
                        class="course-handler-btn"
                        onclick="list_methods.viewMinorHandlers(this)"
                        data-id="'.$course->id.'"
                        title="View faculty handlers">
                        <i class="bx bx-group"></i>
                        <span>'.$assignments.'</span>
                    </button>'
                : '<button type="button"
                        class="course-handler-btn is-empty"
                        disabled
                        aria-disabled="true"
                        title="No handlers assigned">
                        <i class="bx bx-group"></i>
                        <span>0</span>
                    </button>';

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

                        '.$deleteAction.'

                    </div>
                </div>
            ';

            $data[] = [
                '<span class="evaluation-pill course-pill--class" data-pill-palette="purple" data-pill-value="course-code" style="--pill-bg: #e4c7ff; --pill-color: #4c1d95;">'.$course->class_code.'</span>',
                $course->subject_code,
                $subjectType,
                $handlerButton,
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

    public function minorCourseHandlers($id): JsonResponse
    {
        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

        if ($shouldFilter && empty($departmentFilters)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a department scope for this course.',
            ], 403);
        }

        $courseQuery = Course::query()
            ->where('subject_type', 'minor')
            ->whereKey($id);

        if ($shouldFilter) {
            $courseQuery->whereHas('facultyCourses.faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
        }

        $course = $courseQuery->firstOrFail();

        $assignmentsQuery = FacultyCourse::select([
                'id',
                'faculty_id',
                'course_id',
                'section',
                'academic_year',
                'semester',
            ])
            ->with([
                'faculty:id,user_id,employee_no,department,job_title',
                'faculty.user:id,name,email',
                'schedules:id,faculty_course_id,time,day,status',
            ])
            ->where('course_id', $course->id);

        if ($shouldFilter) {
            $assignmentsQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
        }

        $handlers = $assignmentsQuery
            ->get()
            ->sortBy(function ($assignment) {
                return optional(optional($assignment->faculty)->user)->name ?? '';
            })
            ->map(function ($assignment) {
                $faculty = optional($assignment->faculty);
                $facultyUser = optional($faculty->user);
                $schedules = $assignment->schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'day' => $schedule->day ?? '',
                        'time' => $schedule->time ?? '',
                        'status' => $schedule->status ?? '',
                        'label' => $this->formatScheduleLabel($schedule),
                    ];
                })->values();

                return [
                    'id' => $assignment->id,
                    'faculty_name' => $facultyUser->name ?? ($faculty->name ?? 'N/A'),
                    'employee_no' => $faculty->employee_no ?? '',
                    'department' => $faculty->department ?? '',
                    'job_title' => $faculty->job_title ?? '',
                    'section' => $assignment->section ?? '',
                    'academic_year' => $assignment->academic_year ?? '',
                    'semester' => $assignment->semester ?? '',
                    'schedules' => $schedules,
                    'schedule_label' => $schedules->pluck('label')->filter()->unique()->implode(' | ') ?: 'N/A',
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'course' => [
                'id' => $course->id,
                'class_code' => $course->class_code,
                'subject_code' => $course->subject_code,
                'subject_type' => $course->subject_type,
            ],
            'handlers' => $handlers,
        ]);
    }
    public function saveAddMinorCourse(Request $request){
        $this->authorizeAdminOnly();

         try {

            $request->validate([
                'class_code' => 'required|string',
                'subject_code' => 'required|string',
            ]);

            $course = Course::withTrashed()
                ->where('class_code', $request->class_code)
                ->where('subject_code', $request->subject_code)
                ->where('subject_type', 'minor')
                ->first();

            if ($course) {
                if ($course->trashed()) {
                    $course->restore();
                }
            } else {
                Course::create([
                    'class_code' => $request->class_code,
                    'subject_code' => $request->subject_code,
                    'subject_type' => 'minor', // DEFAULT VALUE
                ]);
            }

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
            $this->authorizeCourseDepartmentAccess($course);

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
        $this->authorizeAdminOnly();

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
        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

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

        if ($shouldFilter) {
            if (empty($departmentFilters)) {
                return response()->json([
                    "draw" => $draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => []
                ]);
            }

            $query->where(function ($departmentQuery) use ($departmentFilters) {
                foreach ($departmentFilters as $department) {
                    $departmentQuery->orWhereRaw(
                        "FIND_IN_SET(?, REPLACE(REPLACE(REPLACE(COALESCE(faculties.department, ''), '  ', ' '), ', ', ','), ', ', ','))",
                        [$department]
                    );
                }
            });
        }
        
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
        $recordsTotalQuery = FacultyCourse::whereHas('course', function ($q) {
            $q->where('subject_type', 'minor');
        });
        if ($shouldFilter) {
            $recordsTotalQuery->whereHas('faculty', function ($facultyQuery) use ($departmentFilters) {
                $facultyQuery->forDepartments($departmentFilters);
            });
        }
        $recordsTotal = $recordsTotalQuery->count();

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

            $deleteAction = $user?->role === 'Admin'
                ? '<a class="dropdown-item" href="javascript:void(0);"
                        onclick="list_methods.deleteAssignMinorCourse(this)"
                        data-id="'.$row->id.'">
                            <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>'
                : '';

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
                        '.$deleteAction.'
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
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer',
        ]);

        $assignmentKeys = [
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester'],
        ];

        $facultyCourse = FacultyCourse::withTrashed()->where($assignmentKeys)->first();

        if ($facultyCourse && !$facultyCourse->trashed()) {
            return response()->json([
                'title' => 'Duplicate',
                'message' => 'This course is already assigned to the faculty.',
            ], 422);
        }

        if ($facultyCourse && $facultyCourse->trashed()) {
            $facultyCourse->restore();
            $facultyCourse->load(['faculty.user', 'course']);
        } else {
            $facultyCourse = FacultyCourse::create($assignmentKeys);
            $facultyCourse->load(['faculty.user', 'course']);
        }

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
            $this->authorizeFacultyCourseDepartmentAccess($assignment);
            $targetFaculty = Faculty::findOrFail($request->faculty_id);
            $this->authorizeFacultyDepartmentAccess($targetFaculty);

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
        $this->authorizeAdminOnly();

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
        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $departmentFilters = $this->resolveDepartmentScope($user);

        $facultyCoursesQuery = FacultyCourse::select([
                'id',
                'faculty_id',
                'course_id',
                'section',
                'academic_year',
                'semester',
            ])
            ->with([
                'faculty:id,user_id,employee_no,department,job_title',
                'faculty.user:id,name,email',
                'course:id,class_code,subject_code,subject_type',
                'schedules:id,faculty_course_id,time,day,status',
            ])
            ->whereHas('course', function ($query) {
                $query->where('subject_type', 'minor');
            });

        $facultiesQuery = Faculty::select(['id', 'user_id', 'department', 'job_title'])
            ->with('user:id,name,email');
        $coursesQuery = Course::select(['id', 'class_code', 'subject_code', 'subject_type'])
            ->where('subject_type', 'minor');

        if ($shouldFilter) {
            if (empty($departmentFilters)) {
                $faculties = collect();
                $courses = collect();
                $facultyCourses = collect();

                return view('content.data-management.dm-minor-course', compact('faculties','courses', 'facultyCourses'));
            }

            $facultyCoursesQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
            $facultiesQuery->forDepartments($departmentFilters);
            $coursesQuery->whereHas('facultyCourses.faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
        }

        $faculties = $facultiesQuery->get();
        $courses = $coursesQuery->get();
        $facultyCourses = $facultyCoursesQuery->get();

        return view('content.data-management.dm-minor-course', compact('faculties','courses', 'facultyCourses'));
    }

    // Course Import
    public function import(Request $request)
    {
        $this->authorizeAdminOnly();

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
        $this->authorizeAdminOnly();

        $subjectType = $request->subject_type ?? 'major';

        $validated = $request->validate([
            'class_code' => 'required|string|max:255',
            'subject_code' => 'required|string|max:255',
        ]);

        $validated['subject_type'] = $subjectType;
        $course = Course::withTrashed()
            ->where('class_code', $validated['class_code'])
            ->where('subject_code', $validated['subject_code'])
            ->where('subject_type', $subjectType)
            ->first();

        if ($course) {
            if (!$course->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course already exists.'
                ], 422);
            }

            $course->restore();
            $course->update($validated);
        } else {
            $course = Course::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Course added successfully!',
            'data' => $course->loadCount('facultyCourses')
        ]);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse
    {
        $this->authorizeCourseDepartmentAccess($course);

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
        $this->authorizeAdminOnly();

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully!'
        ]);
    }

    public function bulkDestroyCourses(Request $request): JsonResponse
    {
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:courses,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values()->all();

        $deleted = Course::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted > 1
                ? "{$deleted} courses deleted successfully!"
                : 'Course deleted successfully!',
            'deleted' => $ids,
        ]);
    }

    public function deletedCourses(string $type = 'major'): JsonResponse
    {
        $this->authorizeAdminOnly();

        $subjectType = $type === 'minor' ? 'minor' : 'major';
        $courses = Course::onlyTrashed()
            ->withCount('facultyCourses')
            ->where('subject_type', $subjectType)
            ->latest('deleted_at')
            ->limit(100)
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'class_code' => $course->class_code,
                'subject_code' => $course->subject_code,
                'subject_type' => $course->subject_type,
                'handlers_count' => $course->faculty_courses_count ?? 0,
                'deleted_at' => optional($course->deleted_at)->format('M d, Y h:i A') ?? 'N/A',
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }

    public function restoreCourse(int $id): JsonResponse
    {
        $this->authorizeAdminOnly();

        $course = Course::onlyTrashed()->findOrFail($id);
        $duplicateExists = Course::where('class_code', $course->class_code)
            ->where('subject_code', $course->subject_code)
            ->where('subject_type', $course->subject_type)
            ->exists();

        if ($duplicateExists) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot restore because an active course with the same class code and subject already exists.',
            ], 422);
        }

        $course->restore();

        return response()->json([
            'success' => true,
            'message' => 'Course restored successfully.',
            'data' => $course->loadCount('facultyCourses'),
        ]);
    }

    // Faculty Course Assignment CRUD
    public function deletedFacultyCourses(): JsonResponse
    {
        $this->authorizeAdminOnly();

        $assignments = FacultyCourse::onlyTrashed()
            ->with([
                'faculty:id,user_id,employee_no,department,job_title',
                'faculty.user:id,name,email',
                'course:id,class_code,subject_code,subject_type',
                'schedules:id,faculty_course_id,time,day,status',
            ])
            ->whereHas('course', function ($query) {
                $query->where('subject_type', 'major');
            })
            ->latest('deleted_at')
            ->limit(100)
            ->get()
            ->map(function ($assignment) {
                $faculty = optional($assignment->faculty);
                $facultyUser = optional($faculty->user);
                $course = optional($assignment->course);
                $scheduleLabel = $assignment->schedules
                    ? $assignment->schedules->map(function ($schedule) {
                        return $this->formatScheduleLabel($schedule);
                    })->filter()->unique()->implode(' | ')
                    : '';

                return [
                    'id' => $assignment->id,
                    'faculty_name' => $facultyUser->name ?? 'N/A',
                    'employee_no' => $faculty->employee_no ?? '',
                    'course' => trim(($course->class_code ?? 'N/A').' - '.($course->subject_code ?? '')),
                    'section' => $assignment->section ?? 'N/A',
                    'academic_year' => $assignment->academic_year ?? 'N/A',
                    'semester' => $assignment->semester ?? 'N/A',
                    'schedule' => $scheduleLabel !== '' ? $scheduleLabel : 'N/A',
                    'deleted_at' => optional($assignment->deleted_at)->format('M d, Y h:i A') ?? 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function restoreFacultyCourse(int $id): JsonResponse
    {
        $this->authorizeAdminOnly();

        $assignment = FacultyCourse::onlyTrashed()->findOrFail($id);

        $duplicateExists = FacultyCourse::where([
            'faculty_id' => $assignment->faculty_id,
            'course_id' => $assignment->course_id,
            'section' => $assignment->section,
            'academic_year' => $assignment->academic_year,
            'semester' => $assignment->semester,
        ])->exists();

        if ($duplicateExists) {
            return response()->json([
                'success' => false,
                'message' => 'This assignment cannot be restored because an active matching assignment already exists.',
            ], 422);
        }

        $assignment->restore();

        return response()->json([
            'success' => true,
            'message' => 'Course assignment restored successfully.',
            'data' => $assignment->load(['faculty.user', 'course']),
        ]);
    }

    public function storeFacultyCourse(Request $request): JsonResponse
    {
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);
        $assignmentKeys = [
            'faculty_id' => $validated['faculty_id'],
            'course_id' => $validated['course_id'],
            'section' => $validated['section'],
            'academic_year' => $validated['academic_year'],
            'semester' => $validated['semester']
        ];

        $existingAssignment = FacultyCourse::withTrashed()->where($assignmentKeys)->first();

        if ($existingAssignment && !$existingAssignment->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'This course is already assigned to the faculty for the same academic year and semester.'
            ], 422);
        }

        if ($existingAssignment && $existingAssignment->trashed()) {
            $existingAssignment->restore();
            $facultyCourse = $existingAssignment;
        } else {
            $facultyCourse = FacultyCourse::create($assignmentKeys);
        }

        $facultyCourse->load(['faculty.user', 'course']);

        return response()->json([
            'success' => true,
            'message' => 'Course assigned successfully!',
            'data' => $facultyCourse
        ]);
    }

    public function updateFacultyCourse(Request $request, FacultyCourse $facultyCourse): JsonResponse
    {
        $this->authorizeFacultyCourseDepartmentAccess($facultyCourse);

        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id', // <--- point to faculties.id
            'course_id' => 'required|exists:courses,id',
            'section' => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);

        // Check for duplicate assignment (excluding current record)
        $exists = FacultyCourse::withTrashed()->where([
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

        $targetFaculty = Faculty::findOrFail($validated['faculty_id']);
        $this->authorizeFacultyDepartmentAccess($targetFaculty);

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
        $this->authorizeAdminOnly();

        $facultyCourse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course assignment removed successfully!'
        ]);
    }

    public function bulkDestroyFacultyCourses(Request $request): JsonResponse
    {
        $this->authorizeAdminOnly();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:faculty_courses,id',
        ]);

        $ids = collect($validated['ids'])->unique()->values()->all();

        $deleted = FacultyCourse::whereIn('id', $ids)->delete();

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

    private function formatScheduleLabel($schedule): string
    {
        $day = trim((string) ($schedule->day ?? ''));
        $time = trim((string) ($schedule->time ?? ''));
        $dayUpper = strtoupper($day);
        $displayDay = in_array($dayUpper, ['N/A', 'NA', 'NONE', '-'], true) ? '' : $day;
        $label = trim(($displayDay !== '' ? $displayDay.' ' : '').$time);

        return $label !== '' ? $label : 'N/A';
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

    private function authorizeCourseDepartmentAccess(Course $course): void
    {
        $user = auth()->user();
        if (!$user || $user->role === 'Admin') {
            return;
        }

        $departmentFilters = $this->resolveDepartmentScope($user);
        if (empty($departmentFilters)) {
            abort(404);
        }

        $allowed = $course->facultyCourses()
            ->whereHas('faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            })
            ->exists();

        if (!$allowed) {
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
}
