<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentTestimonialController extends Controller
{
    /**
     * List the authenticated student's own testimonials.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if (!$student) {
            return response()->json(['message' => 'Perfil de aluno não encontrado.'], 403);
        }

        $testimonials = Testimonial::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $testimonials]);
    }

    /**
     * Submit a new testimonial (starts as pending).
     */
    public function store(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if (!$student) {
            return response()->json(['message' => 'Perfil de aluno não encontrado.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|min:20|max:1000',
            'rating'  => 'integer|min:1|max:5',
        ]);

        $user = $request->user();

        $testimonial = Testimonial::create([
            'student_id' => $student->id,
            'name'       => $user->full_name,
            'role'       => 'Aluno',
            'content'    => $validated['content'],
            'rating'     => $validated['rating'] ?? 5,
            'image_url'  => $user->avatar_url,
            'is_active'  => false,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'Testemunho enviado. Aguarda aprovação do administrador.',
            'data'    => $testimonial,
        ], 201);
    }
}
