<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminClassController extends Controller
{
    // ── GET /admin/classes ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'limit'     => ['nullable', 'integer', 'min:1', 'max:200'],
            'course_id' => ['nullable', 'string'],
            'search'    => ['nullable', 'string', 'max:120'],
            'active'    => ['nullable', 'in:0,1,true,false'],
        ]);

        $q = ClassGroup::query()
            ->with(['course:id,title_pt,title_en,level', 'schedules', 'teacher.user:id,first_name,last_name'])
            ->withCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'active')])
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN)))
            ->orderByDesc('year')
            ->orderBy('name');

        $paginator = $q->paginate($request->integer('limit', 20));

        $data = collect($paginator->items())->map(fn (ClassGroup $c) => $this->formatClass($c));

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $paginator->total(),
                'page'      => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    // ── POST /admin/classes ───────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'course_id'  => ['required', 'string', 'exists:courses,id'],
            'teacher_id' => ['required', 'string', 'exists:teacher_profiles,id'],
            'year'       => ['required', 'integer', 'min:2020', 'max:2100'],
            'capacity'   => ['required', 'integer', 'min:1', 'max:200'],
            'room'       => ['nullable', 'string', 'max:60'],
            'schedules'  => ['nullable', 'array'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time'  => ['required', 'date_format:H:i'],
            'schedules.*.end_time'    => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.room'        => ['nullable', 'string', 'max:60'],
        ]);

        $class = ClassGroup::create([
            'name'       => $validated['name'],
            'course_id'  => $validated['course_id'],
            'teacher_id' => $validated['teacher_id'],
            'year'       => $validated['year'],
            'capacity'   => $validated['capacity'],
            'room'       => $validated['room'] ?? null,
            'is_active'  => true,
        ]);

        // Criar conversa de grupo para a turma
        $chatConv = \App\Models\ChatConversation::create([
            'type' => 'group',
            'name' => 'Turma: ' . $class->name,
            'class_id' => $class->id
        ]);

        // Adicionar professor à conversa
        if ($class->teacher && $class->teacher->user_id) {
            $chatConv->participants()->attach($class->teacher->user_id, ['joined_at' => now()]);
        }

        foreach ($validated['schedules'] ?? [] as $s) {
            $class->schedules()->create([
                'day_of_week' => $s['day_of_week'],
                'start_time'  => $s['start_time'],
                'end_time'    => $s['end_time'],
                'room'        => $s['room'] ?? null,
                'is_recurring' => true,
            ]);
        }

        $class->loadMissing(['course:id,title_pt,title_en,level', 'schedules', 'teacher.user:id,first_name,last_name']);
        $class->loadCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'active')]);

        return response()->json(['data' => $this->formatClass($class)], 201);
    }

    // ── PUT /admin/classes/{class} ────────────────────────────────────────────

    public function update(Request $request, ClassGroup $class): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100'],
            'capacity'  => ['sometimes', 'integer', 'min:1', 'max:200'],
            'room'      => ['sometimes', 'nullable', 'string', 'max:60'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $class->update($validated);

        $class->loadMissing(['course:id,title_pt,title_en,level', 'schedules', 'teacher.user:id,first_name,last_name']);
        $class->loadCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'active')]);

        return response()->json(['data' => $this->formatClass($class)]);
    }

    // ── POST /admin/classes/{class}/students ──────────────────────────────────

    public function addStudent(Request $request, ClassGroup $class): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string', 'exists:student_profiles,id'],
        ]);

        if (! $class->hasCapacity()) {
            return response()->json(['message' => 'Turma sem vagas disponíveis.'], 422);
        }

        $enrollment = Enrollment::firstOrCreate(
            ['student_id' => $validated['student_id'], 'class_group_id' => $class->id],
            [
                'course_id'    => $class->course_id,
                'start_date'   => now()->toDateString(),
                'status'       => 'active',
                'progress_pct' => 0,
            ]
        );

        // Adicionar aluno à conversa de grupo da turma
        $chatConv = \App\Models\ChatConversation::where('class_id', $class->id)->first();
        if ($chatConv && $enrollment->student && $enrollment->student->user_id) {
            $chatConv->participants()->syncWithoutDetaching([
                $enrollment->student->user_id => ['joined_at' => now()]
            ]);
        }

        return response()->json(['message' => 'Aluno adicionado à turma.']);
    }

    // ── POST /admin/classes/{class}/schedules ─────────────────────────────────

    public function addSchedule(Request $request, ClassGroup $class): JsonResponse
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time'],
            'room'        => ['nullable', 'string', 'max:60'],
        ]);

        $schedule = $class->schedules()->create([
            'day_of_week'  => $validated['day_of_week'],
            'start_time'   => $validated['start_time'],
            'end_time'     => $validated['end_time'],
            'room'         => $validated['room'] ?? null,
            'is_recurring' => true,
        ]);

        return response()->json([
            'data' => [
                'id'          => $schedule->id,
                'day_of_week' => $schedule->day_of_week,
                'start_time'  => $schedule->start_time,
                'end_time'    => $schedule->end_time,
                'room'        => $schedule->room,
            ],
        ], 201);
    }

    // ── DELETE /admin/classes/{class}/schedules/{schedule} ────────────────────

    public function removeSchedule(ClassGroup $class, ClassSchedule $schedule): JsonResponse
    {
        if ($schedule->class_group_id !== $class->id) {
            return response()->json(['message' => 'Horário não pertence a esta turma.'], 422);
        }

        $schedule->delete();

        return response()->json(null, 204);
    }

    // ── Private helper ────────────────────────────────────────────────────────

    private function formatClass(ClassGroup $c): array
    {
        $teacherName = null;
        if ($c->relationLoaded('teacher') && $c->teacher?->relationLoaded('user') && $c->teacher->user) {
            $teacherName = trim($c->teacher->user->first_name . ' ' . $c->teacher->user->last_name);
        }

        return [
            'id'             => $c->id,
            'name'           => $c->name,
            'year'           => $c->year,
            'capacity'       => $c->capacity,
            'room'           => $c->room,
            'is_active'      => $c->is_active,
            'students_count' => $c->students_count ?? 0,
            'teacher'        => $teacherName,
            'course'         => $c->course ? [
                'id'       => $c->course->id,
                'title_pt' => $c->course->title_pt,
                'title_en' => $c->course->title_en,
                'level'    => $c->course->level,
            ] : null,
            'schedules' => $c->schedules->map(fn (ClassSchedule $s) => [
                'id'          => $s->id,
                'day_of_week' => $s->day_of_week,
                'start_time'  => substr($s->start_time, 0, 5),
                'end_time'    => substr($s->end_time, 0, 5),
                'room'        => $s->room,
            ])->values()->toArray(),
        ];
    }
}