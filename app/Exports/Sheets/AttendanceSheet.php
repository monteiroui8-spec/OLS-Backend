<?php

namespace App\Exports\Sheets;

use App\Models\Attendance;
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

class AttendanceSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithCharts, WithEvents
{
    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Presencas'; }  // sem ç para evitar problemas de encoding em fórmulas

    public function charts(): array { return []; }

    public function collection()
    {
        return Attendance::with(['student.user', 'classGroup'])
            ->orderByDesc('date')
            ->limit(5000)
            ->get();
    }

    public function headings(): array
    {
        return ['Aluno', 'Turma', 'Data', 'Estado', 'Justificação'];
    }

    public function map($record): array
    {
        $statusMap = ['present' => 'Presente', 'absent' => 'Falta', 'late' => 'Atrasado', 'justified' => 'Justificado'];
        return [
            $record->student?->user?->full_name ?? 'N/D',
            $record->classGroup?->name ?? 'N/D',
            $record->date instanceof \Carbon\Carbon ? $record->date->format('d/m/Y') : $record->date,
            $statusMap[$record->status] ?? ucfirst($record->status),
            $record->notes ?? '—',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Calcular totais (dados estáticos embutidos directamente no gráfico)
                $presentCount = Attendance::where('status', 'present')->count();
                $absentCount  = Attendance::where('status', 'absent')->count();
                $lateCount    = Attendance::where('status', 'late')->count();

                if (($presentCount + $absentCount + $lateCount) === 0) return;

                // ── Gráfico: Distribuição de Presenças (pizza) ───────────────────
                $xSeries = new DataSeriesValues('String', null, null, 3, ['Presente', 'Falta', 'Atrasado']);
                $ySeries = new DataSeriesValues('Number', null, null, 3, [$presentCount, $absentCount, $lateCount]);

                $pieSeries = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    [0], [], [$xSeries], [$ySeries]
                );

                $chart = new Chart(
                    'ols_presencas_dist',
                    new Title('Distribuição de Presenças'),
                    new Legend(Legend::POSITION_RIGHT, null, false),
                    new PlotArea(null, [$pieSeries])
                );
                $chart->setTopLeftPosition('G2');
                $chart->setBottomRightPosition('O18');
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
        return ['A' => 28, 'B' => 20, 'C' => 14, 'D' => 12, 'E' => 28];
    }
}
