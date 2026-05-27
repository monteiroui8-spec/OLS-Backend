<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\SendAccountActivatedEmail;
use App\Jobs\SendAccountDeactivatedEmail;
use App\Jobs\SendPasswordSetEmail;
use App\Models\AdminProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                // FIX: usar callbacks exactos para role/status em vez do filtro
                // automático do Spatie que gerava LOWER(...) ILIKE incompatível com MariaDB.
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->where('role', strtolower($value));
                }),
                AllowedFilter::callback('status', function ($query, $value) {
                    $query->where('status', strtolower($value));
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->search($value);
                }),
            ])
            ->allowedSorts(['first_name', 'email', 'created_at', 'last_login_at'])
            ->with(['studentProfile', 'teacherProfile', 'adminProfile'])
            ->withTrashed()
            ->paginate($request->integer('limit', 20));

        return UserResource::collection($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'         => ['required', 'string', 'max:80'],
            'last_name'          => ['required', 'string', 'max:80'],
            'email'              => ['required', 'email', 'max:191', 'unique:users,email'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'password'           => ['nullable', 'string', 'min:8'],
            'role'               => ['required', 'in:admin,teacher,student'],
            'status'             => ['required', 'in:active,inactive,pending,suspended'],
            'country'            => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', 'in:pt,en'],
            'preferred_currency' => ['nullable', 'in:AOA,EUR,USD'],
            // Dados pessoais
            'bi_number'      => ['nullable', 'string', 'max:20'],
            'birth_date'     => ['nullable', 'date'],
            'gender'         => ['nullable', 'in:M,F,outro'],
            'nationality'    => ['nullable', 'string', 'max:80'],
            'address'        => ['nullable', 'string', 'max:255'],
            'province'       => ['nullable', 'string', 'max:80'],
            'marital_status' => ['nullable', 'in:solteiro,casado,divorciado,viuvo,outro'],
            'guardian_name'  => ['nullable', 'string', 'max:160'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ]);

        // Generate or use provided password — always send it by email.
        $plainPassword = $validated['password'] ?? Str::random(12);

        $user = DB::transaction(function () use ($validated, $plainPassword) {
            $user = User::create([
                'first_name'         => $validated['first_name'],
                'last_name'          => $validated['last_name'],
                'email'              => $validated['email'],
                'phone'              => $validated['phone'] ?? null,
                'password'           => Hash::make($plainPassword),
                'role'               => $validated['role'],
                'status'             => $validated['status'],
                'country'            => $validated['country'] ?? 'AO',
                'preferred_language' => $validated['preferred_language'] ?? 'pt',
                'preferred_currency' => $validated['preferred_currency'] ?? 'AOA',
                // Dados pessoais
                'bi_number'      => $validated['bi_number'] ?? null,
                'birth_date'     => $validated['birth_date'] ?? null,
                'gender'         => $validated['gender'] ?? null,
                'nationality'    => $validated['nationality'] ?? null,
                'address'        => $validated['address'] ?? null,
                'province'       => $validated['province'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'guardian_name'  => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
            ]);

            $user->assignRole($user->role);
            $this->createProfileForRole($user);

            return $user;
        });

        // ── Dispatch jobs after transaction ──────────────────────────────────
        // Always send credentials email.
        SendPasswordSetEmail::dispatch($user, $plainPassword);

        // If account is immediately active, also send activation email.
        if ($user->status === 'active') {
            SendAccountActivatedEmail::dispatch($user);
        }

        return response()->json(new UserResource($user->load(['studentProfile', 'teacherProfile', 'adminProfile'])), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name'         => ['sometimes', 'string', 'max:80'],
            'last_name'          => ['sometimes', 'string', 'max:80'],
            'email'              => ['sometimes', 'email', 'max:191', 'unique:users,email,'.$user->id],
            'phone'              => ['sometimes', 'nullable', 'string', 'max:30'],
            'password'           => ['sometimes', 'nullable', 'string', 'min:8'],
            'role'               => ['sometimes', 'in:admin,teacher,student'],
            'status'             => ['sometimes', 'in:active,inactive,pending,suspended'],
            'country'            => ['sometimes', 'string', 'size:2'],
            'preferred_language' => ['sometimes', 'in:pt,en'],
            'preferred_currency' => ['sometimes', 'in:AOA,EUR,USD'],
            // Dados pessoais
            'bi_number'      => ['sometimes', 'nullable', 'string', 'max:20'],
            'birth_date'     => ['sometimes', 'nullable', 'date'],
            'gender'         => ['sometimes', 'nullable', 'in:M,F,outro'],
            'nationality'    => ['sometimes', 'nullable', 'string', 'max:80'],
            'address'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'province'       => ['sometimes', 'nullable', 'string', 'max:80'],
            'marital_status' => ['sometimes', 'nullable', 'in:solteiro,casado,divorciado,viuvo,outro'],
            'guardian_name'  => ['sometimes', 'nullable', 'string', 'max:160'],
            'guardian_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        $previousStatus = $user->status;
        $beforeRole     = $user->role;

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Role changed → sync Spatie role + ensure profile exists.
        if (array_key_exists('role', $validated) && $validated['role'] !== $beforeRole) {
            $user->syncRoles([$validated['role']]);
            $this->createProfileForRole($user->fresh());
        }

        // Status changed → send appropriate email.
        if (array_key_exists('status', $validated) && $validated['status'] !== $previousStatus) {
            $fresh = $user->fresh();
            if ($validated['status'] === 'active') {
                SendAccountActivatedEmail::dispatch($fresh);
            } elseif (in_array($validated['status'], ['inactive', 'suspended'], true)) {
                SendAccountDeactivatedEmail::dispatch($fresh);
            }
        }

        // FIX: carregar relações no fresh() para evitar erro 500 no UserResource
        return response()->json(new UserResource($user->fresh(['studentProfile', 'teacherProfile', 'adminProfile'])));
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(null, 204);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        $plainPassword = $validated['new_password'] ?? Str::random(12);

        $user->update(['password' => Hash::make($plainPassword)]);
        $user->tokens()->delete();

        // Notify user of new credentials.
        SendPasswordSetEmail::dispatch($user, $plainPassword);

        return response()->json(['message' => 'Password redefinida e enviada por email.']);
    }

    // ─── Batch operations ────────────────────────────────────────────────────

    /**
     * POST /admin/users/batch-activate
     * Body: { ids: ["ulid1", "ulid2"] }
     */
    public function batchActivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'], // FIX: era 'integer', incompatível com ULID
        ]);

        $users = User::whereIn('id', $validated['ids'])
            ->where('status', '!=', 'active')
            ->get();

        foreach ($users as $user) {
            $user->update(['status' => 'active']);
            SendAccountActivatedEmail::dispatch($user);
        }

        return response()->json([
            'message'   => "{$users->count()} conta(s) activada(s).",
            'activated' => $users->count(),
        ]);
    }

    /**
     * POST /admin/users/batch-deactivate
     * Body: { ids: ["ulid1", "ulid2"] }
     */
    public function batchDeactivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'], // FIX: era 'integer', incompatível com ULID
        ]);

        $users = User::whereIn('id', $validated['ids'])
            ->where('status', 'active')
            ->get();

        foreach ($users as $user) {
            $user->update(['status' => 'inactive']);
            SendAccountDeactivatedEmail::dispatch($user);
        }

        return response()->json([
            'message'     => "{$users->count()} conta(s) desactivada(s).",
            'deactivated' => $users->count(),
        ]);
    }

    /**
     * POST /admin/users/batch-delete
     * Body: { ids: ["ulid1", "ulid2"] }
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'], // FIX: era 'integer', incompatível com ULID
        ]);

        $count = User::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => "{$count} utilizador(es) eliminado(s).",
            'deleted' => $count,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createProfileForRole(User $user): void
    {
        match ($user->role) {
            'student' => StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_code'    => $this->generateCode('student'),
                    'enrollment_date' => now()->toDateString(),
                ]
            ),
            'teacher' => TeacherProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'teacher_code' => $this->generateCode('teacher'),
                    'hire_date'    => now()->toDateString(),
                ]
            ),
            'admin' => AdminProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'admin_code' => $this->generateCode('admin'),
                ]
            ),
            default => null,
        };
    }

    private function generateCode(string $role): string
    {
        $year   = now()->year;
        $prefix = match ($role) {
            'student' => "OLS-{$year}",
            'teacher' => 'TCH',
            'admin'   => 'ADM',
            default   => strtoupper($role),
        };

        // Use o código máximo existente para evitar duplicados por count
        $next = match ($role) {
            'student' => $this->nextStudentNumber($year),
            'teacher' => TeacherProfile::count() + 1,
            'admin'   => AdminProfile::count() + 1,
            default   => 1,
        };

        return $prefix.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function nextStudentNumber(int $year): int
    {
        $prefix = "OLS-{$year}-";

        // Extrai o maior número já usado para este ano
        $max = StudentProfile::where('student_code', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(student_code, ?) AS UNSIGNED)) as max_num', [strlen($prefix) + 1])
            ->value('max_num');

        return ($max ?? 0) + 1;
    }
}