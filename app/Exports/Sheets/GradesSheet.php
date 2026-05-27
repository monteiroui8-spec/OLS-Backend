<?php

namespace App\Exports\Sheets;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GradesSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Notas'; }

    public function collection()
    {
        return Grade::with(['student.user', 'course', 'teacher.user'])->orderByDesc('date')->get();
    }

    public function headings(): array
    {
        return ['Aluno', 'Curso', 'Professor', 'Título', 'Tipo', 'Nota', 'Nota Máx.', 'Percentagem (%)', 'Data'];
    }

    public function map($grade): array
    {
        $pct = $grade->max_grade > 0 ? round(($grade->grade / $grade->max_grade) * 100, 1) : 0;
        return [
            $grade->student?->user?->full_name ?? 'N/D',
            $grade->course?->getTitle($this->locale) ?? 'N/D',
            $grade->teacher?->user?->full_name ?? 'N/D',
            $grade->title,
            $grade->type,
            $grade->grade,
            $grade->max_grade,
            $pct,
            $grade->date instanceof \Carbon\Carbon ? $grade->date->format('d/m/Y') : $grade->date,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a3a5c']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 24, 'C' => 24, 'D' => 24, 'E' => 14, 'F' => 8, 'G' => 10, 'H' => 16, 'I' => 12];
    }
}
