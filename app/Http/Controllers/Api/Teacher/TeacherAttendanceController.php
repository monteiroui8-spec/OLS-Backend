<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $request->validate([
            'class_group_id' => 'required',
            'date'           => 'nullable|date',
        ]);

        $classGroupId = $request->get('class_group_id');
        $date         = $request->get('date', now()->toDateString());

        // Verify class belongs to this teacher
        $classGroup = ClassGroup::where('id', $classGroupId)
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$classGroup) {
            return response()->json(['message' => 'Turma não encontrada ou sem permissão.'], 404);
        }

        // All active enrollments for that class
        $enrollments = Enrollment::where('class_group_id', $classGroupId)
            ->whereIn('status', ['active', 'enrolled'])
            ->with('student.user')
            ->get();

        // Existing attendance records for that date
        $attendances = Attendance::where('class_group_id', $classGroupId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        $records = $enrollments->map(function (Enrollment $enrollment) use ($attendances) {
            $att = $attendances->get($enrollment->student_id);
            return [
                'student_id'    => $enrollment->student_id,
                'student_name'  => $enrollment->student?->user?->full_name ?? '—',
                'enrollment_id' => $enrollment->id,
                'attendance'    => $att ? [
                    'id'     => $att->id,
                    'status' => $att->status,
                    'notes'  => $att->notes,
                ] : null,
            ];
        });

        return response()->json([
            'class_group' => [
                'id'   => $classGroup->id,
                'name' => $classGroup->name,
            ],
            'date'    => $date,
            'records' => $records->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $validated = $request->validate([
            'class_group_id'          => 'required',
            'date'                    => 'required|date',
            'records'                 => 'required|array|min:1',
            'records.*.student_id'    => 'required',
            'records.*.status'        => 'required|in:present,absent,late,justified',
            'records.*.notes'         => 'nullable|string|max:500',
        ]);

        $saved = 0;
        foreach ($validated['records'] as $record) {
            Attendance::updateOrCreate(
                [
                    'class_group_id' => $validated['class_group_id'],
                    'student_id'     => $record['student_id'],
                    'date'           => $validated['date'],
                ],
                [
                    'teacher_id' => $teacher->id,
                    'status'     => $record['status'],
                    'notes'      => $record['notes'] ?? null,
                ]
            );
            $saved++;
        }

        return response()->json([
            'message' => 'Presenças guardadas com sucesso.',
            'saved'   => $saved,
        ]);
    }

    public function dates(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $request->validate([
            'class_group_id' => 'required',
        ]);

        $dates = Attendance::where('class_group_id', $request->get('class_group_id'))
            ->selectRaw('DISTINCT DATE(date) as attendance_date')
            ->orderByDesc('attendance_date')
            ->pluck('attendance_date')
            ->map(fn($d) => (string) $d);

        return response()->json(['dates' => $dates]);
    }
}
