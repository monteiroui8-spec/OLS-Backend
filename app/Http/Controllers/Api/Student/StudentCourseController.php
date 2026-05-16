<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;
        $locale  = app()->getLocale();

        $enrollments = Enrollment::with(['course', 'classGroup.teacher.user'])
            ->where('student_id', $student->id)
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->get();

        $data = $enrollments->map(function (Enrollment $enrollment) use ($locale) {
            $course     = $enrollment->course;
            $classGroup = $enrollment->classGroup;
            $teacher    = $classGroup?->teacher?->user;

            return [
                'id'          => $enrollment->id,
                'name'        => $course?->getTitle($locale) ?? '',
                'progress'    => (int) ($enrollment->progress_pct ?? 0),
                'teacher'     => $teacher?->full_name ?? '',
                'level'       => $course?->level ?? '',
                'description' => $locale === 'en'
                    ? ($course?->description_en ?? $course?->description_pt ?? '')
                    : ($course?->description_pt ?? ''),
                'category'    => $course?->service_type ?? '',
                'status'      => $enrollment->status,
                'start_date'  => $enrollment->start_date?->toDateString(),
                'end_date'    => $enrollment->end_date?->toDateString(),
            ];
        });

        return response()->json(['data' => $data]);
    }
}