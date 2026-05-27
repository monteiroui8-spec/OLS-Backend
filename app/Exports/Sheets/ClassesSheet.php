<?php

namespace App\Exports\Sheets;

use App\Models\ClassGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClassesSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Turmas'; }

    public function collection()
    {
        return ClassGroup::with('course', 'teacher.user')
            ->withCount(['enrollments' => fn ($q) => $q->whereIn('status', ['active', 'enrolled'])])
            ->get();
    }

    public function headings(): array
    {
        return ['Turma', 'Curso', 'Professor', 'Ano', 'Capacidade', 'Alunos Inscritos', 'Sala', 'Activa'];
    }

    public function map($class): array
    {
        return [
            $class->name,
            $class->course?->getTitle($this->locale) ?? 'N/D',
            $class->teacher?->user?->full_name ?? 'N/D',
            $class->year,
            $class->capacity,
            $class->enrollments_count,
            $class->room ?? 'N/D',
            $class->is_active ? 'Sim' : 'Não',
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
        return ['A' => 20, 'B' => 26, 'C' => 28, 'D' => 8, 'E' => 12, 'F' => 16, 'G' => 12, 'H' => 8];
    }
}
