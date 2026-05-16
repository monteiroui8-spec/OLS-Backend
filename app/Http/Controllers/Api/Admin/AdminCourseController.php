<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = Course::withTrashed()
            ->withCount('classes', 'enrollments')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('title_pt', 'like', '%'.$request->get('search').'%')
                  ->orWhere('title_en', 'like', '%'.$request->get('search').'%');
            }))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->get('status') === 'active')   $q->where('is_active', true)->whereNull('deleted_at');
                if ($request->get('status') === 'inactive') $q->where('is_active', false);
                if ($request->get('status') === 'archived') $q->whereNotNull('deleted_at');
            })
            ->orderBy('title_pt')
            ->paginate($request->integer('limit', 20));

        $lang = $request->header('Accept-Language', 'pt');

        $data = $courses->map(function (Course $course) use ($lang) {
            $card = $course->serviceCardMeta($lang);

            return [
                'id'              => $course->id,
                'slug'            => $course->slug,
                'title'           => $course->getTitle($lang),
                'title_pt'        => $course->title_pt,
                'title_en'        => $course->title_en,
                'description_pt'  => $course->description_pt,
                'description_en'  => $course->description_en,
                'level'           => $course->levelBucket(),
                'level_raw'       => $course->level,
                'service_type'    => $course->service_type,
                'duration'        => $course->duration,
                'start_date'      => $course->start_date?->toDateString(),
                'available_seats' => $course->available_seats,
                'responsible_teacher_id' => $course->responsible_teacher_id,
                'prerequisites'   => $course->prerequisites,
                'syllabus'        => $course->syllabus,
                'required_material' => $course->required_material,
                'price_aoa'       => $course->price_aoa,
                'price_eur'       => $course->price_eur,
                'price_usd'       => $course->price_usd,
                'is_active'       => $course->is_active,
                'visibility_status' => $course->visibility_status,
                'published_at'    => $course->published_at?->toISOString(),
                'meta_description'=> $course->meta_description,
                'image_url'       => $course->image_url,
                'flyer_url'       => $course->flyer_url,
                'card' => [
                    'tag' => $card['tag'] ?? null,
                    'tagColor' => $card['tag_color'] ?? null,
                    'subtitle' => $card['subtitle'] ?? null,
                    'levelLabel' => $card['level_label'] ?? null,
                    'durationLabel' => $card['duration_label'] ?? null,
                    'instructorLabel' => $card['instructor_label'] ?? null,
                ],
                'tags'            => $course->tags ?? [],
                'classes_count'   => $course->classes_count,
                'enrollments_count' => $course->enrollments_count,
                'deleted_at'      => $course->deleted_at?->toISOString(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'     => $courses->total(),
                'page'      => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
            ],
        ]);
    }

    public function show(Course $course): JsonResponse
    {
        $course->loadMissing(['responsibleTeacher.user']);
        $lang = request()->header('Accept-Language', 'pt');
        $card = $course->serviceCardMeta($lang);

        return response()->json([
            'data' => [
                'id' => $course->id,
                'slug' => $course->slug,
                'title_pt' => $course->title_pt,
                'title_en' => $course->title_en,
                'description_pt' => $course->description_pt,
                'description_en' => $course->description_en,
                'prerequisites' => $course->prerequisites,
                'level' => $course->levelBucket(),
                'level_raw' => $course->level,
                'service_type' => $course->service_type,
                'duration' => $course->duration,
                'start_date' => $course->start_date?->toDateString(),
                'available_seats' => $course->available_seats,
                'responsible_teacher_id' => $course->responsible_teacher_id,
                'responsible_teacher' => $course->responsibleTeacher?->user?->full_name,
                'syllabus' => $course->syllabus,
                'required_material' => $course->required_material,
                'price_aoa' => $course->price_aoa,
                'price_eur' => $course->price_eur,
                'price_usd' => $course->price_usd,
                'image_url' => $course->image_url,
                'flyer_url' => $course->flyer_url,
                'card' => [
                    'tag' => $card['tag'] ?? null,
                    'tagColor' => $card['tag_color'] ?? null,
                    'subtitle' => $card['subtitle'] ?? null,
                    'levelLabel' => $card['level_label'] ?? null,
                    'durationLabel' => $card['duration_label'] ?? null,
                    'instructorLabel' => $card['instructor_label'] ?? null,
                ],
                'is_active' => $course->is_active,
                'visibility_status' => $course->visibility_status,
                'published_at' => $course->published_at?->toISOString(),
                'meta_description' => $course->meta_description,
                'tags' => $course->tags ?? [],
                'created_at' => $course->created_at?->toISOString(),
                'updated_at' => $course->updated_at?->toISOString(),
                'deleted_at' => $course->deleted_at?->toISOString(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title_pt'        => ['required', 'string', 'max:191'],
            'title_en'        => ['required', 'string', 'max:191'],
            'description_pt'  => ['required', 'string'],
            'description_en'  => ['required', 'string'],
            'prerequisites'   => ['nullable', 'string'],
            'level'        => ['required', 'in:beginner,intermediate,advanced,all'],
            'service_type' => ['required', 'string', 'max:50'],
            'duration'     => ['nullable', 'string', 'max:60'],
            'start_date'   => ['nullable', 'date'],
            'available_seats' => ['required', 'integer', 'min:1'],
            'responsible_teacher_id' => ['nullable', 'string', 'exists:teacher_profiles,id'],
            'syllabus'     => ['nullable', 'string'],
            'required_material' => ['nullable', 'string'],
            'price_aoa'    => ['required', 'numeric', 'min:0'],
            'price_eur'    => ['nullable', 'numeric', 'min:0'],
            'price_usd'    => ['nullable', 'numeric', 'min:0'],
            'is_active'    => ['boolean'],
            'visibility_status' => ['required', 'in:draft,published,scheduled,hidden'],
            'published_at' => ['nullable', 'date'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'tags'         => ['nullable', 'array'],
            'image_url'    => ['nullable', 'string'],
        ]);

        if (($validated['visibility_status'] ?? null) === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if (($validated['visibility_status'] ?? null) === 'scheduled' && empty($validated['published_at'])) {
            $validated['published_at'] = now()->addDay();
        }

        $validated['slug'] = Str::slug($validated['title_en']).'-'.Str::random(5);

        $course = Course::create($validated);

        Cache::forget('public_courses_pt');
        Cache::forget('public_courses_en');

        return response()->json([
            'id'       => $course->id,
            'title'    => $course->getTitle(app()->getLocale()),
            'is_active'=> $course->is_active,
        ], 201);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'title_pt'     => ['sometimes', 'string', 'max:191'],
            'title_en'     => ['sometimes', 'string', 'max:191'],
            'description_pt' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'prerequisites'  => ['sometimes', 'nullable', 'string'],
            'level'        => ['sometimes', 'in:beginner,intermediate,advanced,all'],
            'service_type' => ['sometimes', 'string', 'max:50'],
            'duration'     => ['sometimes', 'nullable', 'string', 'max:60'],
            'start_date'   => ['sometimes', 'nullable', 'date'],
            'available_seats' => ['sometimes', 'integer', 'min:1'],
            'responsible_teacher_id' => ['sometimes', 'nullable', 'string', 'exists:teacher_profiles,id'],
            'syllabus'     => ['sometimes', 'nullable', 'string'],
            'required_material' => ['sometimes', 'nullable', 'string'],
            'price_aoa'    => ['sometimes', 'numeric', 'min:0'],
            'price_eur'    => ['sometimes', 'numeric', 'min:0'],
            'price_usd'    => ['sometimes', 'numeric', 'min:0'],
            'is_active'    => ['sometimes', 'boolean'],
            'visibility_status' => ['sometimes', 'in:draft,published,scheduled,hidden'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:180'],
            'tags'         => ['sometimes', 'nullable', 'array'],
            'image_url'    => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('visibility_status', $validated)) {
            $newStatus = $validated['visibility_status'];
            if ($newStatus === 'published' && (!array_key_exists('published_at', $validated) || empty($validated['published_at']))) {
                $validated['published_at'] = now();
            }
        }

        $course->update($validated);

        Cache::forget('public_courses_pt');
        Cache::forget('public_courses_en');

        return response()->json([
            'id'       => $course->id,
            'title'    => $course->fresh()->getTitle(app()->getLocale()),
            'is_active'=> $course->is_active,
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        Cache::forget('public_courses_pt');
        Cache::forget('public_courses_en');

        return response()->json(null, 204);
    }

    public function restore(Course $course): JsonResponse
    {
        $course->restore();
        Cache::forget('public_courses_pt');
        Cache::forget('public_courses_en');

        return response()->json(['message' => 'Curso restaurado com sucesso.']);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        $path = $request->file('image')->store('courses/images', 'public');
        
        return response()->json([
            'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($path)
        ]);
    }
}
