<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\AdminReportExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class AdminReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Use /admin/reports/students, /payments, /attendance, /export?format=excel|word',
        ]);
    }

    /**
     * Export report as Excel (.xlsx) with multiple formatted sheets and embedded charts.
     */
    public function exportExcel(Request $request)
    {
        $locale   = $request->get('locale', app()->getLocale());
        $filename = 'relatorio-olsangola-' . now()->format('Y-m-d') . '.xlsx';

        // Gerar o xlsx como bytes em memória
        $xlsxBytes = Excel::raw(new AdminReportExport($locale), \Maatwebsite\Excel\Excel::XLSX);

        // Escrever em ficheiro temporário para poder corrigir os IDs via ZipArchive
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('ols_') . '.xlsx';
        file_put_contents($tempPath, $xlsxBytes);

        // Corrigir IDs duplicados nos drawing XMLs
        $this->fixXlsxDrawingIds($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Corrige IDs duplicados de shapes nos drawing XMLs do XLSX.
     * O PhpSpreadsheet atribui IDs começando em 1025 para CADA sheet,
     * o que cria duplicados no workbook inteiro. O Excel detecta duplicados
     * como corrompido e faz "Reparo", removendo shapes.
     */
    private function fixXlsxDrawingIds(string $path): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE) !== true) return;

        $counter  = 1001;
        $modified = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!preg_match('#xl/drawings/drawing\d+\.xml$#', $name)) continue;

            $content = $zip->getFromIndex($i);
            $content = preg_replace_callback(
                '/\bid="(\d+)"/',
                function () use (&$counter) { return 'id="' . ($counter++) . '"'; },
                $content
            );
            $modified[$name] = $content;
        }

        foreach ($modified as $name => $content) {
            $zip->deleteName($name);
            $zip->addFromString($name, $content);
        }

        $zip->close();
    }

    /**
     * Export report as Word (.docx) with tables and headings.
     */
    public function exportWord(Request $request)
    {
        $locale   = $request->get('locale', app()->getLocale());
        $phpWord  = new PhpWord();
        $phpWord->getDefaultFontName('Arial');
        $phpWord->getDefaultFontSize(11);

        // ── Styles ────────────────────────────────────────────────────────────
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'color' => '1a3a5c']);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 14, 'color' => '2563eb']);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 12, 'color' => '374151']);

        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => 'e4e4e7',
            'cellMargin'  => 80,
        ];
        $headerCellStyle = ['bgColor' => '1a3a5c'];
        $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10];
        $bodyFontStyle   = ['size' => 10];
        $altCellStyle    = ['bgColor' => 'f8fafc'];

        // ── Capa ──────────────────────────────────────────────────────────────
        $cover = $phpWord->addSection();
        $cover->addTitle('Relatório Administrativo', 1);
        $cover->addTitle('Olsangola Corporation', 2);
        $cover->addText('Gerado em: ' . now()->format('d/m/Y H:i'), ['italic' => true, 'color' => '71717a']);
        $cover->addTextBreak(2);

        // ── Resumo ────────────────────────────────────────────────────────────
        $totalStudents   = StudentProfile::count();
        $activeEnroll    = Enrollment::whereIn('status', ['active', 'enrolled'])->count();
        $totalTeachers   = TeacherProfile::count();
        $totalRevenue    = (float) Payment::where('status', 'paid')->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->count();
        $overduePayments = Payment::where('status', 'overdue')->count();

        $cover->addTitle('Resumo Executivo', 2);
        $sumTable = $cover->addTable($tableStyle);
        $this->addWordTableRow($sumTable, ['Indicador', 'Valor'], $headerCellStyle, $headerFontStyle, true);
        foreach ([
            ['Total de Alunos', $totalStudents],
            ['Inscrições Activas', $activeEnroll],
            ['Total de Professores', $totalTeachers],
            ['Receita Total (AOA)', number_format($totalRevenue, 2, ',', '.')],
            ['Pagamentos Pendentes', $pendingPayments],
            ['Pagamentos Em Atraso', $overduePayments],
        ] as $i => $row) {
            $this->addWordTableRow($sumTable, $row, $i % 2 === 0 ? [] : $altCellStyle, $bodyFontStyle);
        }
        // ── Visão geral — chart ───────────────────────────────────────────────
        $cover->addTextBreak(1);
        $cover->addTitle('Visão Geral — Indicadores', 3);
        $cover->addChart('bar',
            ['Alunos', 'Inscrições', 'Professores', 'Cursos', 'Turmas'],
            [
                (int) $totalStudents,
                (int) $activeEnroll,
                (int) $totalTeachers,
                (int) Course::count(),
                (int) ClassGroup::where('is_active', true)->count(),
            ],
            ['width' => 5400000, 'height' => 2800000, 'title' => 'Indicadores Gerais', 'showLegend' => false]
        );

        $cover->addTextBreak();

        // ── Alunos ────────────────────────────────────────────────────────────
        $section2 = $phpWord->addSection();
        $section2->addTitle('Lista de Alunos Inscritos', 2);

        $enrollments = Enrollment::with(['student.user', 'course', 'classGroup'])
            ->whereIn('status', ['active', 'enrolled'])
            ->get();

        $studTable = $section2->addTable($tableStyle);
        $this->addWordTableRow($studTable, ['Nome', 'Email', 'Curso', 'Turma', 'Progresso'], $headerCellStyle, $headerFontStyle, true);
        foreach ($enrollments->take(100) as $i => $e) {
            $this->addWordTableRow($studTable, [
                $e->student?->user?->full_name ?? 'N/D',
                $e->student?->user?->email ?? 'N/D',
                $e->course?->getTitle($locale) ?? 'N/D',
                $e->classGroup?->name ?? 'N/D',
                ($e->progress_pct ?? 0) . '%',
            ], $i % 2 === 0 ? [] : $altCellStyle, $bodyFontStyle);
        }

        if ($enrollments->count() > 100) {
            $section2->addText('... e mais ' . ($enrollments->count() - 100) . ' registos (ver ficheiro Excel para lista completa).', ['italic' => true, 'size' => 9, 'color' => '71717a']);
        }

        // ── Alunos por curso — chart ──────────────────────────────────────────
        $byCourse = Enrollment::selectRaw('course_id, count(*) as total')
            ->whereIn('status', ['active', 'enrolled'])
            ->with('course')
            ->groupBy('course_id')
            ->get();

        if ($byCourse->isNotEmpty()) {
            $section2->addTextBreak(1);
            $section2->addTitle('Alunos por Curso', 3);
            $section2->addChart('bar',
                $byCourse->map(fn ($e) => $e->course?->getTitle($locale) ?? 'N/D')->toArray(),
                $byCourse->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
                ['width' => 5400000, 'height' => 2800000, 'title' => 'Distribuição por Curso', 'showLegend' => false]
            );
        }

        // ── Pagamentos ────────────────────────────────────────────────────────
        $section3 = $phpWord->addSection();
        $section3->addTitle('Resumo de Pagamentos', 2);

        $payments = Payment::with('student.user')->orderByDesc('due_date')->take(100)->get();
        $payTable = $section3->addTable($tableStyle);
        $this->addWordTableRow($payTable, ['Aluno', 'Descrição', 'Valor (AOA)', 'Estado', 'Vencimento'], $headerCellStyle, $headerFontStyle, true);
        $statusMap = ['paid' => 'Pago', 'pending' => 'Pendente', 'overdue' => 'Em Atraso', 'cancelled' => 'Cancelado'];
        foreach ($payments as $i => $p) {
            $this->addWordTableRow($payTable, [
                $p->student?->user?->full_name ?? 'N/D',
                $p->description ?? 'N/D',
                number_format((float)$p->amount, 2, ',', '.'),
                $statusMap[$p->status] ?? ucfirst($p->status),
                $p->due_date?->format('d/m/Y') ?? 'N/D',
            ], $i % 2 === 0 ? [] : $altCellStyle, $bodyFontStyle);
        }

        // ── Pagamentos — chart ────────────────────────────────────────────────
        $section3->addTextBreak(1);
        $section3->addTitle('Distribuição de Pagamentos', 3);
        $paidCount    = Payment::where('status', 'paid')->count();
        $pendingCount = Payment::where('status', 'pending')->count();
        $overdueCount = Payment::where('status', 'overdue')->count();
        $section3->addChart('pie',
            ['Pago', 'Pendente', 'Em Atraso'],
            [(int) $paidCount, (int) $pendingCount, (int) $overdueCount],
            ['width' => 4000000, 'height' => 3000000, 'title' => 'Estado dos Pagamentos']
        );

        // ── Gerar ficheiro ────────────────────────────────────────────────────
        $filename  = 'relatorio-olsangola-' . now()->format('Y-m-d') . '.docx';
        $tempPath  = sys_get_temp_dir() . '/' . $filename;
        $writer    = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Helper: add a row to a PHPWord table.
     */
    private function addWordTableRow($table, array $cells, array $cellStyle, array $fontStyle, bool $isHeader = false): void
    {
        $row = $table->addRow();
        foreach ($cells as $cell) {
            $td = $row->addCell(null, $isHeader ? array_merge($cellStyle, ['valign' => 'center']) : $cellStyle);
            $td->addText(htmlspecialchars((string)$cell), $fontStyle);
        }
    }

    public function enrolledStudents(Request $request): JsonResponse
    {
        $enrollments = Enrollment::with(['student.user', 'student.payments', 'course', 'classGroup'])
            ->where('status', 'active')
            ->get()
            ->map(function (Enrollment $e) {
                $student  = $e->student;
                $payments = $student?->payments ?? collect();
                $paid     = (float) $payments->where('status', 'paid')->sum('amount');
                $pending  = (float) $payments->where('status', 'pending')->sum('amount');
                $overdue  = (float) $payments->where('status', 'overdue')->sum('amount');

                $financialStatus = 'ok';
                if ($overdue > 0) $financialStatus = 'overdue';
                elseif ($pending > 0) $financialStatus = 'pending';

                return [
                    'id'                 => $student?->id,
                    'name'               => $student?->user?->full_name ?? 'N/D',
                    'email'              => $student?->user?->email,
                    'student_code'       => $student?->student_code,
                    'course'             => $e->course?->getTitle(app()->getLocale()) ?? 'N/D',
                    'class'              => $e->classGroup?->name ?? 'N/D',
                    'enrollment_status'  => $e->status,
                    'enrolled_at'        => $e->created_at?->format('d/m/Y'),
                    'progress'           => $e->progress_pct,
                    'paid'               => $paid,
                    'pending'            => $pending,
                    'overdue'            => $overdue,
                    'financial_status'   => $financialStatus,
                ];
            });

        return response()->json([
            'data'  => $enrollments->values(),
            'total' => $enrollments->count(),
        ]);
    }

    public function students(Request $request): JsonResponse
    {
        $total            = StudentProfile::count();
        $activeEnrollments = Enrollment::where('status', 'active')->count();

        $byLevel = StudentProfile::selectRaw('current_level, count(*) as total')
            ->groupBy('current_level')
            ->get();

        // Enrollment trend last 12 months
        $enrollmentTrend = collect(range(11, 0))->map(function (int $monthsAgo) {
            $month = now()->subMonths($monthsAgo);
            return [
                'month'      => $month->format('M/y'),
                'Inscrições' => Enrollment::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'Activas'    => Enrollment::where('status', 'active')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        });

        // Students by course
        $byCourse = Enrollment::selectRaw('course_id, count(*) as total')
            ->where('status', 'active')
            ->with('course')
            ->groupBy('course_id')
            ->get()
            ->map(fn ($e) => [
                'name'  => $e->course?->getTitle(app()->getLocale()) ?? 'Sem curso',
                'total' => $e->total,
            ]);

        // Top 5 classes by student count
        $topClasses = ClassGroup::withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'name'     => $c->name,
                'students' => $c->enrollments_count,
            ]);

        return response()->json([
            'totalStudents'     => $total,
            'activeEnrollments' => $activeEnrollments,
            'totalTeachers'     => TeacherProfile::count(),
            'totalCourses'      => Course::count(),
            'totalClasses'      => ClassGroup::where('is_active', true)->count(),
            'byLevel'           => $byLevel,
            'enrollmentTrend'   => $enrollmentTrend,
            'byCourse'          => $byCourse,
            'topClasses'        => $topClasses,
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $start = $request->get('start_date', now()->startOfMonth()->toDateString());
        $end   = $request->get('end_date',   now()->endOfMonth()->toDateString());

        $payments = Payment::whereBetween('due_date', [$start, $end])->get();

        // Monthly revenue trend last 12 months
        $monthlyTrend = collect(range(11, 0))->map(function (int $monthsAgo) {
            $month = now()->subMonths($monthsAgo);
            return [
                'month'   => $month->format('M/y'),
                'Receita' => (float) Payment::where('status', 'paid')
                    ->whereMonth('paid_at', $month->month)
                    ->whereYear('paid_at', $month->year)
                    ->sum('amount'),
            ];
        });

        // Payment status breakdown for period
        $statusBreakdown = [
            ['name' => 'Pago',      'value' => $payments->where('status', 'paid')->count(),    'amount' => $payments->where('status', 'paid')->sum('amount')],
            ['name' => 'Pendente',  'value' => $payments->where('status', 'pending')->count(),  'amount' => $payments->where('status', 'pending')->sum('amount')],
            ['name' => 'Em Atraso', 'value' => $payments->where('status', 'overdue')->count(),  'amount' => $payments->where('status', 'overdue')->sum('amount')],
        ];

        return response()->json([
            'period'          => ['start' => $start, 'end' => $end],
            'total'           => $payments->count(),
            'paid'            => $payments->where('status', 'paid')->count(),
            'pending'         => $payments->where('status', 'pending')->count(),
            'overdue'         => $payments->where('status', 'overdue')->count(),
            'amountPaid'      => $payments->where('status', 'paid')->sum('amount'),
            'amountPending'   => $payments->where('status', 'pending')->sum('amount'),
            'amountOverdue'   => $payments->where('status', 'overdue')->sum('amount'),
            'monthlyTrend'    => $monthlyTrend,
            'statusBreakdown' => $statusBreakdown,
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $start = $request->get('start_date', now()->subDays(30)->toDateString());
        $end   = $request->get('end_date',   now()->toDateString());

        $records = Attendance::whereBetween('date', [$start, $end])->get();

        $total   = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent  = $records->where('status', 'absent')->count();

        // Daily attendance trend
        $dailyTrend = $records->groupBy(fn ($r) => $r->date->format('d/m'))
            ->map(fn ($group, $date) => [
                'date'     => $date,
                'Presença' => $group->where('status', 'present')->count(),
                'Falta'    => $group->where('status', 'absent')->count(),
            ])
            ->values()
            ->take(30);

        // Classes with most absences
        $classes = ClassGroup::withCount(['attendances' => function ($q) use ($start, $end) {
            $q->whereBetween('date', [$start, $end])->where('status', 'absent');
        }])->orderByDesc('attendances_count')->take(5)->get();

        // Grade averages by course
        $gradesByCourse = Grade::with('course')
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('course_id')
            ->map(fn ($grades, $courseId) => [
                'course' => $grades->first()->course?->getTitle(app()->getLocale()) ?? 'Sem curso',
                'avg'    => round($grades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1),
                'count'  => $grades->count(),
            ])
            ->values();

        return response()->json([
            'period'              => ['start' => $start, 'end' => $end],
            'totalRecords'        => $total,
            'present'             => $present,
            'absent'              => $absent,
            'attendanceRate'      => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            'dailyTrend'          => $dailyTrend,
            'topClassesByAbsences' => $classes->map(fn (ClassGroup $class) => [
                'id'       => $class->id,
                'name'     => $class->name,
                'absences' => $class->attendances_count,
            ]),
            'gradesByCourse'      => $gradesByCourse,
        ]);
    }
}
