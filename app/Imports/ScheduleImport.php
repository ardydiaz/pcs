<?php

namespace App\Imports;

use App\Models\Faculty;
use App\Models\Course;
use App\Models\FacultyCourse;
use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleImport implements ToModel, WithHeadingRow
{
    private array $errors = [];
    public function model(array $row)
    {   
        //dd($row);
        $employeeNo = isset($row['employeeno']) ? trim((string) $row['employeeno']) : '';
        if ($employeeNo === '') {
            $this->logError($row, 'Employee number is empty');
            return null;
        }

        // 1. Find faculty by employeeno
        $faculty = Faculty::where('employee_no', $employeeNo)->first();

        if (!$faculty) {
           $this->logError($row, 'Faculty not found');
            return null;
        }

        $classCode = isset($row['classcode']) ? trim((string) $row['classcode']) : '';
        if ($classCode === '') {
           $this->logError($row, 'Class code is empty');
            return null;
        }

        // 2. Find course by coursecode
        $course = Course::where('class_code', $classCode)->first();

        if (!$course) {
            $this->logError($row, 'Course not found');
            return null;
        }

        // 3. Find faculty_course that matches both faculty & course
        $facultyCourseQuery = FacultyCourse::where('faculty_id', $faculty->id)
            ->where('course_id', $course->id);

        $section = isset($row['section']) ? trim((string) $row['section']) : '';
        $academicYear = $this->normalizeAcademicYear($row['academicyear'] ?? '');
        $semester = $this->normalizeSemester($row['semester'] ?? '');

        if ($section !== '') {
            $facultyCourseQuery->where('section', $section);
        }
        if ($academicYear !== '') {
            $facultyCourseQuery->where('academic_year', $academicYear);
        }
        if ($semester !== '') {
            $facultyCourseQuery->where('semester', $semester);
        }

        $matches = $facultyCourseQuery->get();
        if ($matches->count() !== 1) {
           $this->logError($row, 'FacultyCourse not found or multiple matches');
            return null;
        }
        $facultyCourse = $matches->first();

        $time = $this->normalizeTime((string) ($row['time'] ?? ''));
        $day = $this->normalizeDay((string) ($row['day'] ?? ''));

        if ($time === '' || $day === '') {
            $this->logError($row, 'Invalid time or day format');
            return null;
        }

        $exists = Schedule::where('faculty_course_id', $facultyCourse->id)
            ->where('time', $time)
            ->where('day', $day)
            ->exists();

        if ($exists) {
             $this->logError($row, 'Schedule already exists');
            return null;
        }

        // 4. Insert schedule
        return new Schedule([
            'faculty_course_id' => $facultyCourse->id,
            'time' => $time,
            'day' => $day,
            'status' => $row['status'] ?? 'scheduled',
        ]);
    }
    private function logError($row, $message)
    {
        $this->errors[] = [
            'row' => $row,
            'message' => $message,
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function normalizeTime(string $time): string
    {
        $normalized = preg_replace('/\s*to\s*/i', ' - ', $time);
        return preg_replace('/\s+/', ' ', trim($normalized));
    }

    private function normalizeDay(string $day): string
    {
        $value = strtoupper(trim($day));
        if ($value === '') {
            return '';
        }

        $cleaned = preg_replace('/[^A-Z]/', '', $value);
        $tokens = [];
        $i = 0;
        $length = strlen($cleaned);
        $allowedSingles = ['M', 'T', 'W', 'F', 'S'];

        while ($i < $length) {
            $pair = substr($cleaned, $i, 2);
            if ($pair === 'TH' || $pair === 'SU') {
                $tokens[] = $pair;
                $i += 2;
                continue;
            }
            $char = $cleaned[$i];
            if (in_array($char, $allowedSingles, true)) {
                $tokens[] = $char;
                $i += 1;
                continue;
            }
            return '';
        }

        return implode('', $tokens);
    }

    private function normalizeSemester($semester): string
    {
        $value = strtolower(preg_replace('/[^a-z0-9]+/', '', trim((string) $semester)));

        return match ($value) {
            '1', '1st', 'first', 'firstsem', 'firstsemester', 'semester1' => '1st',
            '2', '2nd', 'second', 'secondsem', 'secondsemester', 'semester2' => '2nd',
            '3', '3rd', 'summer', 'summersem', 'summersemester', 'midyear' => 'Summer',
            default => '',
        };
    }

    private function normalizeAcademicYear($academicYear): string
    {
        $normalized = preg_replace('/\s*-\s*/', '-', trim((string) $academicYear));

        return preg_replace('/\s+/', '', $normalized);
    }
}
