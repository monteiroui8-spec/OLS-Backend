<?php

namespace App\Exports\Sheets;

use App\Models\Payment;
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

class PaymentsSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping, WithCharts, WithEvents
{
    public function __construct(private string $locale = 'pt') {}

    public function title(): string { return 'Pagamentos'; }

    public function charts(): array { return []; }

    public function collection()
    {
        return Payment::with(['student.user'])->orderByDesc('due_date')->get();
    }

    public function headings(): array
    {
        return ['Aluno', 'Descrição', 'Valor (AOA)', 'Estado', 'Vencimento', 'Pago Em'];
    }

    public function map($payment): array
    {
        $statusMap = ['paid' => 'Pago', 'pending' => 'Pendente', 'overdue' => 'Em Atraso', 'cancelled' => 'Cancelado'];
        return [
            $payment->student?->user?->full_name ?? 'N/D',
            $payment->description ?? 'N/D',
            number_format((float) $payment->amount, 2, ',', '.'),
            $statusMap[$payment->status] ?? ucfirst($payment->status),
            $payment->due_date?->format('d/m/Y') ?? 'N/D',
            $payment->paid_at?->format('d/m/Y') ?? '—',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();

                // Calcular totais para gráficos (dados estáticos embutidos)
                $all        = Payment::all();
                $paidSum    = (float) $all->where('status', 'paid')->sum('amount');
                $pendingSum = (float) $all->where('status', 'pending')->sum('amount');
                $overdueSum = (float) $all->where('status', 'overdue')->sum('amount');

                // ── Gráfico 1: Distribuição de Pagamentos (pizza) ────────────────
                $pxSeries = new DataSeriesValues('String', null, null, 3, ['Pago', 'Pendente', 'Em Atraso']);
                $pySeries = new DataSeriesValues('Number', null, null, 3, [$paidSum, $pendingSum, $overdueSum]);

                $pieSeries = new DataSeries(
                    DataSeries::TYPE_PIECHART,
                    DataSeries::GROUPING_STANDARD,
                    [0], [], [$pxSeries], [$pySeries]
                );

                $chart1 = new Chart(
                    'ols_pagamentos_dist',
                    new Title('Distribuição de Pagamentos (AOA)'),
                    new Legend(Legend::POSITION_RIGHT, null, false),
                    new PlotArea(null, [$pieSeries])
                );
                $chart1->setTopLeftPosition('H2');
                $chart1->setBottomRightPosition('Q18');
                $ws->addChart($chart1);

                // ── Gráfico 2: Receita Mensal — últimos 6 meses (linha) ──────────
                $months = [];
                $revenue = [];
                for ($i = 5; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $months[]  = $month->format('M/y');
                    $revenue[] = (float) Payment::where('status', 'paid')
                        ->whereMonth('paid_at', $month->month)
                        ->whereYear('paid_at', $month->year)
                        ->sum('amount');
                }

                $lxSeries = new DataSeriesValues('String', null, null, 6, $months);
                $lySeries = new DataSeriesValues('Number', null, null, 6, $revenue);

                $lineSeries = new DataSeries(
                    DataSeries::TYPE_LINECHART,
                    DataSeries::GROUPING_STANDARD,
                    [0], [], [$lxSeries], [$lySeries]
                );
                $lineSeries->setPlotDirection(DataSeries::DIRECTION_COL);

                $chart2 = new Chart(
                    'ols_pagamentos_mensal',
                    new Title('Receita Mensal (Últimos 6 Meses)'),
                    new Legend(Legend::POSITION_BOTTOM, null, false),
                    new PlotArea(null, [$lineSeries])
                );
                $chart2->setTopLeftPosition('H19');
                $chart2->setBottomRightPosition('Q35');
                $ws->addChart($chart2);
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
        return ['A' => 28, 'B' => 28, 'C' => 16, 'D' => 14, 'E' => 14, 'F' => 14];
    }
}
