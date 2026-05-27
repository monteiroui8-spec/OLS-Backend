<?php

namespace App\Exports\Sheets;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class StudentsSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithCharts, WithEvents
{
    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Alunos'; }

    public function charts(): array { return []; }

    public function collection()
    {
        return Enrollment::with(['student.user', 'course', 'classGroup'])
            ->whereIn('status', ['active', 'enrolled'])
            ->get();
    }

    public function headings(): array
    {
        return ['Código Aluno', 'Nome', 'Email', 'Curso', 'Turma', 'Estado', 'Progresso (%)', 'Data Inscrição'];
    }

    public function map($enrollment): array
    {
        $student = $enrollment->student;
        return [
            $student?->student_code ?? 'N/D',
            $student?->user?->full_name ?? 'N/D',
            $student?->user?->email ?? 'N/D',
            $enrollment->course?->getTitle($this->locale) ?? 'N/D',
            $enrollment->classGroup?->name ?? 'N/D',
            ucfirst($enrollment->status),
            $enrollment->progress_pct ?? 0,
            $enrollment->created_at?->format('d/m/Y') ?? 'N/D',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Calcular distribuição por curso com dados estáticos
                $byCourse = Enrollment::with('course')
                    ->whereIn('status', ['active', 'enrolled'])
                    ->get()
                    ->groupBy('course_id');

                if ($byCourse->isEmpty()) return;

                $courseLabels = [];
                $courseValues = [];
                foreach ($byCourse as $enrollments) {
                    $courseLabels[] = mb_substr($enrollments->first()->course?->getTitle($this->locale) ?? 'N/D', 0, 30);
                    $courseValues[] = $enrollments->count();
                }

                // ── Gráfico: Alunos por Curso (barras horizontais, dados estáticos) ─
                $xSeries = new DataSeriesValues('String', null, null, count($courseLabels), $courseLabels);
                $ySeries = new DataSeriesValues('Number', null, null, count($courseValues), $courseValues);

                $barSeries = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_CLUSTERED,
                    [0], [], [$xSeries], [$ySeries]
                );
                $barSeries->setPlotDirection(DataSeries::DIRECTION_BAR);

                $chart = new Chart(
                    'ols_alunos_curso',
                    new Title('Alunos por Curso'),
                    new Legend(Legend::POSITION_BOTTOM, null, false),
                    new PlotArea(null, [$barSeries])
                );
                $chart->setTopLeftPosition('J2');
                $chart->setBottomRightPosition('S20');
                $ws->addChart($chart);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a3a5c']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 16, 'B' => 28, 'C' => 32, 'D' => 26, 'E' => 16, 'F' => 12, 'G' => 14, 'H' => 16];
    }
}
