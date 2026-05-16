<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @deprecated  Use CourseEnrollmentController (POST /api/public/course-enrollments) instead.
 *
 * This controller previously handled simple enrollment requests without user creation.
 * It is kept as a stub so that any stale client requests receive a clear error
 * instead of a 404, making debugging easier.
 */
class EnrollmentRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Este endpoint foi descontinuado. '
                       . 'Use POST /api/public/course-enrollments para submeter uma inscrição.',
        ], 410); // 410 Gone
    }
}