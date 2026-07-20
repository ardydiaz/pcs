<?php

namespace App\Imports;

use App\Models\Faculty;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FacultyLoadSourceConverter implements ToCollection, WithHeadingRow
{
    private array $rows = [];
    private array $facultyDepartmentsByEmployeeNo = [];

    public function __construct(private readonly array $defaults)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $source = is_array($row) ? $row : $row->toArray();

            if ($this->isBlankRow($source)) {
                continue;
            }

            $classCode = $this->cellAny($source, ['classcode', 'class_code']);
            $subjectCode = $this->cellAny($source, ['subjectcode', 'subject_code']);
            $section = $this->cellAny($source, ['section']);
            $information = $this->cellAny($source, ['information']);
            $employeeNo = $this->cellAny($source, ['employeeno', 'employee_no', 'emp_no', 'empno']);
            $department = $this->cellAny($source, ['department']) ?: ($this->defaults['department'] ?? '');
            $jobTitle = $this->cellAny($source, ['jobtitle', 'job_title']) ?: ($this->defaults['job_title'] ?? '');

            if ($classCode === '' && $subjectCode === '' && $section === '' && $information === '') {
                continue;
            }

            if (($this->defaults['skip_blank_employee_no'] ?? false) && $employeeNo === '') {
                continue;
            }

            if ($this->shouldSkipForDepartment($employeeNo, $department)) {
                continue;
            }

            $parsedInformation = $this->parseInformation($information);

            $this->rows[] = [
                'employeeno' => $employeeNo,
                'classcode' => $classCode,
                'section' => $section,
                'academicyear' => $this->defaults['academic_year'],
                'semester' => $this->defaults['semester'],
                'time' => $parsedInformation['time'],
                'day' => $parsedInformation['day'],
                'subjectcode' => $subjectCode,
                'subjecttype' => $this->defaults['subject_type'],
                'status' => $this->defaults['status'],
                'fullname' => $this->cellAny($source, ['faculty', 'name', 'fullname', 'full_name']),
                'department' => $department,
                'jobtitle' => $jobTitle,
            ];
        }
    }

    public function rows(): array
    {
        return $this->rows;
    }

    private function parseInformation(string $information): array
    {
        $result = ['time' => '', 'day' => ''];

        if ($information === '') {
            return $result;
        }

        if (!preg_match('/(\d{1,2}:\d{2}\s*[ap]m?\s*(?:-|to)\s*\d{1,2}:\d{2}\s*[ap]m?)/i', $information, $timeMatch, PREG_OFFSET_CAPTURE)) {
            return $result;
        }

        $result['time'] = $this->normalizeTime($timeMatch[1][0]);
        $afterTime = trim(substr($information, $timeMatch[0][1] + strlen($timeMatch[0][0])));

        if (preg_match('/^((?:TH|SU|M|T|W|F|S)+)\b/i', $afterTime, $dayMatch)) {
            $result['day'] = strtoupper($dayMatch[1]);
        }

        return $result;
    }

    private function normalizeTime(string $time): string
    {
        $time = preg_replace('/\s*to\s*/i', ' - ', $time);
        $time = preg_replace('/\s*-\s*/', ' - ', $time);

        return preg_replace('/\s+/', ' ', trim($time));
    }

    private function cellAny(array $row, array $keys): string
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = $this->cleanValue($value);
        }

        foreach ($keys as $key) {
            $value = $normalized[$this->normalizeKey($key)] ?? '';
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function shouldSkipForDepartment(string $employeeNo, string $targetDepartment): bool
    {
        $targetDepartment = trim($targetDepartment);
        if ($employeeNo === '' || $targetDepartment === '') {
            return false;
        }

        $facultyDepartments = $this->facultyDepartmentsFor($employeeNo);
        if (empty($facultyDepartments)) {
            return false;
        }

        return count($facultyDepartments) !== 1 || $facultyDepartments[0] !== $targetDepartment;
    }

    private function facultyDepartmentsFor(string $employeeNo): array
    {
        $key = Faculty::normalizeEmployeeNo($employeeNo);
        if ($key === '') {
            return [];
        }

        if (!array_key_exists($key, $this->facultyDepartmentsByEmployeeNo)) {
            $faculty = Faculty::findByEmployeeNoIncludingTrashed($employeeNo);
            $this->facultyDepartmentsByEmployeeNo[$key] = Faculty::normalizeDepartmentList($faculty?->department);
        }

        return $this->facultyDepartmentsByEmployeeNo[$key];
    }

    private function normalizeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($key));
    }

    private function cleanValue($value): string
    {
        $value = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', (string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value ?? '');
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanValue($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
