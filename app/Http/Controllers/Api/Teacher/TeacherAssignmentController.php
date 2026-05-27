<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeacherAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->withCount('submissions')
            ->with(['course', 'classGroup'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'description' => $a->description,
                'course' => $a->course?->getTitle(app()->getLocale()) ?? 'N/A',
                'class' => $a->classGroup?->name ?? 'N/A',
                'dueDate' => $a->due_date?->toDateString(),
                'points' => $a->points,
                'attachmentUrl' => $a->attachment_url,
                'submissions' => $a->submissions_count,
                'status' => $a->due_date && $a->due_date->isPast() ? 'closed' : 'active',
            ]);

        $submissions = AssignmentSubmission::whereHas('assignment', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
            ->with(['student.user', 'assignment'])
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'student' => $s->student->user->full_name,
                'avatar' => $s->student->user->full_name[0],
                'assignment' => $s->assignment->title,
                'date' => $s->submitted_at->format('M d, Y'),
                'file' => $s->file_url,
                'status' => ucfirst($s->status),
                'grade' => $s->grade,
            ]);

        return response()->json([
            'assignments' => $assignments,
            'submissions' => $submissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'Perfil de professor não encontrado.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable',
            'class_group_id' => 'nullable',
            'due_date' => 'nullable|date',
            'points' => 'nullable|integer|min:0',
            'attachment' => 'nullable|file|max:51200',
        ]);

        $attachmentUrl = null;
        if ($request->hasFile('attachment')) {
            $disk = config('filesystems.disks.s3.key') ? 's3' : 'public';
            $path = $request->file('attachment')->store('assignment-attachments', $disk);
            $attachmentUrl = $disk === 's3'
                ? Storage::disk('s3')->temporaryUrl($path, now()->addWeek())
                : Storage::disk('public')->url($path);
        }

        $assignment = Assignment::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'class_group_id' => $validated['class_group_id'] ?? null,
            'teacher_id' => $teacher->id,
            'due_date' => $validated['due_date'] ?? null,
            'points' => $validated['points'] ?? 100,
            'attachment_url' => $attachmentUrl,
        ]);

        // Notify enrolled students in the class group
        if ($assignment->class_group_id) {
            try {
                $enrollments = Enrollment::where('class_group_id', $assignment->class_group_id)
                    ->whereIn('status', ['active', 'enrolled'])
                    ->with('student.user')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    if ($enrollment->student?->user) {
                        $enrollment->student->user->notify(new AssignmentCreatedNotification($assignment));
                    }
                }
            } catch (\Throwable) {
                // Non-fatal
            }
        }

        $assignment->load(['course', 'classGroup']);

        return response()->json([
            'id'            => $assignment->id,
            'title'         => $assignment->title,
            'description'   => $assignment->description,
            'course'        => $assignment->course?->getTitle(app()->getLocale()) ?? 'N/A',
            'class'         => $assignment->classGroup?->name ?? 'N/A',
            'class_group_id'=> $assignment->class_group_id,
            'course_id'     => $assignment->course_id,
            'dueDate'       => $assignment->due_date?->toDateString(),
            'points'        => $assignment->points,
            'attachmentUrl' => $assignment->attachment_url,
            'submissions'   => 0,
            'status'        => $assignment->due_date && $assignment->due_date->isPast() ? 'closed' : 'active',
        ], 201);
    }

    public function grade(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        if ($submission->assignment->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'grade' => $validated['grade'],
            'teacher_feedback' => $validated['feedback'] ?? null,
            'status' => 'graded',
        ]);

        // Also add to grades table
        \App\Models\Grade::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'teacher_id' => $teacher->id,
                'title' => "Trabalho: " . $submission->assignment->title,
            ],
            [
                'type' => 'Assignment',
                'course_id' => $submission->assignment->course_id,
                'class_group_id' => $submission->assignment->class_group_id,
                'grade' => $validated['grade'],
                'max_grade' => $submission->assignment->points,
                'date' => now()->toDateString(),
            ]
        );

        return response()->json($submission);
    }
}
