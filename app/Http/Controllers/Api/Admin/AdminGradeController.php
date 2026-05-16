<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminGradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Grade::with(['student.user', 'course'])
            ->orderByDesc('date');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $grades = $query->paginate($request->integer('limit', 30));

        $data = $grades->map(function (Grade $grade) {
            return [
                'id'        => $grade->id,
                'student'   => $grade->student?->user?->full_name ?? '—',
                'title'     => $grade->title,
                'type'      => $grade->type,
                'grade'     => (float) $grade->grade,
                'max_grade' => $grade->max_grade,
                'date'      => $grade->date?->toDateString(),
                'course'    => $grade->course?->title_pt ?? '—',
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $grades->total(),
                'page'      => $grades->currentPage(),
                'last_page' => $grades->lastPage(),
            ],
        ]);
    }

    public function update(Request $request, Grade $grade): JsonResponse
    {
        $validated = $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:' . $grade->max_grade],
        ]);

        $grade->update($validated);

        return response()->json([
            'id'    => $grade->id,
            'grade' => (float) $grade->grade,
        ]);
    }
}
