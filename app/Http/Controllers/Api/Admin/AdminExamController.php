<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $exams = Exam::with(['classGroup.course', 'teacher.user', 'assignments', 'questions'])
            ->withCount('questions')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByDesc('created_at')
            ->paginate($request->integer('limit', 20));

        $data = $exams->map(function (Exam $exam) {
            return [
                'id'             => $exam->id,
                'title'          => $exam->title,
                'status'         => $exam->status,
                'duration'       => $exam->duration,
                'max_attempts'   => $exam->max_attempts,
                'due_date'       => $exam->due_date?->toDateString(),
                'pass_score'     => $exam->pass_score,
                'questionCount'  => $exam->questions_count,
                'classGroupName' => $exam->classGroup?->name ?? '—',
                'class_group_id' => $exam->class_group_id,
                'teacher_id'     => $exam->teacher_id,
                'course'         => $exam->classGroup?->course?->title_pt ?? '—',
                'teacher'        => $exam->teacher?->user?->full_name ?? '—',
                'assignments'    => $exam->assignments->map(fn ($a) => [
                    'id'   => $a->assignable_id,
                    'type' => $a->assignable_type === \App\Models\ClassGroup::class ? 'class' : 'student'
                ]),
                'questions'      => $exam->questions->map(fn ($q) => [
                    'id'      => $q->id,
                    'text'    => $q->text,
                    'type'    => $q->type,
                    'options' => $q->options,
                    'correct' => $q->correct,
                    'points'  => $q->points,
                ])
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $exams->total(),
                'page'      => $exams->currentPage(),
                'last_page' => $exams->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:191'],
            'teacher_id'     => ['required', 'exists:teacher_profiles,id'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'course_id'      => ['nullable', 'exists:courses,id'],
            'duration'       => ['required', 'integer', 'min:1'],
            'max_attempts'   => ['nullable', 'integer', 'min:1'],
            'due_date'       => ['nullable', 'date'],
            'pass_score'     => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignments'    => ['nullable', 'array'],
            'assignments.*.type' => ['required', 'in:class,student'],
            'assignments.*.id'   => ['required', 'string'],
            'questions'      => ['required', 'array', 'min:1'],
            'questions.*.id'      => ['nullable', 'string'],
            'questions.*.text'    => ['required', 'string'],
            'questions.*.type'    => ['required', 'in:multiple_choice,true_false'],
            'questions.*.options' => ['required', 'array'],
            'questions.*.correct' => ['required', 'integer'],
            'questions.*.points'  => ['nullable', 'integer', 'min:1'],
        ]);

        $exam = DB::transaction(function () use ($validated) {
            $exam = Exam::create([
                'title'          => $validated['title'],
                'teacher_id'     => $validated['teacher_id'],
                'class_group_id' => $validated['class_group_id'] ?? null,
                'course_id'      => $validated['course_id'] ?? null,
                'duration'       => $validated['duration'],
                'max_attempts'   => $validated['max_attempts'] ?? 1,
                'status'         => 'draft',
                'due_date'       => $validated['due_date'] ?? null,
                'pass_score'     => $validated['pass_score'] ?? 50,
                'total_points'   => 0,
            ]);

            if (isset($validated['assignments'])) {
                foreach ($validated['assignments'] as $assignment) {
                    $modelClass = $assignment['type'] === 'class' ? \App\Models\ClassGroup::class : \App\Models\StudentProfile::class;
                    $exam->assignments()->create([
                        'assignable_type' => $modelClass,
                        'assignable_id'   => $assignment['id'],
                    ]);
                }
            }

            $totalPoints = 0;
            foreach ($validated['questions'] as $index => $q) {
                $points = $q['points'] ?? 10;
                $exam->questions()->create([
                    'text'    => $q['text'],
                    'type'    => $q['type'],
                    'options' => $q['options'],
                    'correct' => $q['correct'],
                    'points'  => $points,
                    'order'   => $index + 1,
                ]);
                $totalPoints += $points;
            }

            $exam->update(['total_points' => $totalPoints]);

            return $exam;
        });

        return response()->json([
            'id'     => $exam->id,
            'title'  => $exam->title,
            'status' => $exam->status,
        ], 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:191'],
            'teacher_id'     => ['required', 'exists:teacher_profiles,id'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'course_id'      => ['nullable', 'exists:courses,id'],
            'duration'       => ['required', 'integer', 'min:1'],
            'max_attempts'   => ['nullable', 'integer', 'min:1'],
            'due_date'       => ['nullable', 'date'],
            'pass_score'     => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignments'    => ['nullable', 'array'],
            'assignments.*.type' => ['required', 'in:class,student'],
            'assignments.*.id'   => ['required', 'string'],
            'questions'      => ['required', 'array', 'min:1'],
            'questions.*.id'      => ['nullable', 'string'],
            'questions.*.text'    => ['required', 'string'],
            'questions.*.type'    => ['required', 'in:multiple_choice,true_false'],
            'questions.*.options' => ['required', 'array'],
            'questions.*.correct' => ['required', 'integer'],
            'questions.*.points'  => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($exam, $validated) {
            $exam->update([
                'title'          => $validated['title'],
                'teacher_id'     => $validated['teacher_id'],
                'class_group_id' => $validated['class_group_id'] ?? null,
                'course_id'      => $validated['course_id'] ?? null,
                'duration'       => $validated['duration'],
                'max_attempts'   => $validated['max_attempts'] ?? 1,
                'due_date'       => $validated['due_date'] ?? null,
                'pass_score'     => $validated['pass_score'] ?? 50,
            ]);

            if (isset($validated['assignments'])) {
                $exam->assignments()->delete();
                foreach ($validated['assignments'] as $assignment) {
                    $modelClass = $assignment['type'] === 'class' ? \App\Models\ClassGroup::class : \App\Models\StudentProfile::class;
                    $exam->assignments()->create([
                        'assignable_type' => $modelClass,
                        'assignable_id'   => $assignment['id'],
                    ]);
                }
            }

            $existingQuestionIds = [];
            $totalPoints = 0;
            
            foreach ($validated['questions'] as $index => $q) {
                $points = $q['points'] ?? 10;
                $totalPoints += $points;

                if (!empty($q['id'])) {
                    $question = $exam->questions()->find($q['id']);
                    if ($question) {
                        $question->update([
                            'text'    => $q['text'],
                            'type'    => $q['type'],
                            'options' => $q['options'],
                            'correct' => $q['correct'],
                            'points'  => $points,
                            'order'   => $index + 1,
                        ]);
                        $existingQuestionIds[] = $question->id;
                        continue;
                    }
                }

                $newQuestion = $exam->questions()->create([
                    'text'    => $q['text'],
                    'type'    => $q['type'],
                    'options' => $q['options'],
                    'correct' => $q['correct'],
                    'points'  => $points,
                    'order'   => $index + 1,
                ]);
                $existingQuestionIds[] = $newQuestion->id;
            }

            $exam->questions()->whereNotIn('id', $existingQuestionIds)->delete();
            $exam->update(['total_points' => $totalPoints]);
        });

        return response()->json([
            'id'     => $exam->id,
            'title'  => $exam->title,
            'status' => $exam->status,
        ]);
    }

    public function publish(Exam $exam): JsonResponse
    {
        if ($exam->questions()->count() === 0) {
            return response()->json(['message' => 'O exame precisa de ter pelo menos uma pergunta.'], 422);
        }

        $exam->update(['status' => 'published']);

        return response()->json(['id' => $exam->id, 'status' => $exam->status]);
    }

    public function stats(Exam $exam): JsonResponse
    {
        $attempts = \App\Models\ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->with('student.user')
            ->get();

        $distribution = [
            '0-50' => $attempts->where('score', '<', 50)->count(),
            '50-70' => $attempts->whereBetween('score', [50, 69])->count(),
            '70-90' => $attempts->whereBetween('score', [70, 89])->count(),
            '90-100' => $attempts->where('score', '>=', 90)->count(),
        ];

        $ranking = $attempts->map(function ($attempt) {
            return [
                'student_id' => $attempt->student_id,
                'name' => $attempt->student->user->full_name,
                'score' => $attempt->score,
                'completed_at' => $attempt->completed_at?->toDateTimeString(),
                'passed' => $attempt->score >= ($attempt->exam->pass_score ?? 50),
            ];
        })->sortByDesc('score')->values();

        return response()->json([
            'totalStudents' => $exam->classGroup?->students()->count() ?? 0,
            'completedStudents' => $attempts->count(),
            'avgScore' => round($attempts->avg('score'), 1),
            'distribution' => $distribution,
            'ranking' => $ranking,
        ]);
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();

        return response()->json(null, 204);
    }
}
