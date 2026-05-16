<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentMaterialController extends Controller
{
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

    /**
     * Gera URL de preview seguro conforme o disco configurado.
     * - S3: URL temporário assinado (30 min)
     * - local/public: URL público directo
     */
    private function buildPreviewUrl(Material $material): ?string
    {
        if (!$material->storage_key) {
            return null;
        }

        $disk    = $this->storageDisk();
        $storage = Storage::disk($disk);

        if (!$storage->exists($material->storage_key)) {
            return null;
        }

        // S3 suporta temporaryUrl; disco local não suporta — usar url() directo
        if ($disk === 's3') {
            try {
                return $storage->temporaryUrl(
                    $material->storage_key,
                    now()->addMinutes(30)
                );
            } catch (\Throwable) {
                // fallback se as credenciais S3 não suportarem URLs assinados
                return $storage->url($material->storage_key);
            }
        }

        return $storage->url($material->storage_key);
    }

    /* ─── LIST ───────────────────────────────────────────────────── */

    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->studentProfile;

        [$classIds, $courseIds] = $this->getEnrolledIds($profile);

        // Se o aluno não tiver inscrições, devolver lista vazia
        if (empty($classIds) && empty($courseIds)) {
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0, 'page' => 1, 'last_page' => 1],
            ]);
        }

        $query = Material::where('is_published', true)
            ->where(function ($q) use ($classIds, $courseIds) {
                $q->when(!empty($classIds),  fn ($q) => $q->orWhereIn('class_group_id', $classIds))
                  ->when(!empty($courseIds), fn ($q) => $q->orWhereIn('course_id', $courseIds));
            })
            ->with(['classGroup', 'course', 'uploadedBy']);

        if ($request->filled('class_group_id')) {
            $query->where('class_group_id', $request->get('class_group_id'));
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
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

    /* ─── VIEW (incrementa visualizações + devolve URL de preview) ─ */

    public function view(Request $request, Material $material): JsonResponse
    {
        $this->checkAccess($request, $material);

        $material->increment('views');

        $previewUrl = $material->external_url ?? $this->buildPreviewUrl($material);

        return response()->json([
            ...$this->formatMaterial($material),
            'previewUrl' => $previewUrl,
        ]);
    }

    /* ─── DOWNLOAD ───────────────────────────────────────────────── */

    public function download(Request $request, Material $material)
    {
        $this->checkAccess($request, $material);

        if ($material->external_url) {
            return response()->json(['redirectUrl' => $material->external_url]);
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

    /* ─── Helpers ────────────────────────────────────────────────── */

    /**
     * Devolve [classGroupIds[], courseIds[]] das inscrições activas do aluno.
     */
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

    /**
     * Verifica se o aluno tem acesso ao material.
     * Um material pode estar ligado a uma turma OU a um curso.
     * O aluno tem acesso se estiver inscrito na turma OU no curso correspondente.
     */
    private function checkAccess(Request $request, Material $material): void
    {
        if (!$material->is_published) {
            abort(403, 'Material não publicado.');
        }

        $profile = $request->user()->studentProfile;

        [$classIds, $courseIds] = $this->getEnrolledIds($profile);

        $hasAccess =
            // Material ligado a turma — aluno está nessa turma
            ($material->class_group_id && in_array($material->class_group_id, $classIds, true))
            // Material ligado a curso — aluno está inscrito nesse curso
            || ($material->course_id && in_array($material->course_id, $courseIds, true))
            // Material não está ligado a turma nem a curso — acesso geral (improvável mas defensivo)
            || (!$material->class_group_id && !$material->course_id);

        if (!$hasAccess) {
            abort(403, 'Sem permissão para aceder a este material.');
        }
    }
}