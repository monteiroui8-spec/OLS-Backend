<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminEnrollmentRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'search' => ['nullable', 'string', 'max:120'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = EnrollmentRequest::query()
            ->with([
                'user',
                // withTrashed garante que cursos arquivados (soft-deleted) ainda aparecem
                'course' => fn ($q) => $q->withTrashed(),
                'classGroup.schedules',
            ])
            ->when(!empty($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->when(!empty($validated['search']), function ($q) use ($validated) {
                $s = '%'.mb_strtolower($validated['search']).'%';
                $q->whereHas('user', function ($uq) use ($s) {
                    $uq->whereRaw('LOWER(first_name) LIKE ?', [$s])
                       ->orWhereRaw('LOWER(last_name) LIKE ?', [$s])
                       ->orWhereRaw('LOWER(email) LIKE ?', [$s])
                       ->orWhereRaw('LOWER(username) LIKE ?', [$s]);
                })->orWhere('protocol', 'like', '%'.trim($validated['search']).'%');
            })
            ->orderByDesc('created_at');

        $paginator = $q->paginate($request->integer('limit', 20));

        $data = collect($paginator->items())->map(function (EnrollmentRequest $r) {
            $course = $r->course;
            $user = $r->user;
            $class = $r->classGroup;

            return [
                'id' => $r->id,
                'protocol' => $r->protocol,
                'status' => $r->status,
                'created_at' => $r->created_at?->toISOString(),
                'responded_at' => $r->responded_at?->toISOString(),
                'admin_notes' => $r->admin_notes,
                'user' => [
                    'id'             => $user?->id,
                    'full_name'      => $user?->full_name,
                    'email'          => $user?->email,
                    'username'       => $user?->username,
                    'phone'          => $user?->phone,
                    'cpf'            => $user?->cpf,
                    'status'         => $user?->status,
                    // Dados pessoais submetidos na inscrição
                    'bi_number'      => $user?->bi_number,
                    'birth_date'     => $user?->birth_date
                        ? (\Carbon\Carbon::parse($user->birth_date)->toDateString())
                        : null,
                    'gender'         => $user?->gender,
                    'nationality'    => $user?->nationality,
                    'address'        => $user?->address,
                    'province'       => $user?->province,
                    'marital_status' => $user?->marital_status,
                    'guardian_name'  => $user?->guardian_name,
                    'guardian_phone' => $user?->guardian_phone,
                    'country'        => $user?->country,
                ],
                'course' => $course ? [
                    'id'             => $course->id,
                    'title_pt'       => $course->title_pt ?? $course->serviceCardMeta('pt')['subtitle'] ?? null,
                    'title_en'       => $course->title_en ?? $course->serviceCardMeta('en')['subtitle'] ?? null,
                    'level'          => $course->level,
                    'level_label'    => $course->serviceCardMeta('pt')['level_label'] ?? null,
                    'service_type'   => $course->service_type,
                    'service_label'  => $course->service_type
                        ? ($course->serviceCardMeta('pt')['subtitle'] ?? $course->service_type)
                        : null,
                    'duration'       => $course->duration,
                    'price_aoa'      => $course->price_aoa,
                    'description_pt' => $course->description_pt,
                    'start_date'     => $course->start_date?->toDateString(),
                    'is_archived'    => $course->deleted_at !== null,
                ] : null,
                'class_group' => $class ? [
                    'id' => $class->id,
                    'name' => $class->name,
                    'year' => $class->year,
                    'capacity' => $class->capacity,
                    'schedules' => $class->schedules->map(fn ($s) => [
                        'id' => $s->id,
                        'day_of_week' => $s->day_of_week,
                        'start_time' => $s->start_time,
                        'end_time' => $s->end_time,
                        'room' => $s->room,
                    ])->values(),
                ] : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function approve(Request $request, EnrollmentRequest $enrollmentRequest): JsonResponse
    {
        if ($enrollmentRequest->status !== 'pending') {
            return response()->json(['message' => 'Este pedido já foi processado.'], 422);
        }

        $validated = $request->validate([
            'class_group_id' => ['nullable', 'string', 'exists:class_groups,id'],
            'admin_notes' => ['nullable', 'string'],
            'payment_start_date' => ['nullable', 'date'],
            'payment_frequency' => ['nullable', 'in:monthly,quarterly,semiannual,annual'],
        ]);

        $classGroupId = $validated['class_group_id'] ?? $enrollmentRequest->class_group_id;
        
        if (!$classGroupId) {
            return response()->json(['message' => 'É necessário atribuir uma turma.'], 422);
        }

        $classGroup = ClassGroup::query()
            ->where('id', $classGroupId)
            ->where('is_active', true)
            ->firstOrFail();

        $courseId = $enrollmentRequest->course_id ?? $classGroup->course_id;
        $course = Course::findOrFail($courseId);

        if ($classGroup->course_id !== $course->id) {
            return response()->json(['message' => 'A turma não pertence ao curso especificado.'], 422);
        }

        if (! $classGroup->hasCapacity()) {
            return response()->json(['message' => 'Turma sem vagas disponíveis.'], 422);
        }

        $result = DB::transaction(function () use ($enrollmentRequest, $classGroup, $validated, $course) {
            $user = User::query()->lockForUpdate()->findOrFail($enrollmentRequest->user_id);
            $student = StudentProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if (str_starts_with($student->student_code, 'PENDING-')) {
                $student->update(['student_code' => $this->generateStudentCode()]);
            }

            $plainPassword = Str::random(12);

            // O usuário não fica totalmente ativo até pagar. Fica ativo, mas com status especial
            // Ou podemos deixá-lo 'active' e restringir via verificação de pagamentos
            $user->update([
                'status' => 'active',
                'password' => \Illuminate\Support\Facades\Hash::make($plainPassword)
            ]);

            $enrollment = Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'class_group_id' => $classGroup->id],
                [
                    'course_id' => $course->id,
                    'start_date' => $course->start_date ?? now()->toDateString(),
                    'status' => 'active',
                    'progress_pct' => 0,
                    'payment_start_date' => $validated['payment_start_date'] ?? null,
                    'payment_frequency' => $validated['payment_frequency'] ?? 'monthly',
                ]
            );

            // Gerar o primeiro pagamento automaticamente (primeira mensalidade)
            Payment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
                'description' => 'Primeira Mensalidade — ' . $course->title_pt,
                'amount' => $course->price_aoa,
                'currency' => 'AOA',
                'due_date' => now()->addDays(5), // Vencimento em 5 dias
                'status' => 'pending',
            ]);

            $enrollmentRequest->update([
                'course_id' => $course->id,
                'class_group_id' => $classGroup->id,
                'status' => 'approved',
                'responded_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            // Adicionar aluno à conversa de grupo da turma se ela existir
            $chatConv = \App\Models\ChatConversation::where('class_id', $classGroup->id)->first();
            if ($chatConv) {
                $chatConv->participants()->syncWithoutDetaching([
                    $user->id => ['joined_at' => now()]
                ]);
            }

            return [$user, $student, $enrollment, $plainPassword];
        });

        [$user, $student, $enrollment, $plainPassword] = $result;

        $classGroup->loadMissing(['schedules', 'teacher.user']);
        $this->sendApprovedEmail($user, $student, $course, $classGroup, $enrollmentRequest->protocol, $plainPassword);

        try {
            $user->notify(new \App\Notifications\EnrollmentApprovedNotification(
                $enrollmentRequest->course?->getTitle('pt') ?? ''
            ));
        } catch (\Throwable) { /* Non-fatal */ }

        return response()->json([
            'message' => 'Pedido aprovado e email enviado.',
            'enrollment_id' => $enrollment->id,
        ]);
    }

    public function reject(Request $request, EnrollmentRequest $enrollmentRequest): JsonResponse
    {
        if ($enrollmentRequest->status !== 'pending') {
            return response()->json(['message' => 'Este pedido já foi processado.'], 422);
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        $enrollmentRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return response()->json(['message' => 'Pedido rejeitado.']);
    }

    private function generateStudentCode(): string
    {
        $year = now()->year;
        $count = StudentProfile::whereYear('created_at', $year)->count() + 1;
        return "OLS-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function sendApprovedEmail(User $user, StudentProfile $student, Course $course, ClassGroup $classGroup, string $protocol, string $plainPassword): void
    {
        $appUrl = rtrim(env('FRONTEND_URL', env('APP_URL', '')), '/');
        $loginUrl = $appUrl ? $appUrl.'/login' : null;

        $scheduleText = $classGroup->schedules
            ->map(fn ($s) => "{$s->day_of_week} {$s->start_time}-{$s->end_time}".($s->room ? " ({$s->room})" : ''))
            ->implode("\n");

        $ics = $this->buildSimpleIcs($course, $classGroup);

        Mail::send('emails.enrollment-approved', [
            'user' => $user,
            'student' => $student,
            'course' => $course,
            'classGroup' => $classGroup,
            'protocol' => $protocol,
            'plainPassword' => $plainPassword,
            'loginUrl' => $loginUrl,
            'scheduleText' => $scheduleText,
        ], function ($message) use ($user, $protocol, $ics) {
            $message->to($user->email, $user->full_name)
                ->subject("Bem-vindo(a) — inscrição aprovada ({$protocol})")
                ->attachData($ics, 'calendario-curso.ics', ['mime' => 'text/calendar; charset=utf-8']);
        });
    }

    private function buildSimpleIcs(Course $course, ClassGroup $classGroup): string
    {
        $dtStart = ($course->start_date ?? now())->startOfDay()->format('Ymd\THis');
        $uid = Str::uuid()->toString();
        $summary = 'Curso: '.$course->title_pt;

        $descLines = [];
        $descLines[] = 'Turma: '.$classGroup->name;
        $descLines[] = 'Ano: '.$classGroup->year;
        $descLines[] = 'Horários:';
        foreach ($classGroup->schedules as $s) {
            $descLines[] = "{$s->day_of_week} {$s->start_time}-{$s->end_time}";
        }
        $desc = implode('\\n', array_map(fn ($l) => str_replace([',', ';'], ['\\,', '\\;'], $l), $descLines));

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//OLS//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$dtStart,
            'SUMMARY:'.$summary,
            'DESCRIPTION:'.$desc,
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }
}