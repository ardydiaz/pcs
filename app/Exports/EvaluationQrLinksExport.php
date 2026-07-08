<?php

namespace App\Exports;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Throwable;

class EvaluationQrLinksExport implements FromArray, ShouldAutoSize, WithColumnWidths, WithDrawings, WithEvents, WithHeadings
{
    private array $qrImagePaths = [];

    public function __construct(private readonly array $rows)
    {
    }

    public function headings(): array
    {
        return [
            'QR Code',
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
        return collect($this->rows)
            ->map(function (array $row) {
                return array_merge([''], $row);
            })
            ->all();
    }

    public function drawings(): array
    {
        $drawings = [];
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
        ]);

        foreach ($this->rows as $index => $row) {
            $link = trim((string) ($row[5] ?? ''));
            if ($link === '') {
                continue;
            }

            try {
                $path = tempnam(sys_get_temp_dir(), 'qr_export_');
                if ($path === false) {
                    continue;
                }

                $pngPath = $path . '.png';
                @rename($path, $pngPath);
                file_put_contents($pngPath, (new QRCode($options))->render($link));
                $this->qrImagePaths[] = $pngPath;

                $drawing = new Drawing();
                $drawing->setName('Evaluation QR Code');
                $drawing->setDescription('Evaluation QR code for ' . (string) ($row[0] ?? 'faculty'));
                $drawing->setPath($pngPath);
                $drawing->setHeight(86);
                $drawing->setCoordinates('A' . ($index + 2));
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(6);

                $drawings[] = $drawing;
            } catch (Throwable) {
                if (isset($pngPath) && is_file($pngPath)) {
                    @unlink($pngPath);
                }
            }
        }

        return $drawings;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 28,
            'C' => 34,
            'D' => 42,
            'E' => 16,
            'F' => 16,
            'G' => 58,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->getRowDimension(1)->setRowHeight(24);

                foreach (range(2, count($this->rows) + 1) as $rowNumber) {
                    $sheet->getRowDimension($rowNumber)->setRowHeight(74);
                }

                $sheet->getStyle('A:G')->getAlignment()->setVertical('center');
                $sheet->getStyle('G:G')->getAlignment()->setWrapText(true);
                $sheet->freezePane('A2');
            },
        ];
    }

    public function __destruct()
    {
        foreach ($this->qrImagePaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
