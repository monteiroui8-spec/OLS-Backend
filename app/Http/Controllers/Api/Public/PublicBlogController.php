<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 9);
        $search = $request->input('search');
        $categorySlug = $request->input('category');
        $tag = $request->input('tag');

        $query = BlogPost::with(['category', 'author'])
            ->published()
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($categorySlug, function ($q, $categorySlug) {
                $q->whereHas('category', function ($sub) use ($categorySlug) {
                    $sub->where('slug', $categorySlug);
                });
            })
            ->when($tag, function ($q, $tag) {
                $q->whereJsonContains('tags', $tag);
            })
            ->orderByDesc('published_at');

        $paginator = $query->paginate($limit);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ]
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::with(['category', 'author'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $post]);
    }

    public function categories(): JsonResponse
    {
        $categories = BlogCategory::withCount(['posts' => function ($query) {
            $query->published();
        }])->orderBy('name_pt')->get();

        return response()->json(['data' => $categories]);
    }
}
