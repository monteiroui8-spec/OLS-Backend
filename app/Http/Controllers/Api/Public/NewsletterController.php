<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $language = $request->header('Accept-Language', 'pt');
        $country = $request->get('country');

        NewsletterSubscriber::updateOrCreate(
            ['email' => $validated['email']],
            [
                'language' => in_array($language, ['pt', 'en'], true) ? $language : 'pt',
                'country' => $country,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json([
            'message' => 'Subscrição registada.',
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->query('email');
        if ($email) {
            NewsletterSubscriber::where('email', $email)->update([
                'unsubscribed_at' => now(),
            ]);
        }

        return response("<html><body><h2>Sua subscrição foi cancelada com sucesso.</h2></body></html>");
    }
}
