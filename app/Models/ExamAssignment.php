<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExamAssignment extends Model
{
    protected $fillable = ['exam_id', 'assignable_id', 'assignable_type'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
