<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /** GET /admin/attendance */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'class_group_id' => ['nullable', 'string', 'exists:class_groups,id'],
            'student_id'     => ['nullable', 'string', 'exists:student_profiles,id'],
            'date_from'      => ['nullable', 'date'],
            'date_to'        => ['nullable', 'date'],
            'status'         => ['nullable', 'in:present,absent,late,justified'],
            'limit'          => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $q = Attendance::query()
            ->with(['student.user', 'classGroup', 'teacher.user'])
            ->when($request->filled('class_group_id'), fn ($q) => $q->where('class_group_id', $request->class_group_id))
            ->when($request->filled('student_id'),     fn ($q) => $q->where('student_id', $request->student_id))
            ->when($request->filled('status'),         fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'),      fn ($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'),        fn ($q) => $q->where('date', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . mb_strtolower($request->search) . '%';
                $q->whereHas('student.user', fn ($uq) =>
                    $uq->whereRaw('LOWER(first_name) LIKE ?', [$s])
                       ->orWhereRaw('LOWER(last_name) LIKE ?', [$s])
                );
            })
            ->orderByDesc('date')
            ->orderBy('class_group_id');

        $paginator = $q->paginate($request->integer('limit', 50));

        $data = collect($paginator->items())->map(fn (Attendance $a) => [
            'id'          => $a->id,
            'date'        => $a->date instanceof \Carbon\Carbon ? $a->date->toDateString() : (string) $a->date,
            'status'      => $a->status,
            'notes'       => $a->notes,
            'student'     => [
                'id'   => $a->student_id,
                'name' => $a->student?->user?->full_name ?? '—',
            ],
            'class_group' => [
                'id'   => $a->class_group_id,
                'name' => $a->classGroup?->name ?? '—',
            ],
            'teacher'     => $a->teacher?->user?->full_name ?? '—',
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $paginator->total(),
                'page'      => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /** GET /admin/attendance/summary */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'class_group_id' => ['nullable', 'string', 'exists:class_groups,id'],
        ]);

        $q = Attendance::query()
            ->with(['student.user', 'classGroup'])
            ->when($request->filled('class_group_id'), fn ($q) => $q->where('class_group_id', $request->class_group_id))
            ->selectRaw('student_id, class_group_id,
                COUNT(*) as total,
                SUM(status = "present")   as present,
                SUM(status = "absent")    as absent,
                SUM(status = "late")      as late,
                SUM(status = "justified") as justified')
            ->groupBy('student_id', 'class_group_id');

        $rows = $q->get();

        // Eager load names
        $studentIds    = $rows->pluck('student_id')->unique();
        $classGroupIds = $rows->pluck('class_group_id')->unique();

        $students    = StudentProfile::whereIn('id', $studentIds)->with('user')->get()->keyBy('id');
        $classGroups = ClassGroup::whereIn('id', $classGroupIds)->get()->keyBy('id');

        $data = $rows->map(function ($row) use ($students, $classGroups) {
            $total     = (int) $row->total;
            $present   = (int) $row->present;
            $ratePct   = $total > 0 ? round(($present / $total) * 100) : 0;

            return [
                'student_id'   => $row->student_id,
                'student_name' => $students->get($row->student_id)?->user?->full_name ?? '—',
                'class_name'   => $classGroups->get($row->class_group_id)?->name ?? '—',
                'class_group_id' => $row->class_group_id,
                'total'        => $total,
                'present'      => $present,
                'absent'       => (int) $row->absent,
                'late'         => (int) $row->late,
                'justified'    => (int) $row->justified,
                'rate_pct'     => $ratePct,
            ];
        })->sortByDesc('rate_pct')->values();

        return response()->json(['data' => $data]);
    }
}
