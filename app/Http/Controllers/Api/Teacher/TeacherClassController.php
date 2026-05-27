<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        $classes = ClassGroup::where('teacher_id', $teacher->id)
            ->with(['course', 'schedules', 'enrollments'])
            ->withCount(['enrollments as student_count' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('year', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function (ClassGroup $class) {
                $avgProgress = $class->enrollments->where('status', 'active')->avg('progress_pct') ?? 0;
                
                return [
                    'id'           => $class->id,
                    'name'         => $class->name,
                    'level'        => $class->course?->level ?? 'Intermediate',
                    'course'       => $class->course?->getTitle(app()->getLocale()),
                    'year'         => $class->year,
                    'capacity'     => $class->capacity,
                    'studentCount' => (int)$class->student_count,
                    'progress'     => (int)$avgProgress,
                    'room'         => $class->room,
                    'is_active'    => $class->is_active,
                    'schedules'    => $class->schedules->map(fn ($s) => [
                        'day'       => $s->day_of_week,
                        'startTime' => $s->start_time,
                        'endTime'   => $s->end_time,
                    ]),
                ];
            });

        return response()->json(['data' => $classes]);
    }

    public function students(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        $classIds = ClassGroup::where('teacher_id', $teacher->id)->pluck('id');

        $enrollments = \App\Models\Enrollment::whereIn('class_group_id', $classIds)
            ->whereIn('status', ['active', 'enrolled'])
            ->with(['student.user', 'classGroup.course', 'student.grades' => function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)->orderByDesc('date')->limit(5);
            }])
            ->get();

        $data = $enrollments->map(function ($enrollment) {
            $student = $enrollment->student;
            $grades = $student?->grades ?? collect();
            $avgGrade = $grades->count()
                ? round($grades->avg(fn ($g) => $g->max_grade > 0 ? ($g->grade / $g->max_grade) * 100 : 0), 1)
                : null;

            return [
                'id'         => $student?->id,
                'name'       => $student?->user?->full_name,
                'email'      => $student?->user?->email,
                'classGroup' => $enrollment->classGroup?->name,
                'course'     => $enrollment->classGroup?->course?->getTitle(app()->getLocale()),
                'progress'   => $enrollment->progress_pct ?? 0,
                'avgGrade'   => $avgGrade,
                'status'     => $enrollment->status,
                'grades'     => $grades->map(fn ($g) => [
                    'title' => $g->title,
                    'type'  => $g->type,
                    'grade' => $g->grade,
                    'max'   => $g->max_grade,
                    'date'  => $g->date,
                ]),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
            ],
        ]);
    }

    public function show(Request $request, ClassGroup $classGroup): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if ($classGroup->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $classGroup->load(['course', 'schedules', 'students.user', 'teacher.user']);

        $students = $classGroup->students->map(function ($student) use ($teacher) {
            // Get grades for average calculation
            $grades = \App\Models\Grade::where('student_id', $student->id)
                ->where('class_group_id', $student->pivot->class_group_id)
                ->get();
            
            $avgGrade = $grades->count()
                ? round($grades->avg(fn ($g) => $g->max_grade > 0 ? ($g->grade / $g->max_grade) * 100 : 0), 1)
                : 0;

            return [
                'id'         => $student->id,
                'name'       => $student->user?->full_name,
                'email'      => $student->user?->email,
                'status'     => $student->pivot->status,
                'progress'   => (int)($student->pivot->progress_pct ?? 0),
                'avgGrade'   => $avgGrade,
                'attendance' => 'Present', // Placeholder for now
                'start_date' => $student->pivot->start_date,
            ];
        });

        return response()->json([
            'data' => [
                'id'            => $classGroup->id,
                'name'          => $classGroup->name,
                'course'        => $classGroup->course?->getTitle(app()->getLocale()),
                'level'         => $classGroup->course?->level ?? 'N/A',
                'teacher'       => $classGroup->teacher?->user?->full_name ?? '—',
                'year'          => $classGroup->year,
                'capacity'      => $classGroup->capacity,
                'room'          => $classGroup->room,
                'is_active'     => $classGroup->is_active,
                'student_count' => $students->count(),
                'students'      => $students,
                'schedules'     => $classGroup->schedules->map(fn ($s) => [
                    'day'       => $s->day_of_week,
                    'startTime' => $s->start_time,
                    'endTime'   => $s->end_time,
                    'room'      => $classGroup->room,
                ]),
                'progressTrend' => [
                    ['month' => 'Jan', 'score' => 65],
                    ['month' => 'Feb', 'score' => 68],
                    ['month' => 'Mar', 'score' => 75],
                    ['month' => 'Apr', 'score' => 82],
                ],
            ]
        ]);
    }
}
