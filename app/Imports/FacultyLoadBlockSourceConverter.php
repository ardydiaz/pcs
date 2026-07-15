<?php

namespace App\Imports;

use App\Models\Faculty;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FacultyLoadBlockSourceConverter
{
    private array $facultyIndex;

    public function __construct(private readonly array $defaults)
    {
        $this->facultyIndex = $this->buildFacultyIndex();
    }

    public function convert(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $rows = [];
        $currentFaculty = '';
        $lastCourse = null;

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $highestRow = $sheet->getHighestDataRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            for ($rowNumber = 1; $rowNumber <= $highestRow; $rowNumber++) {
                $row = $this->readRow($sheet, $rowNumber, $highestColumnIndex);

                if ($this->isBlankRow($row)) {
                    continue;
                }

                if ($this->isHeaderRow($row)) {
                    continue;
                }

                if ($this->isTotalRow($row)) {
                    $currentFaculty = '';
                    $lastCourse = null;
                    continue;
                }

                if ($row['faculty'] !== '') {
                    $currentFaculty = $row['faculty'];
                }

                $course = [
                    'section' => $row['section'],
                    'code' => $row['subject_code'],
                    'description' => $row['description'],
                    'days' => $row['days'],
                    'time_from' => $row['time_from'],
                    'time_to' => $row['time_to'],
                ];

                if ($course['section'] === '' && $course['code'] === '' && $course['description'] === '' && $lastCourse) {
                    $course['section'] = $lastCourse['section'];
                    $course['code'] = $lastCourse['code'];
                    $course['description'] = $lastCourse['description'];
                    $course['days'] = $course['days'] !== '' ? $course['days'] : $lastCourse['days'];
                }

                if ($course['section'] === '' || $course['code'] === '' || $course['description'] === '') {
                    continue;
                }

                $lastCourse = $course;
                $subjectCode = $this->formatSubjectCode($course['code'], $course['description']);

                $rows[] = [
                    'employeeno' => $this->employeeNoFor($currentFaculty),
                    'classcode' => $course['code'],
                    'section' => $course['section'],
                    'academicyear' => $this->defaults['academic_year'],
                    'semester' => $this->defaults['semester'],
                    'time' => $this->formatTimeRange($course['time_from'], $course['time_to']),
                    'day' => $this->normalizeDays($course['days']),
                    'subjectcode' => $subjectCode,
                    'subjecttype' => $this->defaults['subject_type'],
                    'status' => $this->defaults['status'],
                    'fullname' => $currentFaculty,
                    'department' => $this->defaults['department'] ?? '',
                    'jobtitle' => $this->defaults['job_title'] ?? '',
                ];
            }
        }

        return $rows;
    }

    private function readRow($sheet, int $rowNumber, int $highestColumnIndex): array
    {
        $values = [];
        for ($column = 1; $column <= max($highestColumnIndex, 10); $column++) {
            $values[$column] = $this->cleanValue($sheet->getCell([$column, $rowNumber])->getFormattedValue());
        }

        return [
            'faculty' => $values[1] ?? '',
            'section' => $values[2] ?? '',
            'subject_code' => $values[3] ?? '',
            'description' => $values[4] ?? '',
            'units' => $values[5] ?? '',
            'days' => $values[6] ?? '',
            'time_from' => $values[7] ?? '',
            'time_to' => $values[8] ?? '',
        ];
    }

    private function isHeaderRow(array $row): bool
    {
        return $this->key($row['faculty']) === 'facultyname'
            && $this->key($row['section']) === 'section'
            && $this->key($row['subject_code']) === 'subjectcode';
    }

    private function isTotalRow(array $row): bool
    {
        return in_array($this->key($row['section']), ['total'], true)
            || in_array($this->key($row['description']), ['total'], true);
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

    private function formatSubjectCode(string $code, string $description): string
    {
        $code = $this->cleanValue($code);
        $description = $this->cleanValue($description);

        if ($description === '') {
            return $code;
        }

        if (str_starts_with(strtolower($description), strtolower($code))) {
            return $description;
        }

        return trim($code . ' - ' . $description);
    }

    private function formatTimeRange(string $from, string $to): string
    {
        $from = $this->normalizeTime($from);
        $to = $this->normalizeTime($to);

        if ($from === '' && $to === '') {
            return '';
        }

        return trim($from . ' - ' . $to, ' -');
    }

    private function normalizeTime(string $time): string
    {
        $time = strtoupper($this->cleanValue($time));
        $time = str_replace([';', 'NN'], [':', 'PM'], $time);

        if (preg_match('/^(\d{1,2}):(\d{2})(?:\s*)?(AM|PM)?$/', $time, $match)) {
            $hour = (int) $match[1];
            $minute = $match[2];
            $period = $match[3] ?? '';
            $suffix = $period === 'PM' ? 'p' : ($period === 'AM' ? 'a' : '');

            return sprintf('%02d:%s%s', $hour, $minute, $suffix);
        }

        return strtolower($time);
    }

    private function normalizeDays(string $days): string
    {
        $days = strtoupper($this->cleanValue($days));
        $days = preg_replace('/[^A-Z,]+/', '', $days);

        if ($days === '') {
            return '';
        }

        $wordMap = [
            'MONDAY' => 'M',
            'MON' => 'M',
            'TUESDAY' => 'T',
            'TUE' => 'T',
            'WEDNESDAY' => 'W',
            'WED' => 'W',
            'THURSDAY' => 'TH',
            'THU' => 'TH',
            'FRIDAY' => 'F',
            'FRI' => 'F',
            'SATURDAY' => 'S',
            'SAT' => 'S',
            'SUNDAY' => 'SU',
            'SUN' => 'SU',
        ];

        if (isset($wordMap[$days])) {
            return $wordMap[$days];
        }

        return str_replace(',', '', $days);
    }

    private function employeeNoFor(string $facultyName): string
    {
        $facultyTokens = $this->nameTokens($facultyName);

        if (empty($facultyTokens)) {
            return '';
        }

        $matches = [];
        foreach ($this->facultyIndex as $faculty) {
            $intersection = array_intersect($facultyTokens, $faculty['tokens']);
            if (count($intersection) >= min(2, count($facultyTokens))) {
                $matches[] = $faculty['employee_no'];
            }
        }

        $matches = array_values(array_unique($matches));

        return count($matches) === 1 ? $matches[0] : '';
    }

    private function buildFacultyIndex(): array
    {
        return Faculty::with('user:id,name')
            ->get(['id', 'user_id', 'employee_no'])
            ->map(function (Faculty $faculty) {
                return [
                    'employee_no' => (string) $faculty->employee_no,
                    'tokens' => $this->nameTokens($faculty->user?->name ?? ''),
                ];
            })
            ->filter(fn (array $faculty) => $faculty['employee_no'] !== '' && !empty($faculty['tokens']))
            ->values()
            ->all();
    }

    private function nameTokens(string $name): array
    {
        $name = strtolower($this->cleanValue($name));
        $name = preg_replace('/[^a-z0-9 ]+/', ' ', $name);

        return collect(preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn ($token) => strlen($token) <= 1 || in_array($token, ['jr', 'sr', 'ii', 'iii', 'iv'], true))
            ->unique()
            ->values()
            ->all();
    }

    private function cleanValue($value): string
    {
        $value = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', (string) $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value ?? '');
    }

    private function key(string $value): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($value));
    }
}
