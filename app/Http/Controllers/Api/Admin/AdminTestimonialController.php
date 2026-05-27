<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminTestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 20);
        $query = Testimonial::orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $testimonials = $query->paginate($limit);
        return response()->json($testimonials);
    }

    public function approve(Request $request, Testimonial $testimonial): JsonResponse
    {
        $testimonial->update(['status' => 'approved', 'is_active' => true]);
        return response()->json(['message' => 'Testemunho aprovado.', 'data' => $testimonial]);
    }

    public function reject(Request $request, Testimonial $testimonial): JsonResponse
    {
        $testimonial->update(['status' => 'rejected', 'is_active' => false]);
        return response()->json(['message' => 'Testemunho rejeitado.', 'data' => $testimonial]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'required|string',
            'rating' => 'integer|min:1|max:5',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('testimonials', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $testimonial = Testimonial::create($validated);
        return response()->json($testimonial, 201);
    }

    public function update(Request $request, Testimonial $testimonial): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'role' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'rating' => 'integer|min:1|max:5',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($testimonial->image_url) {
                $oldPath = str_replace('/storage/', '', $testimonial->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('testimonials', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $testimonial->update($validated);
        return response()->json($testimonial);
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        if ($testimonial->image_url) {
            $oldPath = str_replace('/storage/', '', $testimonial->image_url);
            Storage::disk('public')->delete($oldPath);
        }
        $testimonial->delete();
        return response()->json(['message' => 'Testemunho apagado.']);
    }
}
