<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Notifications\ExamPublishedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherExamController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function formatExam(Exam $exam): array
    {
        return [
            'id'               => $exam->id,
            'title'            => $exam->title,
            'class_group_id'   => $exam->class_group_id,
            'classGroupName'   => $exam->classGroup?->name ?? '—',
            'course'           => $exam->classGroup?->course?->title_pt ?? '—',
            'duration'         => $exam->duration,
            'max_attempts'     => $exam->max_attempts,
            'pass_score'       => $exam->pass_score,
            'due_date'         => $exam->due_date?->toDateString(),
            'start_date'       => $exam->start_date?->toDateString(),
            'end_date'         => $exam->end_date?->toDateString(),
            'status'           => $exam->status,
            'questionCount'    => $exam->questions->count(),
            'totalStudents'    => collect([
                $exam->classGroup?->students()->count() ?? 0,
                $exam->assignments->where('assignable_type', \App\Models\StudentProfile::class)->count(),
            ])->sum(),
            'submissionsCount' => $exam->attempts->where('status', 'completed')->count(),
            'assignments'      => $exam->assignments->map(fn ($a) => [
                'id'   => $a->assignable_id,
                'type' => $a->assignable_type === \App\Models\ClassGroup::class ? 'class' : 'student',
            ]),
            'questions'        => $exam->questions->map(fn ($q) => [
                'id'      => $q->id,
                'text'    => $q->text,
                'type'    => $q->type,
                'options' => $q->options,
                'correct' => $q->correct,
                'points'  => $q->points,
            ]),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        $exams = Exam::where('teacher_id', $teacher->id)
            ->with(['classGroup.course', 'questions', 'assignments', 'attempts'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('limit', 20));

        $data = $exams->map(fn (Exam $exam) => $this->formatExam($exam));

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
        $teacher = $request->user()->teacherProfile;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'duration' => ['required', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'pass_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignments'    => ['nullable', 'array'],
            'assignments.*.type' => ['required', 'in:class,student'],
            'assignments.*.id'   => ['required', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'string'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:multiple_choice,true_false,open_text'],
            'questions.*.options' => ['required_if:questions.*.type,multiple_choice,true_false', 'array'],
            'questions.*.correct' => ['required', 'integer'],
            'questions.*.points' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! empty($validated['class_group_id'])) {
            ClassGroup::where('id', $validated['class_group_id'])
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
        }

        $exam = DB::transaction(function () use ($validated, $teacher) {
            $exam = Exam::create([
                'title' => $validated['title'],
                'class_group_id' => $validated['class_group_id'] ?? null,
                'course_id' => $validated['course_id'] ?? null,
                'teacher_id' => $teacher->id,
                'duration' => $validated['duration'],
                'max_attempts' => $validated['max_attempts'] ?? 1,
                'status'     => 'draft',
                'due_date'   => $validated['due_date'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date'   => $validated['end_date'] ?? null,
                'pass_score' => $validated['pass_score'] ?? 50,
                'total_points' => 0,
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
                    'text' => $q['text'],
                    'type' => $q['type'],
                    'options' => $q['options'] ?? [],
                    'correct' => $q['correct'],
                    'points' => $points,
                    'order' => $index + 1,
                ]);
                $totalPoints += $points;
            }

            $exam->update(['total_points' => $totalPoints]);

            return $exam;
        });

        $exam->load(['classGroup.course', 'questions', 'assignments', 'attempts']);

        return response()->json($this->formatExam($exam), 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if ($exam->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'duration' => ['required', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'pass_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignments'    => ['nullable', 'array'],
            'assignments.*.type' => ['required', 'in:class,student'],
            'assignments.*.id'   => ['required', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'string'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:multiple_choice,true_false,open_text'],
            'questions.*.options' => ['required_if:questions.*.type,multiple_choice,true_false', 'array'],
            'questions.*.correct' => ['required', 'integer'],
            'questions.*.points' => ['nullable', 'integer', 'min:1'],
        ]);

        if (! empty($validated['class_group_id'])) {
            ClassGroup::where('id', $validated['class_group_id'])
                ->where('teacher_id', $teacher->id)
                ->firstOrFail();
        }

        DB::transaction(function () use ($exam, $validated) {
            $exam->update([
                'title' => $validated['title'],
                'class_group_id' => $validated['class_group_id'] ?? null,
                'course_id' => $validated['course_id'] ?? null,
                'duration' => $validated['duration'],
                'max_attempts' => $validated['max_attempts'] ?? 1,
                'due_date'   => $validated['due_date'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date'   => $validated['end_date'] ?? null,
                'pass_score' => $validated['pass_score'] ?? 50,
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
                            'text' => $q['text'],
                            'type' => $q['type'],
                            'options' => $q['options'] ?? [],
                            'correct' => $q['correct'],
                            'points' => $points,
                            'order' => $index + 1,
                        ]);
                        $existingQuestionIds[] = $question->id;
                        continue;
                    }
                }

                $newQuestion = $exam->questions()->create([
                    'text' => $q['text'],
                    'type' => $q['type'],
                    'options' => $q['options'] ?? [],
                    'correct' => $q['correct'],
                    'points' => $points,
                    'order' => $index + 1,
                ]);
                $existingQuestionIds[] = $newQuestion->id;
            }

            $exam->questions()->whereNotIn('id', $existingQuestionIds)->delete();
            $exam->update(['total_points' => $totalPoints]);
        });

        $exam->load(['classGroup.course', 'questions', 'assignments', 'attempts']);

        return response()->json($this->formatExam($exam));
    }

    public function publish(Request $request, Exam $exam): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if ($exam->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'Acesso negado.',
            ], 403);
        }

        if ($exam->questions()->count() === 0) {
            return response()->json([
                'message' => 'O exame precisa de ter pelo menos uma pergunta.',
            ], 422);
        }

        $exam->update(['status' => 'published']);

        $exam->load(['classGroup.course', 'questions', 'assignments', 'attempts']);

        // Notify enrolled students
        try {
            if ($exam->class_group_id) {
                $enrollments = Enrollment::where('class_group_id', $exam->class_group_id)
                    ->whereIn('status', ['active', 'enrolled'])
                    ->with('student.user')
                    ->get();
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->student?->user) {
                        $enrollment->student->user->notify(new ExamPublishedNotification($exam));
                    }
                }
            }
        } catch (\Throwable) {
            // Non-fatal
        }

        return response()->json($this->formatExam($exam));
    }

    public function stats(Request $request, Exam $exam): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if ($exam->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'Acesso negado.',
            ], 403);
        }

        $attempts = ExamAttempt::where('exam_id', $exam->id)
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
}

