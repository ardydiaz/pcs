<?php

namespace App\Imports; // Namespace declaration for the import classes, indicating that these classes are part of the App\Imports namespace, which is a common convention in Laravel applications to organize code related to data imports.

use App\Models\User; // Relationship with users using their user_id as foreign key
use App\Models\Faculty; // Relationship with faculties to create new Faculty records based on the imported data
use App\Models\Course; // Import Course model to create course records based on the imported data
use App\Models\FacultyCourse; // Import FacultyCourse model to create records that link faculties to courses with specific sections, academic years, and semesters
use App\Models\Schedule; // Import Schedule model to create schedule records based on the imported data
use Maatwebsite\Excel\Concerns\ToModel; // Interface to convert each row of the Excel file into a model instance
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Interface to indicate that the first row of the Excel file contains column headings, allowing us to access row data using those headings as keys
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class ImportAll implements ToModel, WithHeadingRow // Class to handle the import of all related data from an Excel file
{
    use RemembersRowNumber;

    protected $skippedRecords = []; // Array to track skipped records during import, such as those with missing required fields or duplicates
    protected $importedCount = 0; // Counter to track the number of successfully imported records, incremented each time a new faculty, course, and schedule record is created
    protected $debugLog = []; // Store detailed debug information for troubleshooting

    // Method to retrieve the list of skipped records, which can be used for reporting or debugging purposes after the import process is complete
    public function getSkippedRecords(): array 
    {
        return $this->skippedRecords;
    }

    // Method to retrieve the count of successfully imported records, which can be used for reporting or feedback purposes after the import process is complete
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    // Method to retrieve debug log for detailed troubleshooting
    public function getDebugLog(): array
    {
        return $this->debugLog;
    }

    public function model(array $row) // Convert each row of the Excel file into a model instance, while also creating linked FacultyCourse and Schedule records based on the imported data
    {
         //dd($row);
        // Extract and validate employee number from the row
        $employeeNo = $this->cell($row, 'employeeno');
        if ($employeeNo === '' || strlen($employeeNo) < 2) {
            $this->skipRow($row, $employeeNo === '' ? 'Missing employee number' : 'Invalid employee number');
            $this->debugLog[] = [
                'action' => 'Row skipped',
                'reason' => 'Invalid employee number',
                'row_data' => $row
            ];
            return null;
        }

        // Extract and validate course details from the row
        $classCode = $this->cell($row, 'classcode');
        $subjectCode = $this->cell($row, 'subjectcode');
        $subjectType = strtolower($this->cell($row, 'subjecttype', 'major'));
        $section = $this->cell($row, 'section');
        $academicYear = $this->normalizeAcademicYear($this->cell($row, 'academicyear'));
        $semester = $this->normalizeSemester($row['semester'] ?? '');

        // Validate required course fields (subject_code is NOT NULL in database)
        if ($classCode === '' || $subjectCode === '' || $section === '' || $academicYear === '' || $semester === '') {
            $this->skipRow($row, 'Missing required course details');
            return null;
        }

        if (!in_array($subjectType, ['major', 'minor'], true)) {
            $this->skipRow($row, 'Invalid subject type: ' . $subjectType . '. Must be major or minor');
            return null;
        }

        // Validate academic year format (should be something like "2023-2024")
        if (!$this->isValidAcademicYear($academicYear)) {
            $this->skipRow($row, 'Invalid academic year format: ' . $academicYear . '. Use YYYY-YYYY, for example 2025-2026');
            return null;
        }

        // Extract schedule details
        $time = $this->cell($row, 'time');
        $day = $this->cell($row, 'day');
        $status = strtolower($this->cell($row, 'status', 'scheduled'));

        // Validate status matches enum values in database
        if (!$this->isValidStatus($status)) {
            $this->skipRow($row, 'Invalid status: ' . $status . '. Must be one of: scheduled, completed, cancelled');
            return null;
        }

        // Validate and normalize day abbreviations
        if ($day !== '' && !Schedule::isOpenHourValue($day) && !$this->isValidDayFormat($day)) {
            $this->skipRow($row, 'Invalid day format: ' . $day);
            return null;
        }
        $day = $day === '' || Schedule::isOpenHourValue($day) ? null : $this->normalizeDayInput($day);

        // Normalize time input to standard format (convert "to" to "-")
        $time = $this->normalizeTimeInput($time);

        try {
            // 1. Find or create Course
            $course = Course::where('class_code', $classCode)
                ->where('subject_code', $subjectCode)
                ->where('subject_type', $subjectType)
                ->first();
            if (!$course) {
                $course = Course::create([
                    'class_code' => $classCode,
                    'subject_code' => $subjectCode,
                    'subject_type' => $subjectType,
                ]);
            }
            
            // Validate course was created/found successfully
            if (!$course || !$course->id) {
                $this->skipRow($row, 'Failed to create or find course');
                return null;
            }

            // 2. Find existing Faculty or create new one if optional fields provided
            $faculty = Faculty::findByEmployeeNoIncludingTrashed($employeeNo);
            $name = $this->cell($row, 'name') ?: $this->cell($row, 'fullname');
            $department = $this->cell($row, 'department');
            $jobTitle = $this->cell($row, 'jobtitle');
            $normalizedDepartment = $department === ''
                ? null
                : Faculty::serializeDepartmentList(Faculty::normalizeDepartmentList($department));

            if (!$faculty) {
                // Create faculty even when optional profile details are missing.
                $user = null;
                if ($name !== '') {
                    $user = User::create([
                        'name' => $name,
                        'department' => $normalizedDepartment,
                        'job_title' => $jobTitle === '' ? null : $jobTitle,
                        'role' => 'Faculty',
                        'status' => 'Active',
                    ]);
                }

                $faculty = Faculty::create([
                    'user_id' => $user?->id,
                    'employee_no' => $employeeNo,
                    'department' => $normalizedDepartment,
                    'job_title' => $jobTitle === '' ? null : $jobTitle,
                ]);
            } else {
                if ($faculty->trashed()) {
                    $faculty->restore();
                }

                $normalizedDepartment = $normalizedDepartment ?? $faculty->department;
                if (!$faculty->user && $name !== '') {
                    $user = User::create([
                        'name' => $name,
                        'department' => $normalizedDepartment,
                        'job_title' => $jobTitle === '' ? null : $jobTitle,
                        'role' => 'Faculty',
                        'status' => 'Active',
                    ]);

                    $faculty->user_id = $user->id;
                }

                if ($normalizedDepartment !== null) {
                    $faculty->department = $normalizedDepartment;
                }
                if ($jobTitle !== '') {
                    $faculty->job_title = $jobTitle;
                }
                $faculty->save();

                if ($faculty->user) {
                    $userUpdates = ['status' => 'Active'];
                    if ($normalizedDepartment !== null) {
                        $userUpdates['department'] = $normalizedDepartment;
                    }
                    if ($jobTitle !== '') {
                        $userUpdates['job_title'] = $jobTitle;
                    }
                    if (trim((string) $faculty->user->name) === '' && $name !== '') {
                        $userUpdates['name'] = $name;
                    } elseif ($name !== '' && strcasecmp($faculty->user->name, $name) !== 0) {
                        $this->debugLog[] = [
                            'action' => 'Faculty name mismatch ignored',
                            'employee_no' => $employeeNo,
                            'existing_name' => $faculty->user->name,
                            'incoming_name' => $name,
                        ];
                    }
                    $faculty->user->update($userUpdates);
                }
            }
            
            // Validate faculty exists and has valid ID
            if (!$faculty || !$faculty->id) {
                $this->skipRow($row, 'Failed to create or find faculty');
                return null;
            }

            // 3. Find or create FacultyCourse linking record
            $facultyCourse = FacultyCourse::withTrashed()
                ->where('faculty_id', $faculty->id)
                ->where('course_id', $course->id)
                ->where('section', $section)
                ->where('academic_year', $academicYear)
                ->where('semester', $semester)
                ->first();

            if ($facultyCourse && $facultyCourse->trashed()) {
                $facultyCourse->restore();
            } elseif (!$facultyCourse) {
                $facultyCourse = FacultyCourse::create([
                    'faculty_id' => $faculty->id,
                    'course_id' => $course->id,
                    'section' => $section,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                ]);
            }
            
            // Validate FacultyCourse was created/found and can be queried back
            if (!$facultyCourse || !$facultyCourse->id) {
                $this->skipRow($row, 'Failed to create or find faculty course assignment');
                return null;
            }
            
            // Verify FacultyCourse can be queried back (critical for EvaluationController validation)
            // Include course_id for precise query matching
            $verifyFC = FacultyCourse::where('faculty_id', $faculty->id)
                ->where('course_id', $course->id)
                ->where('section', $section)
                ->where('academic_year', $academicYear)
                ->where('semester', $semester)
                ->first();
            
            if (!$verifyFC) {
                $this->skipRow($row, 'Faculty course assignment query verification failed');
                return null;
            }

            // 4. Find, update, or create Schedule record.
            // When a re-upload leaves time blank, treat the Excel row as an update
            // to the existing same-day schedule instead of creating a duplicate.
            $schedule = null;

            if ($time === null && $day !== null) {
                $schedule = Schedule::where('faculty_course_id', $facultyCourse->id)
                    ->where('day', $day)
                    ->orderBy('id')
                    ->first();

                if ($schedule) {
                    $schedule->update([
                        'time' => null,
                        'status' => $status,
                    ]);
                    $this->debugLog[] = [
                        'action' => 'Schedule time cleared',
                        'employee_no' => $employeeNo,
                        'schedule_id' => $schedule->id,
                        'faculty_course_id' => $facultyCourse->id,
                        'day' => $day,
                        'status' => $status,
                    ];
                }
            }

            if (!$schedule) {
                $schedule = Schedule::where('faculty_course_id', $facultyCourse->id)
                    ->where('time', $time)
                    ->where('day', $day)
                    ->first();
            }

            if (!$schedule) {
                try {
                    $schedule = Schedule::create([
                        'faculty_course_id' => $facultyCourse->id,
                        'time' => $time,
                        'day' => $day,
                        'status' => $status,
                    ]);
                    $this->debugLog[] = [
                        'action' => 'Schedule created',
                        'employee_no' => $employeeNo,
                        'schedule_id' => $schedule->id,
                        'faculty_course_id' => $facultyCourse->id,
                        'time' => $time,
                        'day' => $day,
                        'status' => $status
                    ];
                } catch (\Exception $scheduleError) {
                    $this->skipRow($row, 'Failed to create schedule: ' . $scheduleError->getMessage());
                    return null;
                }
            }
            
            // Validate schedule was created and can be queried back
            if (!$schedule || !$schedule->id) {
                $this->skipRow($row, 'Failed to create or find schedule');
                return null;
            }

            // Verify schedule is queryable (critical for downstream validation)
            $verifySchedule = Schedule::where('faculty_course_id', $facultyCourse->id)
                ->where('time', $time)
                ->where('day', $day)
                ->first();
            
            if (!$verifySchedule) {
                $this->skipRow($row, 'Schedule query verification failed');
                return null;
            }

            $this->importedCount++;
            $this->debugLog[] = [
                'action' => 'Row imported successfully',
                'employee_no' => $employeeNo,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'schedule_id' => $schedule->id
            ];
            return $schedule;

        } catch (\Exception $e) {
            $this->debugLog[] = [
                'action' => 'Exception caught',
                'error' => $e->getMessage(),
                'employee_no' => $employeeNo ?? 'unknown'
            ];
            $this->skipRow($row, 'Error: ' . $e->getMessage());
            return null;
        }
    }

    // Helper function to normalize semester values to standard format matching enum values
    private function normalizeSemester($semester): string
    {
        $value = preg_replace('/[^a-z0-9]+/', '', strtolower($this->cleanValue($semester)));

        return match ($value) {
            '1', '1st', '1stsemester', 'first', 'firstsem', 'firstsemester', 'semester1' => '1st',
            '2', '2nd', '2ndsemester', 'second', 'secondsem', 'secondsemester', 'semester2' => '2nd',
            '3', '3rd', 'summer', 'summersem', 'summersemester', 'midyear' => 'Summer',
            default => '',
        };
    }

    /**
     * Normalize time input to standard format, ensuring consistent storage.
     * Converts various formats like "07:00a to 08:30a" to "07:00a - 08:30a"
     */
    private function normalizeTimeInput(string $time): ?string
    {
        $normalized = preg_replace('/\s*to\s*/i', ' - ', $time);
        return Schedule::normalizeOpenHourValue($normalized);
    }

    /**
     * Normalize academic year format.
     * Accepts formats like "2023-2024", "2023 - 2024", "2024" or "2025"
     * Returns standardized format: "2023-2024" or "2024"
     */
    private function normalizeAcademicYear(string $academicYear): string
    {
        $academicYear = $this->cleanValue($academicYear);
        
        // Remove all spaces and normalize dashes
        $normalized = preg_replace('/\s*-\s*/', '-', $academicYear);  // "2025 - 2026" → "2025-2026"
        $normalized = preg_replace('/\s+/', '', $normalized);          // Remove any remaining spaces
        
        return $normalized;
    }

    /**
     * Validate academic year format.
     * Accepts formats like "2023-2024", "2024-2025", or just "2024" or "2025"
     */
    private function isValidAcademicYear(string $academicYear): bool
    {
        $academicYear = trim($academicYear);

        if (!preg_match('/^(\d{4})-(\d{4})$/', $academicYear, $matches)) {
            return false;
        }

        return (int) $matches[2] === (int) $matches[1] + 1;
    }

    /**
     * Validate status matches database enum values.
     * Valid values: scheduled, completed, cancelled
     */
    private function isValidStatus(string $status): bool
    {
        $validStatuses = ['scheduled', 'completed', 'cancelled'];
        return in_array(strtolower($status), $validStatuses);
    }

    /**
     * Validate day format contains only valid day abbreviations.
     * Valid abbreviations: M, T, W, TH, F, S, SU
     */
    private function isValidDayFormat(string $day): bool
    {
        return $this->normalizeDayInput($day) !== '';
    }

    private function normalizeDayInput(string $day): string
    {
        $day = strtoupper($this->cleanValue($day));
        if ($day === '') {
            return '';
        }

        $tokens = str_contains($day, ',')
            ? preg_split('/\s*,\s*/', $day, -1, PREG_SPLIT_NO_EMPTY)
            : $this->parseContinuousDays(preg_replace('/\s+/', '', $day));

        $validDays = ['M', 'T', 'W', 'TH', 'F', 'S', 'SU'];
        $normalized = [];

        foreach ($tokens as $token) {
            $token = strtoupper(trim($token));
            if (!in_array($token, $validDays, true)) {
                return '';
            }

            if (!in_array($token, $normalized, true)) {
                $normalized[] = $token;
            }
        }

        return implode(',', $normalized);
    }

    private function parseContinuousDays(string $day): array
    {
        $tokens = [];
        $index = 0;

        while ($index < strlen($day)) {
            $twoChars = substr($day, $index, 2);
            if (in_array($twoChars, ['TH', 'SU'], true)) {
                $tokens[] = $twoChars;
                $index += 2;
                continue;
            }

            $tokens[] = $day[$index];
            $index++;
        }

        return $tokens;
    }

    private function cell(array $row, string $key, string $default = ''): string
    {
        if (!array_key_exists($key, $row) || $row[$key] === null) {
            return $default;
        }

        $value = $this->cleanValue($row[$key]);

        return $value === '' ? $default : $value;
    }

    private function skipRow(array $row, string $reason): void
    {
        $employeeNo = $this->cell($row, 'employeeno');
        $facultyName = $this->cell($row, 'fullname') ?: $this->cell($row, 'name');
        $classCode = $this->cell($row, 'classcode');
        $section = $this->cell($row, 'section');
        $subjectCode = $this->cell($row, 'subjectcode');
        $rowNumber = $this->getRowNumber();

        $summaryParts = [
            'Employee: ' . ($employeeNo === '' ? 'blank' : $employeeNo),
        ];

        if ($facultyName !== '') {
            $summaryParts[] = 'Faculty: ' . $facultyName;
        }

        if ($classCode !== '') {
            $summaryParts[] = 'Class: ' . $classCode;
        }

        if ($section !== '') {
            $summaryParts[] = 'Section: ' . $section;
        }

        if ($subjectCode !== '') {
            $summaryParts[] = 'Subject: ' . $subjectCode;
        }

        $message = 'Row ' . ($rowNumber ?: 'unknown') . ': ' . $reason . ' (' . implode(', ', $summaryParts) . ')';

        $this->skippedRecords[] = [
            'row' => $row,
            'row_number' => $rowNumber,
            'reason' => $reason,
            'message' => $message,
            'employee_no' => $employeeNo,
            'faculty_name' => $facultyName,
            'class_code' => $classCode,
            'section' => $section,
            'subject_code' => $subjectCode,
        ];
    }

    private function cleanValue($value): string
    {
        $value = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', (string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value ?? '');
    }
}
