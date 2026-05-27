<?php

namespace App\Exports\Sheets;

use App\Models\Enrollment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\Course;
use App\Models\ClassGroup;
use App\Models\Payment;
use App\Models\Grade;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
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

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithCharts, WithEvents
{
    private int $totalStudents   = 0;
    private int $activeEnroll    = 0;
    private int $totalTeachers   = 0;
    private int $totalCourses    = 0;
    private int $totalClasses    = 0;
    private int $pendingPayments = 0;
    private int $overduePayments = 0;
    private int $paidPayments    = 0;

    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Resumo'; }

    /** Sinal para WriterFactory activar setIncludeCharts(true). Gráficos adicionados via AfterSheet. */
    public function charts(): array { return []; }

    public function array(): array
    {
        $this->totalStudents   = StudentProfile::count();
        $this->activeEnroll    = Enrollment::whereIn('status', ['active', 'enrolled'])->count();
        $this->totalTeachers   = TeacherProfile::count();
        $this->totalCourses    = Course::count();
        $this->totalClasses    = ClassGroup::where('is_active', true)->count();
        $totalRevenue          = (float) Payment::where('status', 'paid')->sum('amount');
        $this->pendingPayments = Payment::where('status', 'pending')->count();
        $this->overduePayments = Payment::where('status', 'overdue')->count();
        $this->paidPayments    = Payment::where('status', 'paid')->count();
        $totalGrades           = Grade::count();
        $avgGrade              = $totalGrades > 0
            ? round(Grade::get()->avg(fn ($g) => $g->max_grade > 0 ? ($g->grade / $g->max_grade) * 100 : 0), 1)
            : 0;

        return [
            ['RELATÓRIO ADMINISTRATIVO — OLSANGOLA CORPORATION'],
            ['Gerado em: ' . now()->format('d/m/Y H:i')],
            [''],
            ['INDICADOR', 'VALOR'],
            ['Total de Alunos',        $this->totalStudents],
            ['Inscrições Activas',      $this->activeEnroll],
            ['Total de Professores',    $this->totalTeachers],
            ['Total de Cursos',         $this->totalCourses],
            ['Turmas Activas',          $this->totalClasses],
            [''],
            ['INDICADOR FINANCEIRO', 'VALOR (AOA)'],
            ['Receita Total (Pago)',    number_format($totalRevenue, 2, ',', '.')],
            ['Pagamentos Pendentes',    $this->pendingPayments],
            ['Pagamentos Em Atraso',    $this->overduePayments],
            [''],
            ['INDICADOR ACADÉMICO', 'VALOR'],
            ['Total de Notas',          $totalGrades],
            ['Média Geral (%)',          $avgGrade . '%'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // ── Gráfico 1: Indicadores Gerais (colunas) ─────────────────────
                // Dados estáticos embutidos directamente no gráfico — sem referências a células
                $labels = ['Alunos', 'Inscrições', 'Professores', 'Cursos', 'Turmas'];
                $values = [
                    $this->totalStudents,
                    $this->activeEnroll,
                    $this->totalTeachers,
                    $this->totalCourses,
                    $this->totalClasses,
                ];

                $xSeries = new DataSeriesValues('String', null, null, count($labels), $labels);
                $ySeries = new DataSeriesValues('Number', null, null, count($values), $values);

                $barSeries = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_CLUSTERED,
                    [0], [], [$xSeries], [$ySeries]
                );
                $barSeries->setPlotDirection(DataSeries::DIRECTION_COL);

                $chart1 = new Chart(
                    'ols_resumo_kpi',
                    new Title('Indicadores Gerais'),
                    new Legend(Legend::POSITION_BOTTOM, null, false),
                    new PlotArea(null, [$barSeries])
                );
                $chart1->setTopLeftPosition('D3');
                $chart1->setBottomRightPosition('M22');
                $ws->addChart($chart1);

                // ── Gráfico 2: Situação de Pagamentos (pizza) ────────────────────
                $payLabels = ['Pago', 'Pendente', 'Em Atraso'];
                $payValues = [$this->paidPayments, $this->pendingPayments, $this->overduePayments];

                $pxSeries = new DataSeriesValues('String', null, null, 3, $payLabels);
                $pySeries = new DataSeriesValues('Number', null, null, 3, $payValues);

                $pieSeries = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    [0], [], [$pxSeries], [$pySeries]
                );

                $chart2 = new Chart(
                    'ols_resumo_pagamentos',
                    new Title('Situação de Pagamentos'),
                    new Legend(Legend::POSITION_RIGHT, null, false),
                    new PlotArea(null, [$pieSeries])
                );
                $chart2->setTopLeftPosition('D23');
                $chart2->setBottomRightPosition('M40');
                $ws->addChart($chart2);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a3a5c']]],
            4  => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']]],
            11 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']]],
            16 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563eb']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 25, 'D' => 18, 'E' => 14];
    }
}
