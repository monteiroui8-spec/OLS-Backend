<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    /** GET /student/attendance */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;
        if (!$student) {
            return response()->json(['message' => 'Perfil de aluno não encontrado.'], 403);
        }

        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'status'    => ['nullable', 'in:present,absent,late,justified'],
        ]);

        $records = Attendance::where('student_id', $student->id)
            ->with(['classGroup', 'teacher.user'])
            ->when($request->filled('date_from'), fn ($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->where('date', '<=', $request->date_to))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('date')
            ->get();

        $total     = $records->count();
        $present   = $records->where('status', 'present')->count();
        $absent    = $records->where('status', 'absent')->count();
        $late      = $records->where('status', 'late')->count();
        $justified = $records->where('status', 'justified')->count();
        $ratePct   = $total > 0 ? round(($present / $total) * 100) : 0;

        $data = $records->map(fn (Attendance $a) => [
            'id'          => $a->id,
            'date'        => $a->date instanceof \Carbon\Carbon ? $a->date->toDateString() : (string) $a->date,
            'status'      => $a->status,
            'notes'       => $a->notes,
            'class_group' => [
                'id'   => $a->class_group_id,
                'name' => $a->classGroup?->name ?? '—',
            ],
            'teacher_name' => $a->teacher?->user?->full_name ?? '—',
        ]);

        return response()->json([
            'data'    => $data,
            'summary' => [
                'total'     => $total,
                'present'   => $present,
                'absent'    => $absent,
                'late'      => $late,
                'justified' => $justified,
                'rate_pct'  => $ratePct,
            ],
        ]);
    }
}
