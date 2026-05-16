<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherGradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $query = Grade::where('teacher_id', $teacher->id)
            ->with(['student.user', 'course']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->get('student_id'));
        }

        $grades = $query->orderByDesc('date')
            ->paginate($request->integer('limit', 100));

        $data = $grades->map(function (Grade $grade) {
            return [
                'id' => $grade->id,
                'title' => $grade->title,
                'type' => $grade->type,
                'grade' => (float)$grade->grade,
                'max_grade' => (float)$grade->max_grade,
                'date' => $grade->date,
                'student' => [
                    'id' => $grade->student_id,
                    'name' => $grade->student?->user?->full_name,
                ],
                'course' => $grade->course?->getTitle(app()->getLocale()),
                'course_id' => $grade->course_id,
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

    public function store(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $validated = $request->validate([
            'student_id' => 'required',
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:Exam,Assignment,Participation,Project,Other',
            'grade' => 'required|numeric|min:0',
            'max_grade' => 'required|numeric|min:1',
            'course_id' => 'nullable',
            'class_group_id' => 'nullable',
            'date' => 'required|date',
        ]);

        $grade = Grade::create([
            'teacher_id' => $teacher->id,
            'student_id' => $validated['student_id'],
            'title' => $validated['title'],
            'type' => $validated['type'],
            'grade' => $validated['grade'],
            'max_grade' => $validated['max_grade'],
            'course_id' => $validated['course_id'] ?? null,
            'class_group_id' => $validated['class_group_id'] ?? null,
            'date' => $validated['date'],
        ]);

        return response()->json($grade, 201);
    }
}

