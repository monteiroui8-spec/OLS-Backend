<?php

namespace App\Http\Controllers\Api\Admin;

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

class AdminReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Use /admin/reports/students, /payments, /attendance',
        ]);
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
