<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class StudentPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->studentProfile;
        $currency = $request->header('X-Currency', $request->user()->preferred_currency);

        $payments = Payment::forStudent($student->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByDesc('due_date')
            ->paginate($request->integer('limit', 20));

        $data = $payments->map(function (Payment $payment) use ($currency) {
            return [
                'id' => $payment->id,
                'description' => $payment->description,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'due_date' => $payment->due_date,
                'paid_at' => $payment->paid_at,
                'status' => $payment->status,
                'invoice_number' => $payment->invoice_number,
                'proof_url' => $payment->proof_url,
            ];
        });

        $statsData = Payment::forStudent($student->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = \'paid\' THEN amount ELSE 0 END) as totalPaid,
                SUM(CASE WHEN status IN (\'pending\', \'overdue\') THEN amount ELSE 0 END) as totalPending,
                SUM(CASE WHEN status = \'overdue\' THEN amount ELSE 0 END) as totalOverdue
            ')->first();

        $stats = [
            'totalPaid' => (float) ($statsData->totalPaid ?? 0),
            'totalPending' => (float) ($statsData->totalPending ?? 0),
            'totalOverdue' => (float) ($statsData->totalOverdue ?? 0),
            'total' => (int) ($statsData->total ?? 0),
            'currency' => $currency,
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

    public function receipt(Request $request, Payment $payment): Response
    {
        $student = $request->user()->studentProfile;

        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        if ($payment->receipt_url) {
            return response()->redirectTo($payment->receipt_url);
        }

        $payment->load('student.user', 'course');

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment,
        ])->setPaper('a4');

        try {
            if (config('filesystems.default') === 's3') {
                $key = 'receipts/'.$payment->invoice_number.'.pdf';
                Storage::disk('s3')->put($key, $pdf->output(), 'private');

                $publicUrl = Storage::disk('s3')->url($key);
                $payment->update(['receipt_url' => $publicUrl]);
            }
        } catch (\Exception $e) {
            // Fallback: don't fail if S3 is not configured properly, just stream the PDF
        }

        return $pdf->download($payment->invoice_number.'.pdf');
    }

    public function uploadProof(Request $request, Payment $payment): JsonResponse
    {
        $student = $request->user()->studentProfile;

        if ($payment->student_id !== $student->id) {
            abort(403);
        }

        $request->validate([
            'proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
        ]);

        $path = $request->file('proof')->store('payments/proofs', 'public');
        $proofUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);

        $payment->update([
            'proof_url' => $proofUrl,
            // optional: you could change the status to "pending_verification" or similar here
            // if you have such a status, but for now we'll just store the proof URL
        ]);

        return response()->json([
            'message' => 'Comprovativo enviado com sucesso.',
            'proof_url' => $proofUrl,
        ]);
    }
}

