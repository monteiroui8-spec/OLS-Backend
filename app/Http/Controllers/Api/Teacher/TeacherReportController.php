<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (! $teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $classes = ClassGroup::where('teacher_id', $teacher->id)
            ->with(['course', 'enrollments.student.user'])
            ->get();

        $allGrades = Grade::where('teacher_id', $teacher->id)->get();

        $classStats = $classes->map(function (ClassGroup $class) use ($teacher, $allGrades) {
            $activeEnrollments = $class->enrollments->where('status', 'active');
            $classGrades       = $allGrades->where('class_group_id', $class->id);

            $avgGrade = $classGrades->count() > 0
                ? round($classGrades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
                : null;

            $attendances    = Attendance::where('class_group_id', $class->id)->get();
            $attendanceRate = $attendances->count() > 0
                ? round($attendances->where('status', 'present')->count() / $attendances->count() * 100, 1)
                : null;

            $students = $activeEnrollments->map(function ($e) use ($classGrades) {
                $sg = $classGrades->where('student_id', $e->student_id);
                return [
                    'id'        => $e->student_id,
                    'name'      => $e->student?->user?->full_name ?? 'N/D',
                    'progress'  => $e->progress_pct,
                    'avg_grade' => $sg->count() > 0
                        ? round($sg->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
                        : null,
                    'grades_count' => $sg->count(),
                ];
            })->values();

            return [
                'id'              => $class->id,
                'name'            => $class->name,
                'course'          => $class->course?->getTitle(app()->getLocale()) ?? 'N/D',
                'capacity'        => $class->capacity,
                'student_count'   => $activeEnrollments->count(),
                'avg_grade'       => $avgGrade,
                'attendance_rate' => $attendanceRate,
                'grades_count'    => $classGrades->count(),
                'students'        => $students,
            ];
        });

        // Grade distribution by type
        $gradesByType = $allGrades
            ->groupBy('type')
            ->map(fn ($g, $type) => [
                'type'  => $type,
                'count' => $g->count(),
                'avg'   => round($g->avg(fn ($gr) => ($gr->grade / max($gr->max_grade, 1)) * 100), 1),
            ])
            ->values();

        // Grade trend last 6 months
        $gradeTrend = collect(range(5, 0))->map(function (int $m) use ($teacher) {
            $month  = now()->subMonths($m);
            $grades = Grade::where('teacher_id', $teacher->id)
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->get();

            return [
                'month' => $month->format('M/y'),
                'Média' => $grades->count() > 0
                    ? round($grades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
                    : null,
                'Total' => $grades->count(),
            ];
        });

        $totalStudents = $classes->sum(fn ($c) => $c->enrollments->where('status', 'active')->count());
        $overallAvg    = $allGrades->count() > 0
            ? round($allGrades->avg(fn ($g) => ($g->grade / max($g->max_grade, 1)) * 100), 1)
            : null;

        return response()->json([
            'teacher'       => [
                'name' => $teacher->user->full_name ?? 'N/D',
                'code' => $teacher->teacher_code,
            ],
            'totalClasses'  => $classes->count(),
            'totalStudents' => $totalStudents,
            'totalGrades'   => $allGrades->count(),
            'overallAvg'    => $overallAvg,
            'classes'       => $classStats,
            'gradesByType'  => $gradesByType,
            'gradeTrend'    => $gradeTrend,
        ]);
    }
}
