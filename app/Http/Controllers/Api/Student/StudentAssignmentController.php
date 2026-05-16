<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentAssignmentController extends Controller
{
    private function storageDisk(): string
    {
        $key = config('filesystems.disks.s3.key');
        return ($key && $key !== '') ? 's3' : 'public';
    }

    private function getEnrolledIds($profile): array
    {
        if (!$profile) {
            return [[], []];
        }

        $enrollments = $profile->enrollments()
            ->where('status', 'active')
            ->get(['class_group_id', 'course_id']);

        $classIds  = $enrollments->pluck('class_group_id')->filter()->unique()->values()->toArray();
        $courseIds = $enrollments->pluck('course_id')->filter()->unique()->values()->toArray();

        return [$classIds, $courseIds];
    }

    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->studentProfile;

        if (!$profile) {
            return response()->json(['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'last_page' => 1]]);
        }

        [$classIds, $courseIds] = $this->getEnrolledIds($profile);

        if (empty($classIds) && empty($courseIds)) {
            return response()->json(['data' => [], 'meta' => ['total' => 0, 'page' => 1, 'last_page' => 1]]);
        }

        $assignments = Assignment::where('is_published', true)
            ->where(function ($q) use ($classIds, $courseIds) {
                $q->when(!empty($classIds),  fn ($q) => $q->orWhereIn('class_group_id', $classIds))
                  ->when(!empty($courseIds), fn ($q) => $q->orWhereIn('course_id', $courseIds));
            })
            ->with(['course', 'classGroup', 'teacher.user'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('limit', 50));

        $data = $assignments->map(function (Assignment $a) use ($profile) {
            $submission = AssignmentSubmission::where('assignment_id', $a->id)
                ->where('student_id', $profile->id)
                ->first();

            return [
                'id'            => $a->id,
                'title'         => $a->title,
                'description'   => $a->description,
                'course'        => $a->course?->getTitle(app()->getLocale()),
                'class'         => $a->classGroup?->name,
                'teacher'       => $a->teacher?->user?->full_name,
                'dueDate'       => $a->due_date?->toDateString(),
                'points'        => $a->points,
                'attachmentUrl' => $a->attachment_url,
                'status'        => $a->due_date && $a->due_date->isPast() ? 'closed' : 'active',
                'submission'    => $submission ? [
                    'id'              => $submission->id,
                    'status'          => $submission->status,
                    'grade'           => $submission->grade,
                    'feedback'        => $submission->teacher_feedback,
                    'submittedAt'     => $submission->submitted_at?->toISOString(),
                    'fileUrl'         => $submission->file_url,
                    'studentNotes'    => $submission->student_notes,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $assignments->total(),
                'page'      => $assignments->currentPage(),
                'last_page' => $assignments->lastPage(),
            ],
        ]);
    }

    public function submit(Request $request, Assignment $assignment): JsonResponse
    {
        $profile = $request->user()->studentProfile;

        if (!$profile) {
            return response()->json(['message' => 'Perfil de estudante não encontrado.'], 403);
        }

        $validated = $request->validate([
            'file'          => 'nullable|file|max:51200',
            'student_notes' => 'nullable|string|max:2000',
        ]);

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $profile->id)
            ->first();

        $fileUrl = $existing?->file_url;

        if ($request->hasFile('file')) {
            $disk = $this->storageDisk();
            $path = $request->file('file')->store('assignment-submissions', $disk);
            $fileUrl = $disk === 's3'
                ? Storage::disk('s3')->temporaryUrl($path, now()->addWeek())
                : Storage::disk('public')->url($path);
        }

        $isLate = $assignment->due_date && $assignment->due_date->isPast();

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $profile->id],
            [
                'file_url'      => $fileUrl,
                'student_notes' => $validated['student_notes'] ?? null,
                'submitted_at'  => now(),
                'status'        => $existing && $existing->status === 'graded' ? 'graded' : ($isLate ? 'late' : 'pending'),
            ]
        );

        return response()->json([
            'id'           => $submission->id,
            'status'       => $submission->status,
            'submittedAt'  => $submission->submitted_at?->toISOString(),
            'fileUrl'      => $submission->file_url,
            'studentNotes' => $submission->student_notes,
        ], 201);
    }
}
