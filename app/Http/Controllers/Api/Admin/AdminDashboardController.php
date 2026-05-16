<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember(
            'admin_dashboard',
            now()->addMinutes(5),
            function () {
                $now = now();
                $monthStart = $now->copy()->startOfMonth();
                $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
                $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

                $revenueThisMonth = Payment::where('status', 'paid')
                    ->whereBetween('paid_at', [$monthStart, $now])
                    ->sum('amount');

                $revenueLastMonth = Payment::where('status', 'paid')
                    ->whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])
                    ->sum('amount');

                $revenueChange = $revenueLastMonth > 0
                    ? round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1)
                    : 0;

                $financialChart = collect(range(5, 0))->map(function (int $monthsAgo) {
                    $month = now()->subMonths($monthsAgo);

                    return [
                        'month' => $month->format('M'),
                        'Revenue' => Payment::where('status', 'paid')
                            ->whereMonth('paid_at', $month->month)
                            ->whereYear('paid_at', $month->year)
                            ->sum('amount'),
                        'Expenses' => 0,
                    ];
                });

                // Build recent activities from real models
                $recentPayments = Payment::where('status', 'paid')
                    ->orderByDesc('paid_at')
                    ->take(4)
                    ->get()
                    ->map(fn ($p) => [
                        'text' => "Pagamento confirmado — {$p->amount} AOA",
                        'time' => $p->paid_at?->diffForHumans() ?? $p->updated_at->diffForHumans(),
                        'type' => 'payment',
                    ]);

                $recentEnrollments = Enrollment::with('student.user')
                    ->orderByDesc('created_at')
                    ->take(4)
                    ->get()
                    ->map(fn ($e) => [
                        'text' => "Nova inscrição: " . ($e->student?->user?->full_name ?? 'Aluno'),
                        'time' => $e->created_at->diffForHumans(),
                        'type' => 'enrollment',
                    ]);

                $recentUsers = User::orderByDesc('created_at')
                    ->take(3)
                    ->get()
                    ->map(fn ($u) => [
                        'text' => "Novo utilizador registado: {$u->full_name}",
                        'time' => $u->created_at->diffForHumans(),
                        'type' => 'user',
                    ]);

                $activities = $recentPayments
                    ->concat($recentEnrollments)
                    ->concat($recentUsers)
                    ->sortByDesc(fn ($a) => $a['time'])
                    ->take(10)
                    ->values();

                $overdueCount = Payment::where('status', 'overdue')->count();

                $alerts = [];

                if ($overdueCount > 0) {
                    $alerts[] = [
                        'text' => $overdueCount.' pagamento(s) em atraso.',
                        'type' => 'error',
                    ];
                }

                $fullClasses = ClassGroup::where('is_active', true)
                    ->get()
                    ->filter(function (ClassGroup $classGroup) {
                        return ! $classGroup->hasCapacity();
                    });

                if ($fullClasses->count() > 0) {
                    $alerts[] = [
                        'text' => $fullClasses->count().' turma(s) com capacidade máxima atingida.',
                        'type' => 'info',
                    ];
                }

                // Enrollment trend — last 6 months
                $enrollmentTrend = collect(range(5, 0))->map(function (int $monthsAgo) {
                    $month = now()->subMonths($monthsAgo);
                    return [
                        'month' => $month->format('M'),
                        'Inscrições' => Enrollment::whereMonth('created_at', $month->month)
                            ->whereYear('created_at', $month->year)
                            ->count(),
                    ];
                });

                // Grade average trend — last 6 months
                $gradeTrend = collect(range(5, 0))->map(function (int $monthsAgo) {
                    $month = now()->subMonths($monthsAgo);
                    $grades = Grade::whereMonth('date', $month->month)
                        ->whereYear('date', $month->year)
                        ->get();
                    $avg = $grades->count() > 0
                        ? round($grades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
                        : null;
                    return [
                        'month' => $month->format('M'),
                        'Média' => $avg,
                    ];
                });

                return [
                    'stats' => [
                        'totalStudents' => StudentProfile::count(),
                        'totalTeachers' => TeacherProfile::count(),
                        'activeClasses' => ClassGroup::where('is_active', true)->count(),
                        'totalEnrollments' => Enrollment::where('status', 'active')->count(),
                        'monthlyRevenue' => [
                            'AOA' => $revenueThisMonth,
                        ],
                        'revenueChange' => $revenueChange,
                    ],
                    'financialChart' => $financialChart,
                    'enrollmentTrend' => $enrollmentTrend,
                    'gradeTrend' => $gradeTrend,
                    'recentActivities' => $activities,
                    'paymentSummary' => [
                        'paid' => Payment::where('status', 'paid')
                            ->whereMonth('due_date', $now->month)
                            ->count(),
                        'pending' => Payment::where('status', 'pending')->count(),
                        'overdue' => Payment::where('status', 'overdue')->count(),
                    ],
                    'alerts' => $alerts,
                ];
            }
        );

        return response()->json($data);
    }
}

