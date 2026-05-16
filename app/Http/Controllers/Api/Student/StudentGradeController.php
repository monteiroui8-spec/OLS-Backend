<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentGradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;

        $grades = Grade::where('student_id', $student->id)
            ->orderByDesc('date')
            ->paginate($request->integer('limit', 20));

        $data = $grades->map(function (Grade $grade) {
            return [
                'id' => $grade->id,
                'title' => $grade->title,
                'type' => $grade->type,
                'grade' => $grade->grade,
                'max_grade' => $grade->max_grade,
                'date' => $grade->date,
                'course' => $grade->course?->getTitle(app()->getLocale()),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $grades->total(),
                'page' => $grades->currentPage(),
                'last_page' => $grades->lastPage(),
            ],
        ]);
    }
}

