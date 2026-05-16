<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Jobs\SendWelcomeEmail;
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
        $validated = $request->validate([
            'course_id'         => ['required', 'string', 'exists:courses,id'],
            'class_group_id'    => ['required', 'string', 'exists:class_groups,id'],
            'class_schedule_id' => ['nullable', 'string', 'exists:class_schedules,id'],

            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'email'      => ['required', 'email', 'max:191', 'unique:users,email'],
            'username'   => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username'],
            'password'   => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone'      => ['required', 'string', 'max:30'],
            'cpf'        => ['required', 'string', 'max:14', 'regex:/^(\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})$/', 'unique:users,cpf'],

            'country'  => ['nullable', 'string', 'size:2'],
            'language' => ['nullable', Rule::in(['pt', 'en'])],
            'currency' => ['nullable', Rule::in(['AOA', 'EUR', 'USD'])],
        ]);

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

        $cpfDigits = preg_replace('/\D+/', '', $validated['cpf']);
        $protocol  = $this->generateProtocol();
        $lang      = $validated['language'] ?? 'pt';

        $payload = DB::transaction(function () use ($validated, $cpfDigits, $protocol, $course, $classGroup, $lang) {
            $user = User::create([
                'first_name'         => $validated['first_name'],
                'last_name'          => $validated['last_name'],
                'email'              => $validated['email'],
                'username'           => $validated['username'],
                'phone'              => $validated['phone'],
                'cpf'                => $cpfDigits,
                'password'           => Hash::make($validated['password']),
                'role'               => 'student',
                'status'             => 'pending',
                'country'            => $validated['country'] ?? 'AO',
                'preferred_language' => $lang,
                'preferred_currency' => $validated['currency'] ?? 'AOA',
            ]);

            Role::findOrCreate('student', 'web');
            $user->assignRole('student');

            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_code'   => 'PENDING-' . Str::upper(Str::random(6)),
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

        // Envia email de boas-vindas ao cliente (job assíncrono, multilíngue)
        SendWelcomeEmail::dispatch($payload['user']);

        // Envia emails de notificação de inscrição
        $this->sendRequestEmails(
            user: $payload['user'],
            protocol: $protocol,
            course: $course,
            classGroup: $classGroup,
            lang: $lang,
        );

        return response()->json([
            'message'  => $lang === 'en' ? 'Enrollment request received.' : 'Pedido de inscrição recebido.',
            'protocol' => $protocol,
        ], 201);
    }

    private function generateProtocol(): string
    {
        $date = now()->format('Ymd');
        return 'OLS-ENR-' . $date . '-' . Str::upper(Str::random(6));
    }

    private function sendRequestEmails(User $user, string $protocol, Course $course, ClassGroup $classGroup, string $lang = 'pt'): void
    {
        $companyEmail = env('MAIL_ENROLLMENT_TO') ?: config('mail.from.address');

        $data = [
            'user'        => $user,
            'protocol'    => $protocol,
            'course'      => $course,
            'classGroup'  => $classGroup,
            'responseSla' => '48h',
            'lang'        => $lang,
        ];

        $clientSubject = $lang === 'en'
            ? "Enrollment Request Received — {$protocol}"
            : "Pedido de inscrição recebido — {$protocol}";

        Mail::send('emails.enrollment-request-client', $data, function ($message) use ($user, $clientSubject) {
            $message->to($user->email, $user->full_name)->subject($clientSubject);
        });

        if ($companyEmail) {
            Mail::send('emails.enrollment-request-company', $data, function ($message) use ($companyEmail, $protocol) {
                $message->to($companyEmail)->subject("Novo pedido de inscrição — {$protocol}");
            });
        }
    }
}
