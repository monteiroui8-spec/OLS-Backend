<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->studentProfile;

        $grades = Grade::where('student_id', $student->id)
            ->latest('date')
            ->take(7)
            ->get();

        $trend = $grades
            ->sortBy('date')
            ->map(function (Grade $grade) {
                return [
                    'date' => $grade->date->format('M d'),
                    'grade' => $grade->grade,
                ];
            })
            ->values();

        $enrollments = Enrollment::with('course')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->get();

        $courseProgress = $enrollments->map(function (Enrollment $enrollment) {
            $name = $enrollment->course?->{'title_'.app()->getLocale()} ?? '';

            return [
                'name' => $name,
                'progress' => $enrollment->progress_pct,
            ];
        });

        $upcomingExams = Exam::whereIn('class_group_id', $enrollments->pluck('class_group_id'))
            ->where('status', 'published')
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->map(function (Exam $exam) {
                return [
                    'title' => $exam->title,
                    'course' => $exam->classGroup?->course?->{'title_'.app()->getLocale()},
                    'date' => $exam->due_date?->format('M d'),
                    'duration' => $exam->duration.' min',
                ];
            });

        $pendingPayments = Payment::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->count();

        return response()->json([
            'welcomeName' => $user->first_name,
            'currentLevel' => $student->current_level ?? 'N/A',
            'stats' => [
                'enrolledCourses' => $enrollments->count(),
                'availableExams' => $upcomingExams->count(),
                'averageGrade' => round($grades->avg('grade') ?? 0),
                'pendingPayments' => $pendingPayments,
            ],
            'performanceTrend' => $trend,
            'courseProgress' => $courseProgress,
            'upcomingExams' => $upcomingExams,
        ]);
    }
}

