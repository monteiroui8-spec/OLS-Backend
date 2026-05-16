<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Document::query()->with('course', 'uploadedBy');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        if (! $request->user()->isAdmin()) {
            $query->where(function ($q) use ($request) {
                $q->where('is_public', true)->orWhere('uploaded_by_id', $request->user()->id);
            });
        }

        $documents = $query->orderByDesc('created_at')->paginate($request->integer('limit', 20));

        $data = $documents->map(function (Document $document) {
            return [
                'id' => $document->id,
                'name' => $document->name,
                'type' => $document->type,
                'sizeBytes' => $document->size_bytes,
                'isPublic' => $document->is_public,
                'downloads' => $document->downloads,
                'course' => $document->course?->getTitle(app()->getLocale()),
                'uploadedBy' => $document->uploadedBy?->full_name,
                'createdAt' => $document->created_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $documents->total(),
                'page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file'      => ['required', 'file', 'max:512000'],
            'name'      => ['required', 'string', 'max:255'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $file     = $validated['file'];
        $mimeType = $file->getMimeType();

        $allowed = [
            'application/pdf',
            'video/mp4', 'video/avi', 'video/quicktime',
            'image/jpeg', 'image/png', 'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (! in_array($mimeType, $allowed, true)) {
            return response()->json(['message' => 'Tipo de ficheiro não permitido.'], 422);
        }

        $type = match (true) {
            str_contains($mimeType, 'pdf')   => 'PDF',
            str_contains($mimeType, 'video') => 'Video',
            str_contains($mimeType, 'image') => 'Image',
            default                          => 'Document',
        };

        $key  = 'documents/'.Str::uuid().'/'.$file->getClientOriginalName();
        $disk = $this->storageDisk();

        Storage::disk($disk)->putFileAs('', $file, $key);

        $isPublic  = (bool) ($validated['is_public'] ?? false);
        $publicUrl = $isPublic ? Storage::disk($disk)->url($key) : null;

        $document = Document::create([
            'name'           => $validated['name'],
            'type'           => $type,
            'mime_type'      => $mimeType,
            'size_bytes'     => $file->getSize(),
            'storage_key'    => $key,
            'public_url'     => $publicUrl,
            'course_id'      => $validated['course_id'] ?? null,
            'uploaded_by_id' => $request->user()->id,
            'is_public'      => $isPublic,
        ]);

        return response()->json([
            'id'        => $document->id,
            'name'      => $document->name,
            'type'      => $document->type,
            'sizeBytes' => $document->size_bytes,
        ], 201);
    }

    public function download(Request $request, Document $document)
    {
        $user = $request->user();

        $canAccess = $document->is_public
            || $user->isAdmin()
            || $document->uploaded_by_id === $user->id
            || ($user->role === 'student' && $document->course_id && $user->studentProfile?->enrollments()
                ->where('course_id', $document->course_id)
                ->where('status', 'active')
                ->exists())
            || $user->role === 'teacher';

        if (! $canAccess) {
            abort(403, 'Sem permissão para aceder a este ficheiro.');
        }

        $document->increment('downloads');

        $disk = $this->storageDisk();

        if (! Storage::disk($disk)->exists($document->storage_key)) {
            return response()->json(['message' => 'Ficheiro não encontrado no servidor.'], 404);
        }

        return Storage::disk($disk)->download(
            $document->storage_key,
            $document->name
        );
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        if ($request->user()->cannot('delete', $document)) {
            abort(403);
        }

        $disk = $this->storageDisk();
        if (Storage::disk($disk)->exists($document->storage_key)) {
            Storage::disk($disk)->delete($document->storage_key);
        }

        $document->delete();

        return response()->json([], 204);
    }

    private function storageDisk(): string
    {
        // Use S3 when credentials are configured, otherwise fall back to local disk.
        $key = config('filesystems.disks.s3.key');

        return ($key && $key !== '') ? 's3' : 'public';
    }
}
