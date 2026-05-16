<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('student.user', 'course')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByDesc('due_date')
            ->paginate($request->integer('limit', 20));

        $data = $payments->map(function (Payment $payment) {
            return [
                'id' => $payment->id,
                'invoice_number' => $payment->invoice_number,
                'student' => $payment->student?->user?->full_name,
                'student_email' => $payment->student?->user?->email,
                'description' => $payment->description,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'due_date' => $payment->due_date,
                'paid_at' => $payment->paid_at,
                'method' => $payment->method,
                'proof_url' => $payment->proof_url,
                'rejection_reason' => $payment->rejection_reason,
            ];
        });

        $statsData = Payment::selectRaw('
            COUNT(CASE WHEN status = \'paid\' THEN 1 END) as paidCount,
            COUNT(CASE WHEN status = \'pending\' THEN 1 END) as pendingCount,
            COUNT(CASE WHEN status = \'overdue\' THEN 1 END) as overdueCount,
            SUM(CASE WHEN status = \'paid\' THEN amount ELSE 0 END) as totalPaid,
            SUM(CASE WHEN status = \'pending\' THEN amount ELSE 0 END) as totalPending,
            SUM(CASE WHEN status = \'overdue\' THEN amount ELSE 0 END) as totalOverdue
        ')->first();

        $stats = [
            'paidCount' => (int) ($statsData->paidCount ?? 0),
            'pendingCount' => (int) ($statsData->pendingCount ?? 0),
            'overdueCount' => (int) ($statsData->overdueCount ?? 0),
            'totalPaid' => (float) ($statsData->totalPaid ?? 0),
            'totalPending' => (float) ($statsData->totalPending ?? 0),
            'totalOverdue' => (float) ($statsData->totalOverdue ?? 0),
            'currency' => 'AOA',
        ];

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $payments->total(),
                'page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
            'stats' => $stats,
        ]);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load('student.user', 'course');

        return response()->json([
            'id' => $payment->id,
            'invoice_number' => $payment->invoice_number,
            'description' => $payment->description,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'due_date' => $payment->due_date,
            'paid_at' => $payment->paid_at,
            'method' => $payment->method,
            'transaction_ref' => $payment->transaction_ref,
            'proof_url' => $payment->proof_url,
            'receipt_url' => $payment->receipt_url,
            'notes' => $payment->notes,
            'rejection_reason' => $payment->rejection_reason,
            'student' => [
                'id' => $payment->student?->id,
                'full_name' => $payment->student?->user?->full_name,
                'email' => $payment->student?->user?->email,
                'phone' => $payment->student?->user?->phone,
            ],
            'course' => [
                'id' => $payment->course?->id,
                'title' => $payment->course?->title_pt,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'exists:student_profiles,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:student_profiles,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:paid,pending,overdue,cancelled,refunded'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $studentIds = $validated['student_ids'] ?? ($request->has('student_id') && $request->get('student_id') ? [$request->get('student_id')] : []);
        
        if (empty($studentIds)) {
            return response()->json(['message' => 'Pelo menos um aluno deve ser selecionado.'], 422);
        }

        $payments = [];
        foreach ($studentIds as $id) {
            // Se não houver course_id/enrollment_id explícito, tentamos pegar o ativo do aluno
            $courseId = $validated['course_id'] ?? null;
            $enrollmentId = $validated['enrollment_id'] ?? null;

            if (!$courseId) {
                $activeEnrollment = \App\Models\Enrollment::where('student_id', $id)
                    ->where('status', 'active')
                    ->first();
                
                if ($activeEnrollment) {
                    $courseId = $activeEnrollment->course_id;
                    $enrollmentId = $activeEnrollment->id;
                }
            }

            // Removendo campos que não pertencem ao modelo Payment
            $createData = $validated;
            unset($createData['student_ids']);

            $payments[] = Payment::create(array_merge($createData, [
                'student_id' => $id,
                'course_id' => $courseId,
                'enrollment_id' => $enrollmentId,
            ]));
        }

        return response()->json($payments, 201);
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'in:paid,pending,overdue,cancelled,refunded,rejected'],
            'paid_at' => ['required_if:status,paid', 'nullable', 'date'],
            'method' => ['required_if:status,paid', 'nullable', 'in:credit_card,bank_transfer,mpesa,multicaixa,cash,other'],
            'transaction_ref' => ['nullable', 'string', 'max:191'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->update($validated);

        if (($validated['status'] ?? null) === 'paid' && $payment->student && $payment->student->user) {
            $user = $payment->student->user;
            // $user->notify(new PaymentConfirmedNotification($payment));
            
            // Check if it's the first payment
            $isFirstPayment = Payment::where('student_id', $payment->student_id)
                ->where('status', 'paid')
                ->count() === 1;

            Mail::send('emails.payment-confirmed', [
                'user' => $user,
                'payment' => $payment,
                'isFirstPayment' => $isFirstPayment,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->full_name)
                    ->subject('Pagamento Confirmado - Olsangola Corporation');
            });
        }

        if (($validated['status'] ?? null) === 'rejected' && $payment->student && $payment->student->user) {
            $user = $payment->student->user;
            Mail::send('emails.payment-rejected', [
                'user' => $user,
                'payment' => $payment,
                'reason' => $validated['rejection_reason'] ?? 'Não especificado',
            ], function ($message) use ($user) {
                $message->to($user->email, $user->full_name)
                    ->subject('Pagamento Rejeitado - Olsangola Corporation');
            });
        }

        return response()->json([
            'id' => $payment->id,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:paid,pending,overdue,cancelled,refunded'],
        ]);

        $filename = 'pagamentos-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($validated) {
            $payments = Payment::with('student.user')
                ->whereBetween('due_date', [$validated['start_date'], $validated['end_date']])
                ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
                ->orderBy('due_date')
                ->cursor();

            $out = fopen('php://output', 'w');
            
            $header = ['Nº Fatura', 'Aluno', 'Descrição', 'Valor', 'Moeda', 'Estado', 'Data Vencimento', 'Data Pagamento', 'Método'];
            fputcsv($out, $header);

            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->invoice_number,
                    $p->student?->user?->full_name,
                    $p->description,
                    $p->amount,
                    $p->currency,
                    $p->status,
                    $p->due_date,
                    $p->paid_at ?? '',
                    $p->method ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
