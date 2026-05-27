<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Notifications\GradeAssignedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;

        // Pre-load this student's completed attempts indexed by exam_id
        $attempts = ExamAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderByDesc('submitted_at')
            ->get()
            ->groupBy('exam_id');

        $exams = Exam::where(function ($query) use ($student) {
            // exams assigned directly to the student's class group via class_group_id
            $query->whereHas('classGroup.enrollments', function ($q) use ($student) {
                $q->where('student_id', $student->id)->where('status', 'active');
            })
            // OR exams assigned via exam_assignments table
            ->orWhereHas('assignments', function ($q) use ($student) {
                $q->where(function ($sub) use ($student) {
                    // assigned to the student directly
                    $sub->where('assignable_type', \App\Models\StudentProfile::class)
                        ->where('assignable_id', $student->id);
                })->orWhere(function ($sub) use ($student) {
                    // assigned to one of the student's active classes
                    $sub->where('assignable_type', \App\Models\ClassGroup::class)
                        ->whereIn('assignable_id', function ($query) use ($student) {
                            $query->select('class_group_id')
                                ->from('enrollments')
                                ->where('student_id', $student->id)
                                ->where('status', 'active');
                        });
                });
            });
        })
            ->published()
            ->orderBy('due_date')
            ->get()
            ->map(function (Exam $exam) use ($attempts) {
                // Best completed attempt for this exam
                $examAttempts = $attempts->get($exam->id);
                $bestAttempt  = $examAttempts?->sortByDesc('score')->first();

                return [
                    'id'          => $exam->id,
                    'title'       => $exam->title,
                    'status'      => $exam->status,
                    'due_date'    => $exam->due_date,
                    'duration'    => $exam->duration,
                    'pass_score'  => $exam->pass_score,
                    // Attempt data (null if not yet completed)
                    'attempt_id'  => $bestAttempt?->id,
                    'score'       => $bestAttempt ? (float) $bestAttempt->score : null,
                    'passed'      => $bestAttempt ? (bool) $bestAttempt->passed : null,
                    'submitted_at'=> $bestAttempt?->submitted_at?->toISOString(),
                    'attempt_count'=> $examAttempts?->count() ?? 0,
                ];
            });

        return response()->json([
            'data' => $exams,
        ]);
    }

    public function start(Request $request, Exam $exam): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if ($exam->status !== 'published') {
            return response()->json([
                'message' => 'Este exame não está disponível.',
            ], 403);
        }

        if ($exam->due_date && now()->isAfter($exam->due_date)) {
            return response()->json([
                'message' => 'O prazo para este exame expirou.',
            ], 403);
        }

        $attemptCount = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->count();

        if ($attemptCount >= $exam->max_attempts) {
            return response()->json([
                'message' => 'Já atingiu o número máximo de tentativas.',
            ], 422);
        }

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'attemptId' => $attempt->id,
            'timeLimit' => $exam->duration * 60,
            'dueAt' => now()->addMinutes($exam->duration),
            'questions' => $exam->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'text' => $q->text,
                    'type' => $q->type,
                    'options' => $q->options,
                    'points' => $q->points,
                ];
            }),
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if ($attempt->student_id !== $student->id) {
            return response()->json([
                'message' => 'Acesso negado.',
            ], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'message' => 'Esta tentativa já foi submetida.',
            ], 422);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.questionId' => ['required'],
            'answers.*.answer' => ['required'],
        ]);

        $exam = $attempt->exam()->with('questions')->firstOrFail();
        $answers = collect($data['answers']);
        $correct = 0;
        $totalPts = 0;
        $earnedPts = 0;
        $breakdown = [];

        DB::transaction(function () use ($attempt, $exam, $answers, &$correct, &$totalPts, &$earnedPts, &$breakdown) {
            foreach ($exam->questions as $question) {
                $given = $answers->firstWhere('questionId', $question->id);
                $answerValue = $given ? $given['answer'] : -1;
                $isCorrect = $answerValue === $question->correct;

                if ($isCorrect) {
                    $correct++;
                    $earnedPts += $question->points;
                }

                $totalPts += $question->points;

                ExamAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer' => $answerValue,
                    'is_correct' => $isCorrect,
                ]);

                $breakdown[] = [
                    'questionId' => $question->id,
                    'correct' => $isCorrect,
                    'yourAnswer' => $answerValue,
                    'correctAnswer' => $question->correct,
                ];
            }

            $score = $totalPts > 0 ? round($earnedPts / $totalPts * 100, 2) : 0;
            $passed = $score >= $exam->pass_score;

            $attempt->update([
                'status' => 'completed',
                'submitted_at' => now(),
                'score' => $score,
                'passed' => $passed,
            ]);

            $grade = Grade::create([
                'student_id' => $attempt->student_id,
                'teacher_id' => $exam->teacher_id,
                'title' => $exam->title,
                'type' => 'Exam',
                'course_id' => $exam->course_id,
                'class_group_id' => $exam->class_group_id,
                'grade' => $score,
                'max_grade' => 100,
                'date' => now()->toDateString(),
            ]);

            // Notify student about exam grade
            try {
                $attempt->loadMissing('student.user');
                $studentUser = $attempt->student?->user;
                if ($studentUser) {
                    $studentUser->notify(new GradeAssignedNotification($grade));
                }
            } catch (\Throwable) { /* Non-fatal */ }
        });

        return response()->json([
            'score' => $attempt->score,
            'passed' => $attempt->passed,
            'correctCount' => $correct,
            'totalQuestions' => $exam->questions->count(),
            'breakdown' => $breakdown,
        ]);
    }

    public function result(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if ($attempt->student_id !== $student->id) {
            return response()->json([
                'message' => 'Acesso negado.',
            ], 403);
        }

        $attempt->load(['exam', 'answers.question']);

        $breakdown = $attempt->answers->map(function (ExamAnswer $answer) {
            $opts = $answer->question?->options ?? [];
            $yourIdx = $answer->answer;
            $correctIdx = $answer->question?->correct;
            return [
                'questionId' => $answer->question_id,
                'text' => $answer->question?->text,
                'yourAnswer' => is_int($yourIdx) && isset($opts[$yourIdx]) ? $opts[$yourIdx] : null,
                'correctAnswer' => is_int($correctIdx) && isset($opts[$correctIdx]) ? $opts[$correctIdx] : null,
                'correct' => $answer->is_correct,
            ];
        });

        return response()->json([
            'score' => $attempt->score,
            'passed' => $attempt->passed,
            'submittedAt' => $attempt->submitted_at,
            'exam' => [
                'id' => $attempt->exam?->id,
                'title' => $attempt->exam?->title,
                'passScore' => $attempt->exam?->pass_score,
            ],
            'breakdown' => $breakdown,
        ]);
    }

    public function ranking(Exam $exam): JsonResponse
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->with('student.user')
            ->get()
            ->map(function ($attempt) {
                return [
                    'name' => $attempt->student->user->full_name,
                    'score' => $attempt->score,
                    'date' => $attempt->submitted_at?->toDateString(),
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(10);

        return response()->json([
            'ranking' => $attempts,
        ]);
    }
}
