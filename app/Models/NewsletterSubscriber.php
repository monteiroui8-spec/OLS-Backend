<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasUlids;

    protected $fillable = [
        'email',
        'language',
        'country',
        'subscribed_at',
        'unsubscribed_at',
    ];

    public $timestamps = false;
}

