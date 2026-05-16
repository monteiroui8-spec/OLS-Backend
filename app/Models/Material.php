<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'mime_type',
        'size_bytes',
        'storage_key',
        'external_url',
        'class_group_id',
        'course_id',
        'uploaded_by_id',
        'is_published',
        'views',
        'downloads',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'views'        => 'integer',
        'downloads'    => 'integer',
        'size_bytes'   => 'integer',
    ];

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
