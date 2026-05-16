<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        $schedule = ClassSchedule::whereHas('classGroup', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
            ->with(['classGroup.course'])
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'day' => $s->day_of_week,
                    'time' => substr($s->start_time, 0, 5),
                    'endTime' => substr($s->end_time, 0, 5),
                    'subject' => $s->classGroup->course?->getTitle(app()->getLocale()) ?? 'N/A',
                    'room' => $s->classGroup->room ?? 'N/A',
                    'students' => $s->classGroup->enrollments()->where('status', 'active')->count(),
                    'color' => $this->getColorForCourse($s->classGroup->course_id),
                ];
            });

        return response()->json([
            'data' => $schedule,
        ]);
    }

    private function getColorForCourse($courseId): string
    {
        $colors = [
            'bg-green-50 border-green-200 text-green-700',
            'bg-primary/10 border-primary/20 text-primary',
            'bg-accent/10 border-accent/20 text-accent',
            'bg-purple-50 border-purple-200 text-purple-700',
            'bg-blue-50 border-blue-200 text-blue-700',
            'bg-orange-50 border-orange-200 text-orange-700',
        ];
        
        // Deterministic color based on ID
        $index = hexdec(substr(md5($courseId), 0, 2)) % count($colors);
        return $colors[$index];
    }
}
