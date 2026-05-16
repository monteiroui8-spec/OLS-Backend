<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ContactFormSubmission extends Model
{
    use HasUlids;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'subject',
        'message',
        'language',
        'ip_address',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];
}

