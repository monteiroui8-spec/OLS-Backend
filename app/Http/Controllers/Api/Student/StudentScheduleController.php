<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StudentScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;

        $week = $request->get('week');
        $weekStart = $week ? Carbon::parse($week)->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();

        $enrollments = Enrollment::with(['classGroup.course', 'classGroup.teacher.user', 'classGroup.schedules'])
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->get();

        $classes = [];

        foreach ($enrollments as $enrollment) {
            $classGroup = $enrollment->classGroup;

            foreach ($classGroup->schedules as $schedule) {
                $date = (clone $weekStart)->addDays($schedule->day_of_week);

                if ($date->between($weekStart, $weekEnd)) {
                    $classes[] = [
                        'id' => $classGroup->id.'-'.$date->format('Y-m-d'),
                        'title' => $classGroup->course?->{'title_'.app()->getLocale()},
                        'day' => $date->translatedFormat('l'),
                        'date' => $date->format('M d'),
                        'startTime' => $schedule->start_time,
                        'endTime' => $schedule->end_time,
                        'teacher' => [
                            'name' => $classGroup->teacher?->user?->full_name,
                        ],
                        'room' => $schedule->room ?? $classGroup->room,
                        'course' => [
                            'name' => $classGroup->name,
                            'level' => $classGroup->course?->level,
                        ],
                    ];
                }
            }
        }

        $totalHours = collect($classes)->sum(function (array $class) {
            $start = Carbon::createFromFormat('H:i', $class['startTime']);
            $end = Carbon::createFromFormat('H:i', $class['endTime']);

            return $end->diffInMinutes($start) / 60;
        });

        return response()->json([
            'classes' => $classes,
            'stats' => [
                'classesThisWeek' => count($classes),
                'totalHours' => $totalHours,
            ],
        ]);
    }
}

