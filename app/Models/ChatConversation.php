<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    use HasUlids;

    protected $fillable = ['type', 'name', 'class_id'];

    // ── Relations ────────────────────────────────────────────────────────────

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'conversation_id', 'user_id')
                    ->withPivot('joined_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
                    ->latestOfMany();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Retorna o número de mensagens não lidas pelo utilizador dado.
     */
    public function unreadCountFor(string $userId): int
    {
        $readIds = \Illuminate\Support\Facades\DB::table('chat_message_reads')
            ->where('user_id', $userId)
            ->pluck('message_id')
            ->toArray();

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->when(!empty($readIds), fn ($q) => $q->whereNotIn('id', $readIds))
            ->count();
    }

    /**
     * Encontra uma conversa directa entre dois utilizadores, se existir.
     */
    public static function findDirect(string $userA, string $userB): ?self
    {
        return self::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userA))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userB))
            ->first();
    }
}