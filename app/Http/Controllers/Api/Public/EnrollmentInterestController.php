<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Mail\EnrollmentInterestMail;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnrollmentInterestController extends Controller
{
    /**
     * Recebe o formulário de pré-inscrição do site público.
     * Cria um User pendente + StudentProfile + EnrollmentRequest para revisão do admin.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:80'],
            'last_name'      => ['required', 'string', 'max:80'],
            'email'          => ['required', 'email', 'max:191'],
            'phone'          => ['required', 'string', 'max:30'],
            'service_type'   => ['nullable', 'string', 'max:80'],
            'course_id'      => ['nullable', 'string', 'exists:courses,id'],
            'country'        => ['nullable', 'string', 'size:2'],
            'language'       => ['nullable', 'string', 'in:pt,en'],
            'currency'       => ['nullable', 'string', 'in:AOA,EUR,USD'],
            // Dados pessoais
            'bi_number'      => ['nullable', 'string', 'max:20'],
            'birth_date'     => ['nullable', 'date', 'before:-14 years'],
            'gender'         => ['nullable', 'string', 'in:M,F,outro'],
            'nationality'    => ['nullable', 'string', 'max:80'],
            'address'        => ['nullable', 'string', 'max:255'],
            'province'       => ['nullable', 'string', 'max:80'],
            'marital_status' => ['nullable', 'string', 'in:solteiro,casado,divorciado,viuvo,outro'],
            'guardian_name'  => ['nullable', 'string', 'max:160'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ], [
            'first_name.required' => 'O nome é obrigatório.',
            'last_name.required'  => 'O apelido é obrigatório.',
            'email.required'      => 'O e-mail é obrigatório.',
            'email.email'         => 'Formato de e-mail inválido.',
            'phone.required'      => 'O telefone é obrigatório.',
            'course_id.exists'    => 'Curso inválido.',
            'birth_date.before'   => 'O candidato deve ter pelo menos 14 anos.',
        ]);

        // Impede duplicação: se já existe um pedido pendente com o mesmo email+curso, retorna sucesso sem duplicar
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            $existingRequest = EnrollmentRequest::where('user_id', $existingUser->id)
                ->where('status', 'pending')
                ->when(!empty($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
                ->exists();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'O seu pedido de inscrição foi recebido. Entraremos em contacto brevemente.',
                ], 201);
            }
        }

        try {
            $enrollmentRequest = DB::transaction(function () use ($validated) {
                // Cria ou recupera o utilizador pendente
                $user = User::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'first_name'         => $validated['first_name'],
                        'last_name'          => $validated['last_name'],
                        'phone'              => $validated['phone'],
                        'username'           => $this->generateUsername($validated['first_name'], $validated['last_name']),
                        'password'           => Hash::make(Str::random(24)),
                        'role'               => 'student',
                        'status'             => 'pending',
                        'country'            => $validated['country'] ?? null,
                        'preferred_language' => $validated['language'] ?? 'pt',
                        'preferred_currency' => $validated['currency'] ?? 'AOA',
                        // Dados pessoais
                        'bi_number'          => $validated['bi_number'] ?? null,
                        'birth_date'         => $validated['birth_date'] ?? null,
                        'gender'             => $validated['gender'] ?? null,
                        'nationality'        => $validated['nationality'] ?? null,
                        'address'            => $validated['address'] ?? null,
                        'province'           => $validated['province'] ?? null,
                        'marital_status'     => $validated['marital_status'] ?? null,
                        'guardian_name'      => $validated['guardian_name'] ?? null,
                        'guardian_phone'     => $validated['guardian_phone'] ?? null,
                    ]
                );

                // Garante perfil de estudante
                StudentProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_code'    => 'PENDING-' . strtoupper(Str::random(8)),
                        'enrollment_date' => now()->toDateString(),
                    ]
                );

                // Gera protocolo único
                $protocol = 'OLS-' . now()->format('Ym') . '-' . strtoupper(Str::random(6));

                // Cria o pedido de inscrição
                return EnrollmentRequest::create([
                    'protocol'  => $protocol,
                    'user_id'   => $user->id,
                    'course_id' => $validated['course_id'] ?? null,
                    'status'    => 'pending',
                ]);
            });

            // Obter o curso para os emails
            $course = !empty($validated['course_id'])
                ? Course::find($validated['course_id'])
                : null;

            $emailData = array_merge($validated, [
                'course_name' => $course?->getTitle($validated['language'] ?? 'pt'),
                'protocol'    => $enrollmentRequest->protocol,
            ]);

            $studentFullName = $validated['first_name'] . ' ' . $validated['last_name'];
            $studentEmail    = $validated['email'];
            $protocol        = $enrollmentRequest->protocol;

            // 1. Email de confirmação ao aluno (não-fatal)
            try {
                Mail::send(
                    'emails.enrollment-interest-student',
                    ['data' => $emailData],
                    function ($m) use ($studentEmail, $studentFullName, $protocol) {
                        $m->to($studentEmail, $studentFullName)
                          ->subject('Pedido de pré-inscrição recebido — ' . $protocol);
                    }
                );
                Log::info('enrollment_interest_student_email_sent', [
                    'email'    => $studentEmail,
                    'protocol' => $protocol,
                ]);
            } catch (\Throwable $e) {
                Log::error('enrollment_interest_student_email_failed', [
                    'email'    => $studentEmail,
                    'protocol' => $protocol,
                    'error'    => $e->getMessage(),
                ]);
            }

            // 2. Notificação ao admin (não-fatal)
            try {
                $adminEmail = config('mail.from.address', 'admin@olsangola.com');
                Mail::to($adminEmail)->send(new EnrollmentInterestMail($emailData));
                Log::info('enrollment_interest_admin_email_sent', [
                    'admin'    => $adminEmail,
                    'protocol' => $protocol,
                ]);
            } catch (\Throwable $e) {
                Log::error('enrollment_interest_admin_email_failed', [
                    'admin'    => config('mail.from.address'),
                    'protocol' => $protocol,
                    'error'    => $e->getMessage(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('enrollment_interest_store_failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Ocorreu um erro ao registar o pedido. Por favor tente novamente.',
            ], 500);
        }

        return response()->json([
            'message'  => 'O seu pedido de inscrição foi recebido. Entraremos em contacto brevemente.',
            'protocol' => $enrollmentRequest->protocol,
        ], 201);
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $base = strtolower(
            Str::ascii($firstName) . '.' . Str::ascii($lastName)
        );
        $base = preg_replace('/[^a-z0-9._]/', '', $base);
        $candidate = $base;
        $i = 1;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . $i;
            $i++;
        }
        return $candidate;
    }
}
