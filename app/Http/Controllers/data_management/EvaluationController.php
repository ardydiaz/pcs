<?php

namespace App\Http\Controllers\data_management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Evaluation, EvaluationResponse, Schedule, User, FacultyCourse, Faculty};
use Illuminate\Support\Str;
use App\Support\AuditLogger;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Carbon\Carbon;

class EvaluationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $accessLevels = collect($user?->access_level ?? []);
        $canManageEvaluations = $accessLevels->contains('Manage Evaluations');
        $shouldFilter = $user && $user->role !== 'Admin' && !$canManageEvaluations;
        $department = trim($user?->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);

        if ($shouldFilter && empty($departmentFilters)) {
            $evaluations = collect();
            $faculties = collect();
            $academicYearOptions = collect();
            $semesterOptions = collect();
            return view('content.data-management.dm-evaluation', compact(
                'evaluations',
                'faculties',
                'academicYearOptions',
                'semesterOptions'
            ));
        }

        $evaluationsQuery = Evaluation::with('faculty')->latest();
        $facultiesQuery = $this->getEligibleFacultyProfiles($shouldFilter ? $departmentFilters : null);

        if ($shouldFilter) {
            $departmentFacultyIds = Faculty::forDepartments($departmentFilters)->pluck('user_id');
            $evaluationsQuery->whereIn('faculty_id', $departmentFacultyIds);
        }

        $evaluations = $evaluationsQuery->get();
        $faculties = $facultiesQuery
            ->map(function ($faculty) {
                if (!$faculty->user) {
                    return null;
                }

                return (object) [
                    'id' => $faculty->user->id,
                    'name' => $faculty->user->name,
                    'email' => $faculty->user->email,
                ];
            })
            ->filter()
            ->values();

        $academicYearQuery = FacultyCourse::query();

        if ($shouldFilter) {
            $academicYearQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
        }

        $academicYearOptions = $academicYearQuery
            ->whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->distinct()
            ->orderBy('academic_year')
            ->pluck('academic_year')
            ->values();

        // Use a fixed list of valid semesters to prevent duplicates from inconsistent DB values
        $semesterOptions = collect([
            ['value' => '1st',    'label' => '1st Semester'],
            ['value' => '2nd',    'label' => '2nd Semester'],
            ['value' => 'Summer', 'label' => 'Summer'],
        ]);

        return view('content.data-management.dm-evaluation', compact(
            'evaluations',
            'faculties',
            'academicYearOptions',
            'semesterOptions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:users,id',
            'academic_year' => 'required|string',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);

        $normalizedSemester = $this->normalizeSemesterValue($request->semester) ?? $request->semester;
        $normalizedAcademicYear = $this->normalizeAcademicYear($request->academic_year);

        $facultyProfile = Faculty::where('user_id', $request->faculty_id)->first();
        if (!$facultyProfile) {
            return back()->with('error', 'Selected faculty does not have a faculty profile. Please verify the assignment.');
        }
        $this->enforceDepartmentAccess($facultyProfile);

        if (!$this->facultyHasScheduleForTerm($facultyProfile, $normalizedAcademicYear, $normalizedSemester)) {
            return back()->with('error', 'Selected faculty does not have an assigned course with a schedule for the specified academic year and semester.');
        }

        $existingEvaluation = $this->findExistingEvaluation(
            $request->faculty_id,
            $normalizedAcademicYear,
            $normalizedSemester
        );

        if ($existingEvaluation) {
            return back()->with('error', 'Evaluation form already exists for this faculty in the selected academic year and semester.');
        }

        // Extra safety: direct DB check with normalized semester value
        $directDuplicate = Evaluation::where('faculty_id', $request->faculty_id)
            ->where('academic_year', $normalizedAcademicYear)
            ->where('semester', $normalizedSemester)
            ->exists();

        if ($directDuplicate) {
            return back()->with('error', 'Evaluation form already exists for this faculty in the selected academic year and semester.');
        }

        $formLink = $this->generateUniqueLink($request->faculty_id, $normalizedAcademicYear, $normalizedSemester);
        $user = User::find($request->faculty_id);
        $snapshot = $user ? $this->buildFacultySnapshot($user, $facultyProfile) : [];

        $evaluation = Evaluation::create([
            'faculty_id' => $request->faculty_id,
            'academic_year' => $normalizedAcademicYear,
            'semester' => $normalizedSemester,
            'form_link' => $formLink,
            ...$snapshot,
        ]);
        

        return back()->with('success', 'Evaluation form generated successfully.');
    }

    public function generateAll(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'semester' => 'required|in:1st,2nd,Summer'
        ]);

        $normalizedSemester = $this->normalizeSemesterValue($request->semester) ?? $request->semester;

        $user = auth()->user();
        $shouldFilter = $user && $user->role !== 'Admin';
        $department = trim($user?->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);
        if ($shouldFilter && empty($departmentFilters)) {
            return back()->with('error', 'No department assigned. Please contact the administrator.');
        }

        $faculties = $this->getEligibleFacultyProfiles($shouldFilter ? $departmentFilters : null);
        $generated = 0;
        $skipped = 0;
        $skippedFaculties = []; // Array to store skipped faculty names

        foreach ($faculties as $facultyProfile) {
            $user = $facultyProfile->user;
            if (!$user) {
                $skipped++;
                $skippedFaculties[] = 'Unknown faculty (missing user record)';
                continue;
            }

            // Check if faculty has assigned course + schedule
            $hasSchedule = $this->facultyHasScheduleForTerm($facultyProfile, $request->academic_year, $normalizedSemester);

            if (!$hasSchedule) {
                $skipped++;
                $skippedFaculties[] = $user->name . ' (No schedule)';
                continue;
            }

            // Skip if evaluation already exists (check with findExistingEvaluation + direct DB check)
            $exists = $this->findExistingEvaluation(
                $user->id,
                $request->academic_year,
                $normalizedSemester
            );

            if (!$exists) {
                $exists = Evaluation::where('faculty_id', $user->id)
                    ->where('academic_year', $request->academic_year)
                    ->where('semester', $normalizedSemester)
                    ->exists();
            }

            if ($exists) {
                $skipped++;
                $skippedFaculties[] = $user->name . ' (Already exists)';
                continue;
            }

            // Generate form link
            $formLink = $this->generateUniqueLink($user->id, $request->academic_year, $normalizedSemester);
            $snapshot = $this->buildFacultySnapshot($user, $facultyProfile);

            Evaluation::create([
                'faculty_id' => $user->id,
                'academic_year' => $request->academic_year,
                'semester' => $normalizedSemester,
                'form_link' => $formLink,
                ...$snapshot,
            ]);

            $generated++;
        }

        // Build success message with skipped faculty details
        $noScheduleCount = collect($skippedFaculties)->filter(fn($f) => str_ends_with($f, '(No schedule)'))->count();
        $alreadyExistsCount = collect($skippedFaculties)->filter(fn($f) => str_ends_with($f, '(Already exists)'))->count();

        $message = "Generated {$generated} new evaluation form(s).";

        if ($skipped > 0) {
            $message .= " Skipped {$skipped} faculty member(s)";
            $details = [];
            if ($noScheduleCount > 0) {
                $details[] = "{$noScheduleCount} with no schedule";
            }
            if ($alreadyExistsCount > 0) {
                $details[] = "{$alreadyExistsCount} already have an evaluation";
            }
            $otherCount = $skipped - $noScheduleCount - $alreadyExistsCount;
            if ($otherCount > 0) {
                $details[] = "{$otherCount} other reason(s)";
            }
            if (!empty($details)) {
                $message .= ' (' . implode(', ', $details) . ').';
            } else {
                $message .= '.';
            }
        }

        return back()->with('success', $message);
    }

    protected function getEligibleFacultyProfiles(?array $departments = null)
    {
        $query = Faculty::with(['user:id,name,email'])
            ->whereHas('facultyCourses', function ($courseQuery) {
                $courseQuery->whereHas('schedules');
            });

        if ($departments !== null && !empty($departments)) {
            $query->forDepartments($departments);
        }

        return $query->get();
    }

    protected function facultyHasScheduleForTerm(Faculty $facultyProfile, string $academicYear, string $semester): bool
    {
        return Schedule::whereHas('facultyCourse', function ($query) use ($facultyProfile, $academicYear, $semester) {
            $query->where('faculty_id', $facultyProfile->id)
                ->where('academic_year', $academicYear)
                ->where('semester', $semester);
        })->exists();
    }

    protected function formatSemesterLabel(?string $value): string
    {
        $value = trim($value ?? '');
        if ($value === '') {
            return 'Unknown Semester';
        }

        $normalized = strtolower(preg_replace('/[^a-z0-9]+/', '', $value));

        $mapping = [
            '1' => '1st Semester',
            '1st' => '1st Semester',
            'first' => '1st Semester',
            'firstsem' => '1st Semester',
            'firstsemester' => '1st Semester',
            'semester1' => '1st Semester',
            '2' => '2nd Semester',
            '2nd' => '2nd Semester',
            'second' => '2nd Semester',
            'secondsem' => '2nd Semester',
            'secondsemester' => '2nd Semester',
            'semester2' => '2nd Semester',
            'summer' => 'Summer',
            'summersem' => 'Summer',
            'summersemester' => 'Summer',
            'midyear' => 'Summer',
            '3' => 'Summer',
            '3rd' => 'Summer',
        ];

        return $mapping[$normalized] ?? ucfirst($value);
    }

    /**
     * Normalize academic year format to match database storage.
     * Converts "2025 - 2026" to "2025-2026"
     */
    protected function normalizeAcademicYear(string $academicYear): string
    {
        $academicYear = trim($academicYear);
        
        // Remove all spaces and normalize dashes
        $normalized = preg_replace('/\s*-\s*/', '-', $academicYear);  // "2025 - 2026" → "2025-2026"
        $normalized = preg_replace('/\s+/', '', $normalized);          // Remove any remaining spaces
        
        return $normalized;
    }

    protected function normalizeSemesterValue(?string $value): ?string
    {
        $value = trim($value ?? '');
        if ($value === '') {
            return null;
        }

        $normalized = strtolower(preg_replace('/[^a-z0-9]+/', '', $value));

        $mapping = [
            '1' => '1st',
            '1st' => '1st',
            'first' => '1st',
            'firstsem' => '1st',
            'firstsemester' => '1st',
            'semester1' => '1st',
            '2' => '2nd',
            '2nd' => '2nd',
            'second' => '2nd',
            'secondsem' => '2nd',
            'secondsemester' => '2nd',
            'semester2' => '2nd',
            'summer' => 'Summer',
            'summersem' => 'Summer',
            'summersemester' => 'Summer',
            'midyear' => 'Summer',
            '3' => 'Summer',
            '3rd' => 'Summer',
        ];

        return $mapping[$normalized] ?? null;
    }

    protected function findExistingEvaluation(int $facultyId, string $academicYear, string $semester): ?Evaluation
    {
        $normalizedSemester = $this->normalizeSemesterValue($semester);
        if (!$normalizedSemester) {
            return null;
        }

        // Check all possible semester variants that normalize to the same value
        $semesterVariants = collect([
            '1st', 'first', 'firstsem', 'firstsemester', 'semester1', '1',
            '2nd', 'second', 'secondsem', 'secondsemester', 'semester2', '2',
            'Summer', 'summer', 'summersem', 'summersemester', 'midyear', '3', '3rd',
        ])->filter(function ($v) use ($normalizedSemester) {
            return $this->normalizeSemesterValue($v) === $normalizedSemester;
        })->values()->all();

        return Evaluation::where('faculty_id', $facultyId)
            ->where('academic_year', $academicYear)
            ->whereIn('semester', $semesterVariants)
            ->first();
    }

    public function toggleStatus(Request $request, Evaluation $evaluation)
    {
        $previousStatus = $evaluation->is_active;
        $evaluation->update(['is_active' => !$evaluation->is_active]);
        $status = $evaluation->is_active ? 'activated' : 'deactivated';

        AuditLogger::log($evaluation->is_active ? 'evaluation_activated' : 'evaluation_deactivated', [
            'module' => 'Evaluation',
            'description' => "Evaluation form {$status} for {$evaluation->resolved_faculty_name} ({$evaluation->academic_year} - {$evaluation->semester}).",
            'target_type' => Evaluation::class,
            'target_id' => $evaluation->id,
            'before_values' => [
                'is_active' => $previousStatus,
            ],
            'after_values' => [
                'faculty_id' => $evaluation->faculty_id,
                'faculty_name' => $evaluation->resolved_faculty_name,
                'academic_year' => $evaluation->academic_year,
                'semester' => $evaluation->semester,
                'is_active' => $evaluation->is_active,
            ],
            'severity' => $evaluation->is_active ? 'info' : 'warning',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Evaluation form has been {$status}.",
                'data' => [
                    'id' => $evaluation->id,
                    'is_active' => $evaluation->is_active,
                    'status' => $evaluation->is_active ? 'active' : 'inactive',
                    'status_label' => $evaluation->is_active ? 'Active' : 'Inactive',
                    'toggle_label' => $evaluation->is_active ? 'Deactivate' : 'Activate',
                ],
            ]);
        }

        return back()->with('success', "Evaluation form has been {$status}.");
    }

    public function destroy(Request $request, Evaluation $evaluation)
    {
        $logValues = [
            'faculty_id' => $evaluation->faculty_id,
            'faculty_name' => $evaluation->resolved_faculty_name,
            'academic_year' => $evaluation->academic_year,
            'semester' => $evaluation->semester,
            'is_active' => $evaluation->is_active,
            'form_link' => $evaluation->form_link,
        ];

        $evaluation->delete();

        AuditLogger::log('evaluation_deleted', [
            'module' => 'Evaluation',
            'description' => "Evaluation form deleted for {$logValues['faculty_name']} ({$logValues['academic_year']} - {$logValues['semester']}).",
            'target_type' => Evaluation::class,
            'target_id' => $evaluation->id,
            'before_values' => $logValues,
            'severity' => 'danger',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Evaluation form deleted successfully.',
                'data' => [
                    'id' => $evaluation->id,
                ],
            ]);
        }

        return back()->with('success', 'Evaluation form deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'message' => 'No evaluation ids provided.'
            ], 422);
        }

        $evaluations = Evaluation::whereIn('id', $ids)->get();
        $logValues = $evaluations->map(fn ($evaluation) => [
            'id' => $evaluation->id,
            'faculty_id' => $evaluation->faculty_id,
            'faculty_name' => $evaluation->resolved_faculty_name,
            'academic_year' => $evaluation->academic_year,
            'semester' => $evaluation->semester,
            'is_active' => $evaluation->is_active,
        ])->values()->all();

        $deleted = Evaluation::whereIn('id', $ids)->delete();

        AuditLogger::log('evaluation_bulk_deleted', [
            'module' => 'Evaluation',
            'description' => "Bulk deleted {$deleted} evaluation form(s).",
            'target_type' => Evaluation::class,
            'before_values' => [
                'evaluations' => $logValues,
            ],
            'severity' => 'danger',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Selected evaluations deleted successfully.',
            'deleted' => $deleted,
        ]);
    }

    public function downloadQrCode(Evaluation $evaluation)
    {
        $this->enforceEvaluationAccess($evaluation);
        $faculty = $evaluation->faculty;
        $fileName = "evaluation_qr_{$faculty->name}_{$evaluation->academic_year}_{$evaluation->semester}.png";
        $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);

        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 8,
            'imageBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $qrCodeImage = $qrcode->render($evaluation->form_link);

        AuditLogger::log('evaluation_qr_downloaded', [
            'module' => 'Evaluation',
            'description' => "Downloaded QR code for {$evaluation->resolved_faculty_name} ({$evaluation->academic_year} - {$evaluation->semester}).",
            'target_type' => Evaluation::class,
            'target_id' => $evaluation->id,
            'after_values' => [
                'faculty_id' => $evaluation->faculty_id,
                'faculty_name' => $evaluation->resolved_faculty_name,
                'academic_year' => $evaluation->academic_year,
                'semester' => $evaluation->semester,
                'file_name' => $fileName,
            ],
            'severity' => 'info',
        ]);

        return response($qrCodeImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function showQrCode(Evaluation $evaluation)
    {
        $this->enforceEvaluationAccess($evaluation);
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 6,
            'imageBase64' => false,
        ]);

        $qrcode = new QRCode($options);
        $qrCodeImage = $qrcode->render($evaluation->form_link);

        AuditLogger::log('evaluation_qr_generated', [
            'module' => 'Evaluation',
            'description' => "Generated QR code preview for {$evaluation->resolved_faculty_name} ({$evaluation->academic_year} - {$evaluation->semester}).",
            'target_type' => Evaluation::class,
            'target_id' => $evaluation->id,
            'after_values' => [
                'faculty_id' => $evaluation->faculty_id,
                'faculty_name' => $evaluation->resolved_faculty_name,
                'academic_year' => $evaluation->academic_year,
                'semester' => $evaluation->semester,
            ],
            'severity' => 'info',
        ]);

        return response($qrCodeImage)->header('Content-Type', 'image/png');
    }

    public function showForm($token)
    {
        $evaluation = Evaluation::where('form_link', 'LIKE', "%{$token}%")
            ->where('is_active', true)
            ->firstOrFail();

        // Get the faculty record for this user
        $facultyRecord = \App\Models\Faculty::where('user_id', $evaluation->faculty_id)->first();

        if (!$facultyRecord) {
            abort(404, 'Faculty record not found');
        }

        $schedules = Schedule::with(['facultyCourse.course'])
            ->whereHas('facultyCourse', function ($query) use ($facultyRecord, $evaluation) {
                $query->where('faculty_id', $facultyRecord->id)
                    ->where('academic_year', $evaluation->academic_year)
                    ->where('semester', $evaluation->semester);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.data-management.evaluation-files.evaluation-form', compact('evaluation', 'schedules'));
    }

    public function submitResponse(Request $request, $token)
    {
        $evaluation = Evaluation::where('form_link', 'LIKE', "%{$token}%")
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'effectiveness_rating' => 'required|in:1,2,3,4',
            'feedback_comments' => 'nullable|string|max:1000',
        ]);

        $schedule = Schedule::with(['facultyCourse.course'])->findOrFail($request->schedule_id);
        $course = optional($schedule->facultyCourse)->course;

        $ipAddress = $request->ip();
        $scheduleId = $request->schedule_id;
        $cooldownMinutes = 1; // 1 minute cooldown

        // Check if IP is still in cooldown period for this specific schedule
        if (EvaluationResponse::isInCooldown($scheduleId, $ipAddress, $cooldownMinutes)) {
            $remainingSeconds = EvaluationResponse::getRemainingCooldown($scheduleId, $ipAddress, $cooldownMinutes);
            $remainingTime = gmdate("i:s", $remainingSeconds); // Format as MM:SS

            // Return with a specific cooldown error that the frontend can detect
            return back()->with('error', "Please wait {$remainingTime} before submitting another evaluation for this course.")->withInput();
        }

        EvaluationResponse::create([
            'evaluation_id' => $evaluation->id,
            'schedule_id' => $request->schedule_id,
            'ip_address' => $ipAddress,
            'effectiveness_rating' => $request->effectiveness_rating,
            'feedback_comments' => $request->feedback_comments,
            'course_code_snapshot' => $course->class_code ?? null,
            'course_name_snapshot' => $course->subject_code ?? null,
            'schedule_time_snapshot' => $schedule->time,
            'schedule_days_snapshot' => $schedule->day,
        ]);

        return back()->with('success', 'Thank you! Your evaluation has been submitted successfully.');
    }

    private function generateUniqueLink($facultyId, $academicYear, $semester)
    {
        $faculty = User::find($facultyId);
        $facultySlug = Str::slug($faculty->name, '_');
        $yearSlug = str_replace('-', '_', $academicYear);
        $semesterSlug = strtolower($semester);
        $randomToken = Str::random(12);

        return url("/eval/{$randomToken}");
    }

    protected function countUniqueCoursesFromResponses($responses): int
    {
        return $responses->map(function ($response) {
            return $this->makeResponseCourseKey($response);
        })->unique()->count();
    }

    protected function makeResponseCourseKey(EvaluationResponse $response): string
    {
        if (!empty($response->schedule_id)) {
            return 'schedule_' . $response->schedule_id;
        }

        return 'snapshot_' . implode('|', [
            $response->course_code_snapshot ?? '',
            $response->course_name_snapshot ?? '',
            $response->schedule_time_snapshot ?? '',
            $response->schedule_days_snapshot ?? '',
        ]);
    }

    // public function viewResponses(Evaluation $evaluation, Request $request)
    // {
    //     $this->enforceEvaluationAccess($evaluation);
        
    //     // Get filter parameters from query string
    //     $academicYear = $request->get('academic_year', 'all');
    //     $semester = $request->get('semester', 'all');
    //     $subjectType = $request->get('subject_type', 'all');
        
    //     // Validate subject_type
    //     if (!in_array($subjectType, ['all', 'major', 'minor'], true)) {
    //         $subjectType = 'all';
    //     }
        
    //     // Get all responses for this faculty (not just this evaluation)
    //     // This matches the export logic which also uses faculty_id
    //     $responsesQuery = EvaluationResponse::with(['schedule.facultyCourse.course'])
    //         ->whereHas('schedule.facultyCourse', function ($query) use ($evaluation) {
    //             $query->where('faculty_id', $evaluation->faculty_id);
    //         });
        
    //     // Apply academic_year filter if specified
    //     if ($academicYear !== 'all') {
    //         $responsesQuery->whereHas('schedule.facultyCourse', function ($q) use ($academicYear) {
    //             $q->where('academic_year', $academicYear);
    //         });
    //     }
        
    //     // Apply semester filter if specified
    //     // Handle both "Summer" and "2nd" semester since data may be stored inconsistently
    //     if ($semester !== 'all') {
    //         $responsesQuery->whereHas('schedule.facultyCourse', function ($q) use ($semester) {
    //             $q->where('semester', $semester)
    //               ->orWhere(function ($query) use ($semester) {
    //                   // If looking for Summer, also include 2nd semester
    //                   if ($semester === 'Summer') {
    //                       $query->where('semester', '2nd');
    //                   }
    //                   // If looking for 2nd, also include Summer
    //                   elseif ($semester === '2nd') {
    //                       $query->where('semester', 'Summer');
    //                   }
    //               });
    //         });
    //     }
        
    //     // Apply subject_type filter if specified
    //     if ($subjectType !== 'all') {
    //         $responsesQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
    //             $q->where('subject_type', $subjectType);
    //         });
    //     }
        
    //     $responses = $responsesQuery->latest()->get();

    //     $coursesEvaluatedCount = $this->countUniqueCoursesFromResponses($responses);

    //     return view('content.data-management.evaluation-files.evaluation-responses', compact(
    //         'evaluation',
    //         'responses',
    //         'coursesEvaluatedCount'
    //     ));
    // }
        public function viewResponses(Evaluation $evaluation)
    {
        $this->enforceEvaluationAccess($evaluation);
        
        // Get filter parameters from query string
        $academicYear = request('academic_year', $evaluation->academic_year);
        $semester = request('semester', $evaluation->semester);
        $subjectType = request('subject_type', 'all');
        
        \Log::info('ViewResponses - Starting', [
            'evaluation_id' => $evaluation->id,
            'faculty_id' => $evaluation->faculty_id,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'subject_type' => $subjectType
        ]);
        
        // Build query to fetch responses based on evaluation and filter parameters
        $query = EvaluationResponse::with(['schedule.facultyCourse.course', 'evaluation'])
            ->where('evaluation_id', $evaluation->id);
        
        // Filter by academic_year if provided and not 'all'
        if ($academicYear && $academicYear !== 'all') {
            $query->whereHas('evaluation', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }
        
        // Filter by semester if provided and not 'all'
        if ($semester && $semester !== 'all') {
            $query->whereHas('evaluation', function ($q) use ($semester) {
                $q->where('semester', $semester);
            });
        }
        
        // Filter by subject_type if provided and not 'all'
        if ($subjectType && $subjectType !== 'all') {
            $query->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                $q->where('subject_type', $subjectType);
            });
        }
        
        $responses = $query->latest()->get();
        
        \Log::info('ViewResponses - Query result', [
            'evaluation_id' => $evaluation->id,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'subject_type' => $subjectType,
            'count' => $responses->count()
        ]);
        
        // If no responses found with evaluation_id, try by faculty_id + academic_year + semester
        if ($responses->isEmpty() && $evaluation->faculty_id) {
            \Log::info('ViewResponses - Fallback: Attempt by faculty_id + academic_year + semester', [
                'faculty_id' => $evaluation->faculty_id,
                'academic_year' => $academicYear,
                'semester' => $semester
            ]);
            
            $fallbackQuery = EvaluationResponse::with(['schedule.facultyCourse.course', 'evaluation'])
                ->whereHas('evaluation', function ($q) use ($evaluation, $academicYear, $semester) {
                    $q->where('faculty_id', $evaluation->faculty_id)
                      ->where('academic_year', $academicYear)
                      ->where('semester', $semester)
                      ->where('is_active', true);
                });
            
            if ($subjectType && $subjectType !== 'all') {
                $fallbackQuery->whereHas('schedule.facultyCourse.course', function ($q) use ($subjectType) {
                    $q->where('subject_type', $subjectType);
                });
            }
            
            $responses = $fallbackQuery->latest()->get();
            
            \Log::info('ViewResponses - Fallback result', [
                'count' => $responses->count()
            ]);
        }

        $coursesEvaluatedCount = $this->countUniqueCoursesFromResponses($responses);

        AuditLogger::log('evaluation_responses_viewed', [
            'module' => 'Evaluation',
            'description' => "Viewed responses for {$evaluation->resolved_faculty_name} ({$academicYear} - {$semester}).",
            'target_type' => Evaluation::class,
            'target_id' => $evaluation->id,
            'after_values' => [
                'faculty_id' => $evaluation->faculty_id,
                'faculty_name' => $evaluation->resolved_faculty_name,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'subject_type' => $subjectType,
                'responses_count' => $responses->count(),
                'courses_evaluated_count' => $coursesEvaluatedCount,
            ],
            'severity' => 'info',
        ]);

        return view('content.data-management.evaluation-files.evaluation-responses', compact(
            'evaluation',
            'responses',
            'coursesEvaluatedCount'
        ));
    }

    public function exportResponses(Evaluation $evaluation, Request $request)
    {
        $this->enforceEvaluationAccess($evaluation);

        $academicYear = $request->get('academic_year', $evaluation->academic_year);
        $semester = $request->get('semester', $evaluation->semester);
        $subjectType = $request->get('subject_type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        \Log::info('ExportResponses - Starting', [
            'evaluation_id' => $evaluation->id,
            'faculty_id' => $evaluation->faculty_id,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'subject_type' => $subjectType,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Start with base query - get all responses for this evaluation
        $responsesQuery = EvaluationResponse::with(['schedule.facultyCourse.course', 'evaluation'])
            ->where('evaluation_id', $evaluation->id);

        // Get all responses first (before date filtering)
        $allResponsesBeforeDateFilter = (clone $responsesQuery)->orderBy('created_at')->get();
        
        \Log::info('ExportResponses - All responses before date filter', [
            'evaluation_id' => $evaluation->id,
            'total_count' => $allResponsesBeforeDateFilter->count(),
            'first_response_created_at' => $allResponsesBeforeDateFilter->first()?->created_at
        ]);

        // If no responses found with evaluation_id, try by faculty_id + academic_year + semester (fallback)
        if ($allResponsesBeforeDateFilter->isEmpty() && $evaluation->faculty_id) {
            \Log::info('ExportResponses - Fallback: Attempt by faculty_id + academic_year + semester', [
                'faculty_id' => $evaluation->faculty_id,
                'academic_year' => $academicYear,
                'semester' => $semester
            ]);
            
            $responsesQuery = EvaluationResponse::with(['schedule.facultyCourse.course', 'evaluation'])
                ->whereHas('evaluation', function ($q) use ($evaluation, $academicYear, $semester) {
                    $q->where('faculty_id', $evaluation->faculty_id)
                      ->where('academic_year', $academicYear)
                      ->where('semester', $semester)
                      ->where('is_active', true);
                });
            
            $allResponsesBeforeDateFilter = (clone $responsesQuery)->orderBy('created_at')->get();
            
            \Log::info('ExportResponses - Fallback result', [
                'count' => $allResponsesBeforeDateFilter->count()
            ]);
        }

        // Apply date filters only if provided
        if ($startDate || $endDate) {
            $start = null;
            $end = null;

            if ($startDate) {
                try {
                    $start = Carbon::parse($startDate)->startOfDay();
                    $responsesQuery->where('created_at', '>=', $start);
                } catch (\Exception $e) {
                    // Invalid date, skip
                }
            }

            if ($endDate) {
                try {
                    $end = Carbon::parse($endDate)->endOfDay();
                    $responsesQuery->where('created_at', '<=', $end);
                } catch (\Exception $e) {
                    // Invalid date, skip
                }
            }
        }

        // Get all responses first
        $allResponses = $responsesQuery->orderBy('created_at')->get();

        \Log::info('ExportResponses - All responses fetched', [
            'evaluation_id' => $evaluation->id,
            'total_count' => $allResponses->count(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        // Apply academic_year filter in PHP if needed
        if ($academicYear && $academicYear !== 'all') {
            $allResponses = $allResponses->filter(function ($response) use ($academicYear) {
                return $response->evaluation && $response->evaluation->academic_year === $academicYear;
            })->values();
            
            \Log::info('ExportResponses - After academic_year filter', [
                'academic_year' => $academicYear,
                'count' => $allResponses->count()
            ]);
        }

        // Apply semester filter in PHP if needed
        if ($semester && $semester !== 'all') {
            $allResponses = $allResponses->filter(function ($response) use ($semester) {
                return $response->evaluation && $response->evaluation->semester === $semester;
            })->values();
            
            \Log::info('ExportResponses - After semester filter', [
                'semester' => $semester,
                'count' => $allResponses->count()
            ]);
        }

        // Apply subject_type filter in PHP if needed
        if ($subjectType && $subjectType !== 'all') {
            $beforeSubjectFilter = $allResponses->count();
            $allResponses = $allResponses->filter(function ($response) use ($subjectType) {
                $course = $response->schedule?->facultyCourse?->course;
                $courseSubjectType = $course?->subject_type;
                \Log::debug('ExportResponses - Checking subject_type', [
                    'response_id' => $response->id,
                    'course_subject_type' => $courseSubjectType,
                    'filter_subject_type' => $subjectType,
                    'match' => $courseSubjectType === $subjectType
                ]);
                return $course && $courseSubjectType === $subjectType;
            })->values();
            
            \Log::info('ExportResponses - After subject_type filter', [
                'subject_type' => $subjectType,
                'before_count' => $beforeSubjectFilter,
                'after_count' => $allResponses->count()
            ]);
        }

        $responses = $allResponses;

        $facultySlug = Str::slug($evaluation->resolved_faculty_name, '_');
        $dateTag = now()->format('Ymd_His');
        $filename = "evaluation_responses_{$facultySlug}_{$dateTag}.csv";

        return response()->streamDownload(function () use ($responses, $evaluation) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Faculty Name',
                'Faculty Department',
                'Academic Year',
                'Semester',
                'Course Code',
                'Course Name',
                'Effectiveness Rating',
                'Effectiveness Text',
                'Feedback Comments',
            ]);

            foreach ($responses as $response) {
                fputcsv($handle, [
                    $evaluation->resolved_faculty_name,
                    $evaluation->resolved_faculty_department,
                    $evaluation->academic_year,
                    $evaluation->semester,
                    $response->resolved_course_code,
                    $response->resolved_course_name,
                    $response->effectiveness_rating,
                    $response->effectiveness_text,
                    $response->feedback_comments,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function convertToShortUrls()
    {
        $evaluations = Evaluation::all();
        $converted = 0;

        foreach ($evaluations as $evaluation) {
            if (strpos($evaluation->form_link, '/evaluation/') !== false) {
                $urlParts = explode('/', $evaluation->form_link);
                $token = end($urlParts);
                $shortUrl = url("/eval/{$token}");
                $evaluation->update(['form_link' => $shortUrl]);
                $converted++;
            }
        }

        return back()->with('success', "Successfully converted {$converted} evaluation URLs to short format for QR code compatibility.");
    }

    public function checkCooldown(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $ipAddress = $request->ip();
        $scheduleId = $request->schedule_id;
        $cooldownMinutes = 1;

        $isInCooldown = EvaluationResponse::isInCooldown($scheduleId, $ipAddress, $cooldownMinutes);
        $remainingSeconds = EvaluationResponse::getRemainingCooldown($scheduleId, $ipAddress, $cooldownMinutes);

        return response()->json([
            'in_cooldown' => $isInCooldown,
            'remaining_seconds' => $remainingSeconds,
            'remaining_time' => $remainingSeconds > 0 ? gmdate("i:s", $remainingSeconds) : '00:00'
        ]);
    }

    private function buildFacultySnapshot(User $user, ?Faculty $facultyProfile = null): array
    {
        $departmentList = array_merge(
            Faculty::normalizeDepartmentList($user->department ?? ''),
            Faculty::normalizeDepartmentList($facultyProfile?->department ?? '')
        );

        $departmentList = array_values(array_unique($departmentList));
        $department = $departmentList === []
            ? null
            : Faculty::serializeDepartmentList($departmentList);

        return [
            'faculty_name_snapshot' => $user->name,
            'faculty_email_snapshot' => $user->email,
            'faculty_department_snapshot' => $department,
        ];
    }

    private function enforceDepartmentAccess(Faculty $facultyProfile): void
    {
        $user = auth()->user();
        if (!$user || $user->role === 'Admin') {
            return;
        }

        $department = trim($user->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);
        if (empty($departmentFilters)) {
            abort(404);
        }

        $facultyDepartments = Faculty::normalizeDepartmentList($facultyProfile->department ?? '');
        $allowed = collect($departmentFilters)->intersect($facultyDepartments);
        if ($allowed->isEmpty()) {
            abort(404);
        }
    }

    private function enforceEvaluationAccess(Evaluation $evaluation): void
    {
        $user = auth()->user();
        if (!$user || $user->role === 'Admin') {
            return;
        }
        $accessLevels = collect($user->access_level ?? []);
        if ($accessLevels->contains('View All Reports')) {
            return;
        }

        $department = trim($user->department ?? '');
        if ($department === '') {
            $department = trim(optional($user?->faculty)->department ?? '');
        }
        $departmentFilters = Faculty::normalizeDepartmentList($department);
        if (empty($departmentFilters)) {
            abort(404);
        }

        $evaluationDepartments = Faculty::normalizeDepartmentList(
            $evaluation->resolved_faculty_department ?? $evaluation->faculty?->department ?? ''
        );
        $allowed = collect($departmentFilters)->intersect($evaluationDepartments);
        if ($allowed->isEmpty()) {
            abort(404);
        }
    }
}
