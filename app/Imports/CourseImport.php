<?php

namespace App\Imports;

use App\Models\Course; // Import Course model to create course records based on the imported data
use App\Models\Faculty; // Import Faculty model to find faculty records based on employee number and create linked FacultyCourse records
use App\Models\FacultyCourse; // Import FacultyCourse model to create records that link faculties to courses with specific sections, academic years, and semesters
use App\Support\SectionNormalizer;
use Maatwebsite\Excel\Concerns\ToModel; // Interface to convert each row of the Excel file into a model instance
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Interface to indicate that the first row of the Excel file contains column headings, allowing us to access row data using those headings as keys
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class CourseImport implements ToModel, WithHeadingRow // Class to handle the import of course data from an Excel file, creating Course records and linking them to Faculty through FacultyCourse records based on the imported data
{  
    use RemembersRowNumber;
    private $errors = [];
    public function model(array $row) // Convert each row of the Excel file into a Course model instance, while also creating linked FacultyCourse records based on the imported data
    {
        // Extract and validate employee number from the row. If it's missing or empty, skip this row by returning null.
       // $employeeNo = isset($row['employeeno']) ? trim((string) $row['employeeno']) : '';

        $rowNumber = $this->getRowNumber();
        // =========================
        // 1. Extract Excel data
        // =========================
        $employeeNo = trim((string) ($row['employeeno'] ?? ''));
        if ($employeeNo === '') {
            $this->errors[] = "Row {$rowNumber}: Missing employee number";
            return null;
        }

        // Extract and validate course details from the row. If any of the required fields are missing or empty, skip this row by returning null.
        $classCode = isset($row['classcode']) ? trim((string) $row['classcode']) : ''; // e.g 2110001, 213039
        $subjectCode = isset($row['subjectcode']) ? trim((string) $row['subjectcode']) : '';  // e.g LRK 002 - Language/Reading, SCK 001 - Science, Computer Programming
        $subjectType = strtolower(trim((string) ($row['subjecttype'] ?? '')));
        $section = SectionNormalizer::normalize($row['section'] ?? ''); // e.g BSN 3, BSCS 1A, BSIT 2B
        $academicYear = isset($row['academicyear']) ? trim((string) $row['academicyear']) : ''; // e.g 2022-2023, 2023-2024
        $semester = $this->normalizeSemester($row['semester'] ?? ''); // Normalize semester value to standard format (e.g 1st Semester, 2nd Semester, Summer)
        

        // =========================
        // 2. Validate required fields
        // =========================
        // If any of the required course details are missing or empty, skip this row by returning null.
        if ($classCode === '' || $section === '' || $academicYear === '' || $semester === '' || $subjectType === '') {
            $this->errors[] =  "Row {$rowNumber}: Incomplete data for employee {$employeeNo}";
            return null;
        }
         // Validate subject type
        if (!in_array($subjectType, ['major', 'minor'])) {
            $this->errors[] =  "Row {$rowNumber}: Invalid subject type for class {$classCode}";
            return null;
        }

        // =========================
        // 3. Course handling
        // =========================
        // Match manual restriction: class_code must be unique.
        $course = Course::withTrashed()->where('class_code', $classCode)->first();
        if (!$course) {
            if ($subjectCode === '') {
                $this->errors[] =  "Row {$rowNumber}: Missing subject code for class {$classCode}";
                return null;
            }
            $course = Course::create([
                'class_code' => $classCode, // e.g 2110001, 213039
                'subject_code' => $subjectCode, // e.g LRK 002 - Language/Reading, SCK 001 - Science, Computer Programming
                'subject_type' => $subjectType,
            ]);
        }else{
            if ($course->trashed()) {
                $course->restore();
            }
            // Update subject_type if changed
            if ($course->subject_type !== $subjectType) {
                $course->update([
                    'subject_type' => $subjectType
                ]);
            }
        }
        

        // =========================
        // 4. Faculty check
        // =========================
        // 2. Find faculty by employee_no
        $faculty = Faculty::findByEmployeeNoIncludingTrashed($employeeNo);
        if (!$faculty) {
            $this->errors[] =  "Row {$rowNumber}: Employee not found: {$employeeNo}";
            return null;
        }

        if ($faculty->trashed()) {
            $faculty->restore();
        }

        // 3. Only assign if faculty exists
        if ($faculty) {
             // =========================
            // 5. FacultyCourse insert
            // =========================
            $facultyCourse = $this->findExistingFacultyCourse(
                $faculty->id,
                $course->id,
                $section,
                $academicYear,
                $semester
            );

            if ($facultyCourse && $facultyCourse->trashed()) {
                $facultyCourse->restore();
            } elseif (!$facultyCourse) {
                FacultyCourse::create([
                    'faculty_id' => $faculty->id,
                    'course_id' => $course->id,
                    'section' => $section,
                    'academic_year' => $academicYear,
                    'semester' => $semester,
                ]);
            } elseif ($facultyCourse->section !== $section) {
                $facultyCourse->update(['section' => $section]);
            }
        }

        return null;
    }



    // =========================
    // Normalize semester
    // ========================
    // Helper function to normalize semester values to a standard format (e.g 1st, 2nd, Summer) for consistent storage and comparison in the database
    private function normalizeSemester($semester): string
    {
        $value = strtolower(trim((string) $semester));
        if ($value === '1st' || $value === '1st semester') {
            return '1st';
        }
        if ($value === '2nd' || $value === '2nd semester') {
            return '2nd';
        }
        if ($value === 'summer') {
            return 'Summer';
        }
        return '';
    }

    // =========================
    // Return errors to controller
    // =========================
    public function getErrors()
    {
        return $this->errors;
    }

    private function findExistingFacultyCourse(
        int $facultyId,
        int $courseId,
        string $section,
        string $academicYear,
        string $semester
    ): ?FacultyCourse {
        $targetSectionKey = SectionNormalizer::key($section);

        return FacultyCourse::withTrashed()
            ->where('faculty_id', $facultyId)
            ->where('course_id', $courseId)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->get()
            ->sortByDesc(function (FacultyCourse $facultyCourse) use ($section) {
                return $facultyCourse->section === $section ? 1 : 0;
            })
            ->first(function (FacultyCourse $facultyCourse) use ($targetSectionKey) {
                return SectionNormalizer::key($facultyCourse->section) === $targetSectionKey;
            });
    }
}
