<?php

namespace App\Http\Controllers\data_management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Evaluation, EvaluationResponse, Schedule, User, FacultyCourse, Faculty};
use Illuminate\Support\Str;
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
        $semesterQuery = FacultyCourse::query();

        if ($shouldFilter) {
            $academicYearQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
                $query->forDepartments($departmentFilters);
            });
            $semesterQuery->whereHas('faculty', function ($query) use ($departmentFilters) {
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

        $semesterOptions = $semesterQuery
            ->whereNotNull('semester')
            ->where('semester', '!=', '')
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester')
            ->map(function ($value) {
                return [
                    'value' => $value,
                    'label' => $this->formatSemesterLabel($value),
                ];
            })
            ->values();

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

            // Skip if evaluation already exists
            $exists = $this->findExistingEvaluation(
                $user->id,
                $request->academic_year,
                $normalizedSemester
            );

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
        $message = "Generated {$generated} new evaluation forms.";

        if ($skipped > 0) {
            $skippedNames = implode(', ', $skippedFaculties);
            $message .= " Skipped {$skipped} faculties: {$skippedNames}";
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

        $evaluations = Evaluation::where('faculty_id', $facultyId)
            ->where('academic_year', $academicYear)
            ->get();

        foreach ($evaluations as $evaluation) {
            if ($this->normalizeSemesterValue($evaluation->semester) === $normalizedSemester) {
                return $evaluation;
            }
        }

        return null;
    }

    public function toggleStatus(Request $request, Evaluation $evaluation)
    {
        $evaluation->update(['is_active' => !$evaluation->is_active]);
        $status = $evaluation->is_active ? 'activated' : 'deactivated';

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
        $evaluation->delete();

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

        $deleted = Evaluation::whereIn('id', $ids)->delete();

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

    public function viewResponses(Evaluation $evaluation)
    {
        $this->enforceEvaluationAccess($evaluation);
        $responses = EvaluationResponse::with(['schedule.facultyCourse.course'])
            ->where('evaluation_id', $evaluation->id)
            ->latest()
            ->get();

        $coursesEvaluatedCount = $this->countUniqueCoursesFromResponses($responses);

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
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $responsesQuery = EvaluationResponse::with(['schedule.facultyCourse.course'])
            ->where('evaluation_id', $evaluation->id);

        if ($academicYear !== 'all' && $academicYear !== $evaluation->academic_year) {
            $responsesQuery->whereRaw('1 = 0');
        }

        if ($semester !== 'all' && $semester !== $evaluation->semester) {
            $responsesQuery->whereRaw('1 = 0');
        }

        $start = null;
        $end = null;

        if ($startDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
            } catch (\Exception $e) {
                $start = null;
            }
        }

        if ($endDate) {
            try {
                $end = Carbon::parse($endDate)->endOfDay();
            } catch (\Exception $e) {
                $end = null;
            }
        }

        if ($start && $end && $end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        if ($start) {
            $responsesQuery->where('created_at', '>=', $start);
        }

        if ($end) {
            $responsesQuery->where('created_at', '<=', $end);
        }

        $responses = $responsesQuery->orderBy('created_at')->get();

        $facultySlug = Str::slug($evaluation->resolved_faculty_name, '_');
        $dateTag = now()->format('Ymd_His');
        $filename = "evaluation_responses_{$facultySlug}_{$dateTag}.csv";

        return response()->streamDownload(function () use ($responses, $evaluation) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Faculty Name',
                'Faculty Email',
                'Faculty Department',
                'Academic Year',
                'Semester',
                'Course Code',
                'Course Name',
                'Schedule Time',
                'Schedule Days',
                'Effectiveness Rating',
                'Effectiveness Text',
                'Feedback Comments',
                'Response Submitted At',
                'Evaluation Link',
                'Evaluation Created At',
            ]);

            foreach ($responses as $response) {
                fputcsv($handle, [
                    $evaluation->resolved_faculty_name,
                    $evaluation->resolved_faculty_email,
                    $evaluation->resolved_faculty_department,
                    $evaluation->academic_year,
                    $evaluation->semester,
                    $response->resolved_course_code,
                    $response->resolved_course_name,
                    $response->resolved_schedule_time,
                    $response->resolved_schedule_days,
                    $response->effectiveness_rating,
                    $response->effectiveness_text,
                    $response->feedback_comments,
                    optional($response->created_at)->toDateTimeString(),
                    $evaluation->form_link,
                    optional($evaluation->created_at)->toDateTimeString(),
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
