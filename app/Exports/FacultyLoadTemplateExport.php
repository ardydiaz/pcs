<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FacultyLoadTemplateExport implements FromArray, WithHeadings
{
    public function __construct(private readonly array $rows)
    {
    }

    public function headings(): array
    {
        return [
            'employeeno',
            'classcode',
            'section',
            'academicyear',
            'semester',
            'time',
            'day',
            'subjectcode',
            'subjecttype',
            'status',
            'fullname',
            'department',
            'jobtitle',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
