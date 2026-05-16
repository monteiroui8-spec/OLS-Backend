<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminNewsletterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 50);
        $search = $request->input('search');
        $status = $request->input('status', 'active');
        
        $query = NewsletterSubscriber::query()
            ->when($search, function ($q, $search) {
                $q->where('email', 'like', "%{$search}%");
            })
            ->when($status === 'active', fn($q) => $q->whereNull('unsubscribed_at'))
            ->when($status === 'unsubscribed', fn($q) => $q->whereNotNull('unsubscribed_at'))
            ->orderByDesc('subscribed_at');

        $paginator = $query->paginate($limit);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $subscribers = NewsletterSubscriber::whereNull('unsubscribed_at')->get();
        
        foreach ($subscribers as $subscriber) {
            Mail::send('emails.newsletter', [
                'content' => $validated['content'],
                'unsubscribeUrl' => env('APP_URL') . '/api/public/newsletter/unsubscribe?email=' . urlencode($subscriber->email),
            ], function ($message) use ($subscriber, $validated) {
                $message->to($subscriber->email)
                        ->subject($validated['subject']);
            });
        }

        return response()->json([
            'message' => 'Newsletter enviada com sucesso para ' . $subscribers->count() . ' subscritores.',
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        $subscriber->delete();
        return response()->json(['message' => 'Subscritor removido.']);
    }
}
