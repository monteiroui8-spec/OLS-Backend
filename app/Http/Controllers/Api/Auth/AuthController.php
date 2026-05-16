<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendWelcomeEmail;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Conta suspensa ou inactiva. Contacte o suporte.',
            ], 403);
        }

        // Revoga tokens antigos (opcional — mantém apenas 1 sessão activa)
        // $user->tokens()->delete();

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'first_name'         => $request->first_name,
                'last_name'          => $request->last_name,
                'email'              => $request->email,
                'phone'              => $request->phone,
                'password'           => Hash::make($request->password),
                'role'               => 'student',
                'status'             => 'active',
                'country'            => $request->country ?? 'AO',
                'preferred_language' => $request->preferred_language ?? 'pt',
                'preferred_currency' => $request->preferred_currency ?? 'AOA',
            ]);
            Role::findOrCreate('student', 'web');
            $user->assignRole('student');

            StudentProfile::create([
                'user_id'      => $user->id,
                'student_code' => $this->generateStudentId(),
                'enrollment_date' => now()->toDateString(),
            ]);

            return $user;
        });

        SendWelcomeEmail::dispatch($user);

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Conta criada com sucesso.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            $request->user()?->tokens()->delete();
        }

        return response()->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load($this->profileRelation($request->user()));

        return response()->json(new UserResource($user));
    }

    /**
     * POST /api/auth/refresh
     * Revoga o token actual e emite um novo.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $token = $request->user()->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function generateStudentId(): string
    {
        $year = now()->year;
        $last = StudentProfile::whereYear('created_at', $year)->count() + 1;

        return "OLS-{$year}-" . str_pad($last, 4, '0', STR_PAD_LEFT);
    }

    private function profileRelation(User $user): array
    {
        return match ($user->role) {
            'student' => ['studentProfile'],
            'teacher' => ['teacherProfile'],
            'admin'   => ['adminProfile'],
            default   => [],
        };
    }
}
