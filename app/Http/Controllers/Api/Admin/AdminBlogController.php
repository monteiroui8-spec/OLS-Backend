<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    // --- Posts ---
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 20);
        $search = $request->input('search');
        $status = $request->input('status');
        
        $posts = BlogPost::with(['category', 'author'])
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate($limit);

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_posts,slug'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'string', 'exists:blog_categories,id'],
            'image_url' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $post = BlogPost::create($validated);
        
        // Notify subscribers if published immediately
        if ($post->status === 'published' && (!$post->published_at || $post->published_at <= now())) {
            $this->notifySubscribers($post);
        }

        return response()->json($post, 201);
    }

    public function show(BlogPost $post): JsonResponse
    {
        $post->load(['category', 'author']);
        return response()->json($post);
    }

    public function update(Request $request, BlogPost $post): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_posts,slug,'.$post->id],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'string', 'exists:blog_categories,id'],
            'image_url' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);

        $wasDraft = $post->status === 'draft';
        
        $post->update($validated);
        
        // Notify subscribers if just published
        if ($wasDraft && $post->status === 'published' && (!$post->published_at || $post->published_at <= now())) {
            $this->notifySubscribers($post);
        }

        return response()->json($post);
    }

    public function destroy(BlogPost $post): JsonResponse
    {
        $post->delete();
        return response()->json(['message' => 'Post apagado.']);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        $path = $request->file('image')->store('blog/images', 'public');
        
        return response()->json([
            'url' => Storage::disk('public')->url($path)
        ]);
    }

    // --- Categories ---
    public function indexCategories(): JsonResponse
    {
        $categories = BlogCategory::withCount('posts')->orderBy('name_pt')->get();
        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_pt' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_categories,slug'],
        ]);

        $category = BlogCategory::create($validated);
        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, BlogCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name_pt' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blog_categories,slug,'.$category->id],
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroyCategory(BlogCategory $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Categoria apagada.']);
    }

    private function notifySubscribers(BlogPost $post): void
    {
        $subscribers = NewsletterSubscriber::whereNull('unsubscribed_at')->get();
        $appUrl = rtrim(env('FRONTEND_URL', env('APP_URL', '')), '/');
        
        foreach ($subscribers as $subscriber) {
            Mail::send('emails.new-blog-post', [
                'post' => $post,
                'subscriber' => $subscriber,
                'postUrl' => $appUrl . '/blog/' . $post->slug,
                'unsubscribeUrl' => env('APP_URL') . '/api/public/newsletter/unsubscribe?email=' . urlencode($subscriber->email),
            ], function ($message) use ($subscriber, $post) {
                $message->to($subscriber->email)
                        ->subject("Novo artigo no Blog: {$post->title}");
            });
        }
    }
}
