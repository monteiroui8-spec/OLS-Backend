<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * POST /api/auth/email/verify/{id}/{hash}
     * Verifica o email com o link assinado enviado por Sanctum.
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email já verificado.']);
        }

        $request->fulfill();

        event(new Verified($request->user()));

        return response()->json(['message' => 'Email verificado com sucesso.']);
    }

    /**
     * POST /api/auth/email/resend
     * Reenvio do link de verificação.
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email já verificado.'], 422);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Link de verificação reenviado.']);
    }
}
