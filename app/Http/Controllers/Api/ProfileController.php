<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

class ProfileController extends Controller
{
    /**
     * PUT /api/profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name'         => ['sometimes', 'string', 'max:80'],
            'last_name'          => ['sometimes', 'string', 'max:80'],
            'name'               => ['sometimes', 'string', 'max:160'], // for frontend compatibility
            'phone'              => ['sometimes', 'nullable', 'string', 'max:30'],
            'country'            => ['sometimes', 'string', 'size:2'],
            'preferred_language' => ['sometimes', 'in:pt,en'],
            'preferred_currency' => ['sometimes', 'in:AOA,EUR,USD'],
            'bio'                => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        if (isset($validated['name'])) {
            $parts = explode(' ', $validated['name'], 2);
            $validated['first_name'] = $parts[0];
            $validated['last_name'] = $parts[1] ?? '';
            unset($validated['name']);
        }

        $user->update($validated);

        if (isset($validated['bio'])) {
            if ($user->role === 'teacher' && $user->teacherProfile) {
                $user->teacherProfile->update(['bio' => $validated['bio']]);
            } elseif ($user->role === 'student' && $user->studentProfile) {
                $user->studentProfile->update(['bio' => $validated['bio']]);
            }
        }

        return response()->json(['message' => 'Profile updated successfully.', 'data' => new UserResource($user->fresh())]);
    }

    /**
     * PUT /api/profile/password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'A password actual está incorrecta.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoga todos os outros tokens para forçar novo login noutros dispositivos
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $user->tokens()->where('id', '!=', $token->id)->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Password actualizada com sucesso.']);
    }

    /**
     * POST /api/profile/avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $user = $request->user();

        // Remove avatar anterior
        if ($user->avatar_url) {
            $oldPath = str_replace(Storage::url(''), '', $user->avatar_url);
            Storage::delete($oldPath);
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');

        $user->update(['avatar_url' => Storage::url($path)]);

        return response()->json([
            'avatar_url' => $user->avatar_url,
            'message'    => 'Avatar actualizado.',
        ]);
    }

    /**
     * DELETE /api/profile/avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_url) {
            $path = str_replace(Storage::url(''), '', $user->avatar_url);
            Storage::delete($path);
            $user->update(['avatar_url' => null]);
        }

        return response()->json(['message' => 'Avatar removido.']);
    }
}
