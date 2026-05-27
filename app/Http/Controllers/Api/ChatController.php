<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatMessageRead;
use App\Models\ClassGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Serializa um participante (User) para o formato esperado pelo frontend.
     */
    private function formatParticipant($user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->full_name,
            'role'       => $user->role,
            'avatar_url' => $user->avatar_url,
        ];
    }

    /**
     * Serializa uma mensagem completa com sender, attachment e read_by.
     */
    /**
     * @param array<string,string[]>|null $readByMap  id_mensagem → [user_ids] (pré-carregado em batch)
     */
    private function formatMessage(ChatMessage $msg, array $readByMap = []): array
    {
        $msg->loadMissing(['sender', 'attachment']);

        // Usar mapa pré-carregado se disponível; caso contrário query individual
        $readBy = $readByMap[$msg->id]
            ?? DB::table('chat_message_reads')
                ->where('message_id', $msg->id)
                ->pluck('user_id')
                ->toArray();

        return [
            'id'              => $msg->id,
            'conversation_id' => $msg->conversation_id,
            'sender_id'       => $msg->sender_id,
            'sender'          => $msg->sender ? $this->formatParticipant($msg->sender) : null,
            'type'            => $msg->type,
            'body'            => $msg->body,
            'attachment'      => $msg->attachment ? $this->formatAttachment($msg->attachment) : null,
            'read_by'         => $readBy,
            'created_at'      => optional($msg->created_at)->toISOString() ?? now()->toISOString(),
            'updated_at'      => optional($msg->updated_at)->toISOString() ?? now()->toISOString(),
        ];
    }

    /**
     * Serializa um anexo.
     */
    private function formatAttachment(ChatAttachment $att): array
    {
        return [
            'id'               => $att->id,
            'url'              => $att->url,
            'name'             => $att->name,
            'mime_type'        => $att->mime_type,
            'size_bytes'       => $att->size_bytes,
            'duration_seconds' => $att->duration_seconds,
        ];
    }

    /**
     * Serializa uma conversa com last_message e unread_count.
     */
    private function formatConversation(ChatConversation $conv, string $userId): array
    {
        $conv->loadMissing(['participants', 'lastMessage.sender', 'lastMessage.attachment']);

        return [
            'id'           => $conv->id,
            'type'         => $conv->type,
            'name'         => $conv->name,
            'class_id'     => $conv->class_id,
            'participants' => $conv->participants->map(fn ($u) => $this->formatParticipant($u))->values()->toArray(),
            'last_message' => $conv->lastMessage ? $this->formatMessage($conv->lastMessage) : null,
            'unread_count' => $conv->unreadCountFor($userId),
            'created_at'   => $conv->created_at->toISOString(),
            'updated_at'   => $conv->updated_at->toISOString(),
        ];
    }

    /**
     * Carrega read_by para um conjunto de mensagens numa única query.
     * Devolve array<message_id, user_id[]>
     */
    private function batchReadBy(array $messageIds): array
    {
        if (empty($messageIds)) return [];

        $rows = DB::table('chat_message_reads')
            ->whereIn('message_id', $messageIds)
            ->get(['message_id', 'user_id']);

        $map = [];
        foreach ($rows as $row) {
            $map[$row->message_id][] = $row->user_id;
        }
        return $map;
    }

    // ── GET /api/chat/conversations ───────────────────────────────────────────

    /**
     * Lista todas as conversas do utilizador autenticado,
     * ordenadas pela última mensagem mais recente.
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        // 1. Get conversations where user is explicit participant
        $explicitConvIds = DB::table('chat_participants')
            ->where('user_id', $userId)
            ->pluck('conversation_id')
            ->toArray();

        // 2. Get class groups the user belongs to
        $classGroupIds = [];
        if ($user->role === 'teacher' && $user->teacherProfile) {
            $classGroupIds = ClassGroup::where('teacher_id', $user->teacherProfile->id)
                ->pluck('id')
                ->toArray();
        } elseif ($user->role === 'student' && $user->studentProfile) {
            $classGroupIds = $user->studentProfile->enrollments()
                ->where('status', 'active')
                ->pluck('class_group_id')
                ->toArray();
        }

        // 3. Find group conversations for these classes
        $groupConvIds = ChatConversation::whereIn('class_id', $classGroupIds)
            ->pluck('id')
            ->toArray();

        $allConvIds = array_unique(array_merge($explicitConvIds, $groupConvIds));

        $convs = ChatConversation::whereIn('id', $allConvIds)
            ->with(['participants', 'lastMessage.sender', 'lastMessage.attachment'])
            ->orderByDesc(
                ChatMessage::select('created_at')
                    ->whereColumn('conversation_id', 'chat_conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        return response()->json([
            'data' => $convs->map(fn ($c) => $this->formatConversation($c, $userId))->values(),
        ]);
    }

    // ── GET /api/chat/conversations/{id}/messages ─────────────────────────────

    /**
     * Retorna as mensagens de uma conversa com suporte a paginação e polling
     * incremental via ?after={message_id}.
     */
    public function messages(Request $request, string $id): JsonResponse
    {
        $userId = $request->user()->id;

        // Verificar que o utilizador é participante
        $conv = ChatConversation::whereHas(
            'participants',
            fn ($q) => $q->where('user_id', $userId)
        )->findOrFail($id);

        $limit = (int) $request->input('limit', 50);
        $page  = (int) $request->input('page', 1);
        $after = $request->input('after');

        $query = $conv->messages()
            ->with(['sender', 'attachment'])
            ->orderBy('created_at');

        if ($after) {
            // Polling incremental: mensagens com id > after (ULIDs são ordenáveis)
            $query->where('id', '>', $after);
            $msgs = $query->get();
            $readByMap = $this->batchReadBy($msgs->pluck('id')->toArray());

            return response()->json([
                'data' => $msgs->map(fn ($m) => $this->formatMessage($m, $readByMap))->values(),
                'meta' => ['total' => $msgs->count(), 'page' => 1, 'last_page' => 1],
            ]);
        }

        // Paginação normal
        $paginator = $query->paginate($limit, ['*'], 'page', $page);
        $items     = collect($paginator->items());
        $readByMap = $this->batchReadBy($items->pluck('id')->toArray());

        return response()->json([
            'data' => $items->map(fn ($m) => $this->formatMessage($m, $readByMap))->values(),
            'meta' => [
                'total'     => $paginator->total(),
                'page'      => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    // ── POST /api/chat/messages ───────────────────────────────────────────────

    /**
     * Envia uma nova mensagem (texto ou com anexo já carregado).
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'conversation_id' => 'required|string|exists:chat_conversations,id',
            'type'            => ['required', Rule::in(['text', 'audio', 'file', 'image'])],
            'body'            => 'nullable|string|max:5000',
            'attachment_id'   => 'nullable|string|exists:chat_attachments,id',
        ]);

        // Verificar participação
        $conv = ChatConversation::whereHas(
            'participants',
            fn ($q) => $q->where('user_id', $userId)
        )->findOrFail($validated['conversation_id']);

        // Validação de conteúdo
        if (empty($validated['body']) && empty($validated['attachment_id'])) {
            return response()->json(['message' => 'A mensagem deve ter texto ou anexo.'], 422);
        }

        $msg = ChatMessage::create([
            'conversation_id' => $conv->id,
            'sender_id'       => $userId,
            'type'            => $validated['type'],
            'body'            => $validated['body'] ?? null,
            'attachment_id'   => $validated['attachment_id'] ?? null,
        ]);

        // Marcar automaticamente como lida pelo remetente
        ChatMessageRead::firstOrCreate([
            'message_id' => $msg->id,
            'user_id'    => $userId,
        ], ['read_at' => now()]);

        // Actualizar updated_at da conversa
        $conv->touch();

        $msg->load(['sender', 'attachment']);

        return response()->json($this->formatMessage($msg), 201);
    }

    // ── POST /api/chat/conversations/{id}/read ────────────────────────────────

    /**
     * Marca todas as mensagens não lidas desta conversa como lidas
     * pelo utilizador autenticado.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $userId = $request->user()->id;

        // Verificar participação
        ChatConversation::whereHas(
            'participants',
            fn ($q) => $q->where('user_id', $userId)
        )->findOrFail($id);

        // Buscar mensagens não lidas (não enviadas por si, sem registo de leitura)
        $alreadyReadIds = \DB::table('chat_message_reads')
            ->where('user_id', $userId)
            ->pluck('message_id')
            ->toArray();

        $unreadIds = ChatMessage::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->when(!empty($alreadyReadIds), fn ($q) => $q->whereNotIn('id', $alreadyReadIds))
            ->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            $now  = now();
            $rows = $unreadIds->map(fn ($msgId) => [
                'message_id' => $msgId,
                'user_id'    => $userId,
                'read_at'    => $now,
            ])->toArray();

            DB::table('chat_message_reads')->insertOrIgnore($rows);
        }

        return response()->json(['ok' => true]);
    }

    // ── GET /api/users?search= ────────────────────────────────────────────────

    /**
     * Pesquisa utilizadores por nome ou email para iniciar uma conversa directa.
     * Retorna apenas id, full_name, role e avatar_url — sem dados sensíveis.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $search = $request->input('search', '');
        $limit  = min((int) $request->input('limit', 10), 20);

        $users = \App\Models\User::query()
            ->when(strlen($search) >= 2, function ($q) use ($search) {
                $term = '%' . mb_strtolower($search) . '%';
                $q->where(fn ($inner) =>
                    $inner->whereRaw('LOWER(first_name) LIKE ?', [$term])
                          ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                          ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                );
            })
            ->where('id', '!=', $request->user()->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'role', 'avatar_url']);

        return response()->json([
            'data' => $users->map(fn ($u) => [
                'id'         => $u->id,
                'full_name'  => $u->full_name,
                'role'       => $u->role,
                'avatar_url' => $u->avatar_url,
            ])->values(),
        ]);
    }


    /**
     * Cria uma conversa directa entre o utilizador autenticado e outro utilizador.
     * Se já existir, retorna a existente (idempotente).
     */
    public function createConversation(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'type'            => ['required', Rule::in(['direct', 'group'])],
            // direto
            'recipient_id'    => 'required_if:type,direct|nullable|string|exists:users,id',
            // grupo
            'name'            => 'required_if:type,group|nullable|string|max:100',
            'participant_ids' => 'required_if:type,group|nullable|array|min:1',
            'participant_ids.*' => 'string|exists:users,id',
        ]);

        if ($validated['type'] === 'direct') {
            $recipientId = $validated['recipient_id'];

            if ($recipientId === $userId) {
                return response()->json(['message' => 'Não pode criar uma conversa consigo mesmo.'], 422);
            }

            // Verificar se já existe conversa directa
            $existing = ChatConversation::findDirect($userId, $recipientId);
            if ($existing) {
                $existing->load(['participants', 'lastMessage.sender', 'lastMessage.attachment']);
                return response()->json($this->formatConversation($existing, $userId), 200);
            }

            $conv = DB::transaction(function () use ($userId, $recipientId) {
                $conv = ChatConversation::create(['type' => 'direct']);
                $now  = now();
                $conv->participants()->attach([
                    $userId      => ['joined_at' => $now],
                    $recipientId => ['joined_at' => $now],
                ]);
                return $conv;
            });

            $conv->load(['participants', 'lastMessage']);
            return response()->json($this->formatConversation($conv, $userId), 201);
        }

        // ── Grupo ────────────────────────────────────────────────────────────
        $participantIds = collect($validated['participant_ids'] ?? [])
            ->push($userId)
            ->unique()
            ->values()
            ->toArray();

        $conv = DB::transaction(function () use ($participantIds, $validated) {
            $conv = ChatConversation::create([
                'type' => 'group',
                'name' => $validated['name'],
            ]);
            $now = now();
            $attachData = [];
            foreach ($participantIds as $pid) {
                $attachData[$pid] = ['joined_at' => $now];
            }
            $conv->participants()->attach($attachData);
            return $conv;
        });

        $conv->load(['participants', 'lastMessage']);
        return response()->json($this->formatConversation($conv, $userId), 201);
    }

    // ── POST /api/chat/attachments ────────────────────────────────────────────

    /**
     * Faz upload de um ficheiro e devolve o attachment criado.
     * O cliente usa o attachment_id ao enviar a mensagem seguinte.
     */
    public function uploadAttachment(Request $request): JsonResponse
    {
        $maxMb = (int) config('chat.max_attachment_mb', 50);

        $validator = Validator::make($request->all(), [
            'file'             => [
                'required',
                'file',
                "max:{$maxMb}000", // kilobytes
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp3,mp4,ogg,webm,wav,m4a',
            ],
            'duration_seconds' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file      = $request->file('file');
        $disk      = 'public'; // Force public disk
        $path      = $file->store('chat/attachments', $disk);
        $url       = Storage::disk($disk)->url($path);

        $attachment = ChatAttachment::create([
            'uploader_id'      => $request->user()->id,
            'url'              => $url,
            'name'             => $file->getClientOriginalName(),
            'mime_type'        => $file->getMimeType(),
            'size_bytes'       => $file->getSize(),
            'duration_seconds' => $request->input('duration_seconds'),
        ]);

        return response()->json(['attachment' => $this->formatAttachment($attachment)], 201);
    }
}