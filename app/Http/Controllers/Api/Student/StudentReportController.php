<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;
        if (! $student) {
            return response()->json(['message' => 'Perfil de aluno não encontrado.'], 403);
        }

        $grades      = $student->grades()->with('course')->orderBy('date')->get();
        $attendances = $student->attendances()->orderBy('date')->get();
        $enrollments = $student->enrollments()->with(['course', 'classGroup'])->get();
        $payments    = $student->payments()->orderBy('due_date')->get();

        // Grade by type
        $gradesByType = $grades
            ->groupBy('type')
            ->map(fn ($g, $type) => [
                'type'  => $type,
                'count' => $g->count(),
                'avg'   => round($g->avg(fn ($gr) => ($gr->grade / max($gr->max_grade, 1)) * 100), 1),
            ])
            ->values();

        // Grade trend grouped by month
        $gradeTrend = $grades
            ->groupBy(fn ($g) => $g->date->format('M/y'))
            ->map(fn ($g, $month) => [
                'month' => $month,
                'Média' => round($g->avg(fn ($gr) => ($gr->grade / max($gr->max_grade, 1)) * 100), 1),
                'Total' => $g->count(),
            ])
            ->values()
            ->take(12);

        $avgGrade = $grades->count() > 0
            ? round($grades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
            : null;

        $attendanceRate = $attendances->count() > 0
            ? round($attendances->where('status', 'present')->count() / $attendances->count() * 100, 1)
            : null;

        return response()->json([
            'student' => [
                'name'  => $student->user->full_name ?? 'N/D',
                'code'  => $student->student_code,
                'level' => $student->current_level,
            ],
            'stats' => [
                'totalGrades'    => $grades->count(),
                'avgGrade'       => $avgGrade,
                'attendanceRate' => $attendanceRate,
                'totalAbsences'  => $attendances->where('status', 'absent')->count(),
            ],
            'enrollments' => $enrollments->map(fn ($e) => [
                'course'      => $e->course?->getTitle(app()->getLocale()) ?? 'N/D',
                'class'       => $e->classGroup?->name ?? 'N/D',
                'status'      => $e->status,
                'progress'    => $e->progress_pct,
                'enrolled_at' => $e->created_at?->format('d/m/Y'),
            ]),
            'grades' => $grades->map(fn ($g) => [
                'title'     => $g->title,
                'type'      => $g->type,
                'grade'     => (float) $g->grade,
                'max_grade' => (float) $g->max_grade,
                'pct'       => round(($g->grade / max($g->max_grade, 1)) * 100, 1),
                'date'      => $g->date?->format('d/m/Y'),
                'course'    => $g->course?->getTitle(app()->getLocale()) ?? 'N/D',
            ]),
            'gradesByType' => $gradesByType,
            'gradeTrend'   => $gradeTrend,
            'attendance' => [
                'total'   => $attendances->count(),
                'present' => $attendances->where('status', 'present')->count(),
                'absent'  => $attendances->where('status', 'absent')->count(),
            ],
            'payments' => [
                'paid'    => (float) $payments->where('status', 'paid')->sum('amount'),
                'pending' => (float) $payments->where('status', 'pending')->sum('amount'),
                'overdue' => (float) $payments->where('status', 'overdue')->sum('amount'),
            ],
        ]);
    }
}
