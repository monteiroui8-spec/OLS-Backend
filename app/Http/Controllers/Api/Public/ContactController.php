<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactFormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $submission = ContactFormSubmission::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'language' => $request->header('Accept-Language', 'pt'),
            'ip_address' => $request->ip(),
        ]);

        try {
            Mail::send([], [], function ($m) use ($validated) {
                $fullName = $validated['first_name'] . ' ' . $validated['last_name'];
                $m->to(env('MAIL_CONTACT_RECEIVER', 'suporte@ols.com'))
                  ->replyTo($validated['email'], $fullName)
                  ->subject("Portal OLS — Contato: " . $validated['subject'])
                  ->html(
                      "<html><body style='font-family:sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px'>" .
                      "<div style='background:#1a56db;padding:20px;border-radius:8px 8px 0 0;'><h2 style='color:white;margin:0'>Nova Mensagem de Contato — Portal OLS</h2></div>" .
                      "<div style='background:#f9fafb;padding:24px;border:1px solid #e5e7eb;border-radius:0 0 8px 8px'>" .
                      "<p><strong>Nome:</strong> {$fullName}</p>" .
                      "<p><strong>E-mail:</strong> <a href='mailto:{$validated['email']}'>{$validated['email']}</a></p>" .
                      "<p><strong>Telefone:</strong> " . ($validated['phone'] ?? 'N/A') . "</p>" .
                      "<p><strong>Assunto:</strong> {$validated['subject']}</p>" .
                      "<hr style='border:none;border-top:1px solid #e5e7eb;margin:16px 0'>" .
                      "<p><strong>Mensagem:</strong></p>" .
                      "<div style='background:white;padding:16px;border-radius:6px;border:1px solid #e5e7eb'>" .
                      nl2br(htmlspecialchars($validated['message'])) .
                      "</div>" .
                      "<p style='color:#6b7280;font-size:12px;margin-top:20px'>Mensagem enviada através do Portal OLS Angola.</p>" .
                      "</div></body></html>"
                  );
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send contact email: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Mensagem recebida.',
        ]);
    }
}

