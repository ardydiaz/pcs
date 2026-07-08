<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EvaluationQrLinksExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly array $rows)
    {
    }

    public function headings(): array
    {
        return [
            'Faculty Name',
            'Department',
            'Program',
            'Academic Year',
            'Semester',
            'Evaluation QR Link',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }
}
