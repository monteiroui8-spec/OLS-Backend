<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Material;
use App\Notifications\MaterialPublishedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherMaterialController extends Controller
{
    /* ─── Helpers ──────────────────────────────────────────────── */

    private function storageDisk(): string
    {
        $key = config('filesystems.disks.s3.key');
        return ($key && $key !== '') ? 's3' : 'public';
    }

    private function formatMaterial(Material $m): array
    {
        return [
            'id'           => $m->id,
            'title'        => $m->title,
            'description'  => $m->description,
            'type'         => $m->type,
            'mimeType'     => $m->mime_type,
            'sizeBytes'    => $m->size_bytes,
            'externalUrl'  => $m->external_url,
            'isPublished'  => $m->is_published,
            'views'        => $m->views,
            'downloads'    => $m->downloads,
            'classGroup'   => $m->classGroup?->name,
            'classGroupId' => $m->class_group_id,
            'course'       => $m->course?->getTitle(app()->getLocale()),
            'courseId'     => $m->course_id,
            'uploadedBy'   => $m->uploadedBy?->full_name,
            'createdAt'    => $m->created_at,
        ];
    }

    /* ─── LIST — materials uploaded by this teacher ─────────────── */

    public function index(Request $request): JsonResponse
    {
        $teacher = $request->user()->teacherProfile;

        $query = Material::where('uploaded_by_id', $request->user()->id)
            ->with(['classGroup', 'course', 'uploadedBy']);

        if ($request->filled('class_group_id')) {
            $query->where('class_group_id', $request->get('class_group_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->get('search') . '%');
        }

        $materials = $query->orderByDesc('created_at')
            ->paginate($request->integer('limit', 20));

        return response()->json([
            'data' => $materials->map(fn (Material $m) => $this->formatMaterial($m)),
            'meta' => [
                'total'     => $materials->total(),
                'page'      => $materials->currentPage(),
                'last_page' => $materials->lastPage(),
            ],
        ]);
    }

    /* ─── UPLOAD ─────────────────────────────────────────────────── */

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'file'           => ['nullable', 'file', 'max:512000'],
            'external_url'   => ['nullable', 'url', 'max:2048'],
            'class_group_id' => ['nullable', 'exists:class_groups,id'],
            'course_id'      => ['nullable', 'exists:courses,id'],
            'is_published'   => ['nullable', 'boolean'],
        ]);

        // Must have either a file or an external URL
        if (empty($validated['file']) && empty($validated['external_url'])) {
            return response()->json(['message' => 'É necessário enviar um ficheiro ou fornecer um URL externo.'], 422);
        }

        $storageKey = null;
        $mimeType   = null;
        $sizeBytes  = null;
        $type       = 'Other';

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $mimeType = $file->getMimeType();

            $allowed = [
                'application/pdf',
                'video/mp4', 'video/avi', 'video/quicktime', 'video/webm', 'video/x-msvideo',
                'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'audio/mpeg', 'audio/ogg', 'audio/wav',
            ];

            if (!in_array($mimeType, $allowed, true)) {
                return response()->json(['message' => 'Tipo de ficheiro não permitido.'], 422);
            }

            $type = match (true) {
                str_contains($mimeType, 'pdf')   => 'PDF',
                str_contains($mimeType, 'video') => 'Video',
                str_contains($mimeType, 'image') => 'Image',
                str_contains($mimeType, 'audio') => 'Audio',
                default                          => 'Document',
            };

            $sizeBytes  = $file->getSize();
            $storageKey = 'materials/' . Str::uuid() . '/' . $file->getClientOriginalName();
            Storage::disk($this->storageDisk())->putFileAs('', $file, $storageKey);
        }

        if (!empty($validated['external_url'])) {
            $type = 'Link';
        }

        $material = Material::create([
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'type'           => $type,
            'mime_type'      => $mimeType,
            'size_bytes'     => $sizeBytes,
            'storage_key'    => $storageKey,
            'external_url'   => $validated['external_url'] ?? null,
            'class_group_id' => $validated['class_group_id'] ?? null,
            'course_id'      => $validated['course_id'] ?? null,
            'uploaded_by_id' => $request->user()->id,
            'is_published'   => (bool) ($validated['is_published'] ?? true),
        ]);

        $material->load(['classGroup', 'course', 'uploadedBy']);

        // Notify enrolled students when material is published
        if ($material->is_published && $material->class_group_id) {
            try {
                $enrollments = Enrollment::where('class_group_id', $material->class_group_id)
                    ->whereIn('status', ['active', 'enrolled'])
                    ->with('student.user')
                    ->get();
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->student?->user) {
                        $enrollment->student->user->notify(new MaterialPublishedNotification($material));
                    }
                }
            } catch (\Throwable) { /* Non-fatal */ }
        }

        return response()->json($this->formatMaterial($material), 201);
    }

    /* ─── UPDATE (title, description, published status) ─────────── */

    public function update(Request $request, Material $material): JsonResponse
    {
        if ($material->uploaded_by_id !== $request->user()->id) {
            abort(403, 'Sem permissão para editar este material.');
        }

        $validated = $request->validate([
            'title'        => ['sometimes', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $material->update($validated);
        $material->load(['classGroup', 'course', 'uploadedBy']);

        return response()->json($this->formatMaterial($material));
    }

    /* ─── DELETE ─────────────────────────────────────────────────── */

    public function destroy(Request $request, Material $material): JsonResponse
    {
        if ($material->uploaded_by_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403, 'Sem permissão para remover este material.');
        }

        $disk = $this->storageDisk();
        if ($material->storage_key && Storage::disk($disk)->exists($material->storage_key)) {
            Storage::disk($disk)->delete($material->storage_key);
        }

        $material->delete();

        return response()->json([], 204);
    }

    /* ─── DOWNLOAD (teacher downloads their own file) ───────────── */

    public function download(Request $request, Material $material)
    {
        if ($material->uploaded_by_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        if (!$material->storage_key) {
            return response()->json(['message' => 'Este material não tem ficheiro para descarregar.'], 404);
        }

        $disk = $this->storageDisk();

        if (!Storage::disk($disk)->exists($material->storage_key)) {
            return response()->json(['message' => 'Ficheiro não encontrado no servidor.'], 404);
        }

        $material->increment('downloads');

        return Storage::disk($disk)->download($material->storage_key, $material->title);
    }
}
