<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatAttachment extends Model
{
    use HasUlids;

    protected $fillable = [
        'uploader_id',
        'url',
        'name',
        'mime_type',
        'size_bytes',
        'duration_seconds',
    ];

    protected $casts = [
        'size_bytes'       => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
