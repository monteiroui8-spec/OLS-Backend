<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CourseEnrollmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // ---------------------------------------------------------------
        // 1. Normalise CPF digits before validation so the unique check
        //    compares apples to apples (digits only stored in the DB).
        // ---------------------------------------------------------------
        $cpfRaw    = $request->input('cpf', '');
        $cpfDigits = preg_replace('/\D+/', '', $cpfRaw);

        $validated = $request->validate([
            'course_id'         => ['required', 'string', 'exists:courses,id'],
            'class_group_id'    => ['required', 'string', 'exists:class_groups,id'],
            'class_schedule_id' => ['nullable', 'string', 'exists:class_schedules,id'],

            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],

            'email' => ['required', 'email', 'max:191', 'unique:users,email'],

            // Username: letters, numbers, _ . - only (mirrors front-end regex)
            'username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[a-zA-Z0-9_.\-]+$/',
                'unique:users,username',
            ],

            // Password must have mixed case, at least one number and one symbol
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],

            'phone' => ['required', 'string', 'max:30'],

            // Validate the raw CPF format, but check uniqueness against the
            // digits-only value that we will actually store.
            'cpf' => [
                'required',
                'string',
                'max:14',
                'regex:/^(\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})$/',
                Rule::unique('users', 'cpf')->where(fn ($query) => $query->where('cpf', $cpfDigits)),
            ],

            'country'  => ['nullable', 'string', 'size:2'],
            'language' => ['nullable', Rule::in(['pt', 'en'])],
            'currency' => ['nullable', Rule::in(['AOA', 'EUR', 'USD'])],

            // ── Dados pessoais do aluno ──────────────────────────────────
            'bi_number'      => ['nullable', 'string', 'max:20'],
            'birth_date'     => ['required', 'date', 'before:-14 years'],
            'gender'         => ['required', Rule::in(['M', 'F', 'outro'])],
            'nationality'    => ['nullable', 'string', 'max:80'],
            'address'        => ['required', 'string', 'max:255'],
            'province'       => ['nullable', 'string', 'max:80'],
            'marital_status' => ['nullable', Rule::in(['solteiro', 'casado', 'divorciado', 'viuvo', 'outro'])],
            'guardian_name'  => ['nullable', 'string', 'max:160'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
        ], [
            // ---------------------------------------------------------------
            // 2. Human-readable error messages (Portuguese default)
            // ---------------------------------------------------------------
            'course_id.required'      => 'O curso é obrigatório.',
            'course_id.exists'        => 'Curso inválido.',
            'class_group_id.required' => 'A turma é obrigatória.',
            'class_group_id.exists'   => 'Turma inválida.',

            'first_name.required' => 'O nome é obrigatório.',
            'last_name.required'  => 'O apelido é obrigatório.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email'    => 'Formato de e-mail inválido.',
            'email.unique'   => 'Este e-mail já está registado.',

            'username.required' => 'O username é obrigatório.',
            'username.min'      => 'O username deve ter pelo menos 3 caracteres.',
            'username.regex'    => 'O username só pode conter letras, números, _, . e -.',
            'username.unique'   => 'Este username já está em uso.',

            'password.required' => 'A password é obrigatória.',

            'phone.required' => 'O telefone é obrigatório.',

            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.regex'    => 'CPF inválido. Use o formato 000.000.000-00 ou apenas os 11 dígitos.',
            'cpf.unique'   => 'Este CPF já está registado.',

            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.date'     => 'Data de nascimento inválida.',
            'birth_date.before'   => 'O aluno deve ter pelo menos 14 anos de idade.',
            'gender.required'     => 'O sexo é obrigatório.',
            'gender.in'           => 'Sexo inválido. Use M, F ou outro.',
            'address.required'    => 'A morada é obrigatória.',
        ]);

        // ---------------------------------------------------------------
        // 3. Business-rule checks
        // ---------------------------------------------------------------
        $course = Course::query()->findOrFail($validated['course_id']);

        $classGroup = ClassGroup::query()
            ->where('id', $validated['class_group_id'])
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $classGroup->hasCapacity()) {
            return response()->json(['message' => 'Turma sem vagas disponíveis.'], 422);
        }

        if (! empty($validated['class_schedule_id'])) {
            $schedule = ClassSchedule::query()
                ->where('id', $validated['class_schedule_id'])
                ->where('class_group_id', $classGroup->id)
                ->first();

            if (! $schedule) {
                return response()->json(['message' => 'Horário inválido para a turma seleccionada.'], 422);
            }
        }

        $protocol = $this->generateProtocol();

        // ---------------------------------------------------------------
        // 4. Persist – user + student profile + enrollment request
        //    (single transaction for consistency)
        // ---------------------------------------------------------------
        $payload = DB::transaction(function () use ($validated, $cpfDigits, $protocol, $course, $classGroup) {
            $user = User::create([
                'first_name'         => $validated['first_name'],
                'last_name'          => $validated['last_name'],
                'email'              => $validated['email'],
                'username'           => $validated['username'],
                'phone'              => $validated['phone'],
                'cpf'                => $cpfDigits,          // store digits only
                'password'           => Hash::make($validated['password']),
                'role'               => 'student',
                'status'             => 'pending',
                'country'            => $validated['country'] ?? 'AO',
                'preferred_language' => $validated['language'] ?? 'pt',
                'preferred_currency' => $validated['currency'] ?? 'AOA',
                // Dados pessoais
                'bi_number'      => $validated['bi_number'] ?? null,
                'birth_date'     => $validated['birth_date'],
                'gender'         => $validated['gender'],
                'nationality'    => $validated['nationality'] ?? 'Angolana',
                'address'        => $validated['address'],
                'province'       => $validated['province'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'guardian_name'  => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
            ]);

            Role::findOrCreate('student', 'web');
            $user->assignRole('student');

            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_code'    => 'PENDING-' . Str::upper(Str::random(6)),
                    'enrollment_date' => now()->toDateString(),
                ]
            );

            $enrollmentRequest = EnrollmentRequest::create([
                'protocol'          => $protocol,
                'user_id'           => $user->id,
                'course_id'         => $course->id,
                'class_group_id'    => $classGroup->id,
                'class_schedule_id' => $validated['class_schedule_id'] ?? null,
                'status'            => 'pending',
            ]);

            return [
                'user'    => $user,
                'request' => $enrollmentRequest,
            ];
        });

        $this->sendRequestEmails(
            user:       $payload['user'],
            protocol:   $protocol,
            course:     $course,
            classGroup: $classGroup,
        );

        return response()->json([
            'message'  => 'Pedido de inscrição recebido.',
            'protocol' => $protocol,
        ], 201);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function generateProtocol(): string
    {
        $date = now()->format('Ymd');
        return 'OLS-ENR-' . $date . '-' . Str::upper(Str::random(6));
    }

    private function sendRequestEmails(
        User       $user,
        string     $protocol,
        Course     $course,
        ClassGroup $classGroup,
    ): void {
        $companyEmail = env('MAIL_ENROLLMENT_TO') ?: config('mail.from.address');

        $data = [
            'user'        => $user,
            'protocol'    => $protocol,
            'course'      => $course,
            'classGroup'  => $classGroup,
            'responseSla' => '48h',
            'lang'        => $user->preferred_language ?? 'pt',
        ];

        // Email de confirmação ao aluno
        try {
            $toEmail = $user->email;
            $toName  = $user->full_name;
            Mail::send(
                'emails.enrollment-request-client',
                $data,
                function ($m) use ($toEmail, $toName, $protocol) {
                    $m->to($toEmail, $toName)
                      ->subject("Pedido de inscrição recebido — {$protocol}");
                }
            );
            \Illuminate\Support\Facades\Log::info('enrollment_client_email_sent', [
                'protocol' => $protocol,
                'email'    => $toEmail,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('enrollment_client_email_failed', [
                'protocol' => $protocol,
                'email'    => $user->email,
                'error'    => $e->getMessage(),
            ]);
        }

        // Email de notificação à empresa/admin
        if ($companyEmail) {
            try {
                Mail::send(
                    'emails.enrollment-request-company',
                    $data,
                    function ($m) use ($companyEmail, $protocol) {
                        $m->to($companyEmail)
                          ->subject("Novo pedido de inscrição — {$protocol}");
                    }
                );
                \Illuminate\Support\Facades\Log::info('enrollment_company_email_sent', [
                    'protocol' => $protocol,
                    'admin'    => $companyEmail,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('enrollment_company_email_failed', [
                    'protocol' => $protocol,
                    'admin'    => $companyEmail,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}