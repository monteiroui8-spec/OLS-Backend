<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasUlids;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'category_id',
        'image_url',
        'author_id',
        'status',
        'published_at',
        'tags',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')->orWhere('published_at', '<=', now());
                     });
    }

    public function getImageUrlAttribute($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) {
            return $value;
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : $value;
        $filename = trim(basename($path));

        if ($filename === '' || $filename === '.' || $filename === '/') {
            return null;
        }

        if (Str::contains($value, ['/storage/', 'storage/'])) {
            $relative = Str::startsWith($path, '/') ? ltrim($path, '/') : ltrim($value, '/');
            return rtrim(config('app.url'), '/') . '/' . $relative;
        }

        if (Str::contains($value, ['src/assets', 'assets/']) || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $filename) === 1) {
            return Storage::disk('public')->url("blog/images/{$filename}");
        }

        return null;
    }
}
