<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentRequest extends Model
{
    use HasUlids;

    protected $fillable = [
        'protocol',
        'user_id',
        'course_id',
        'class_group_id',
        'class_schedule_id',
        'status',
        'responded_at',
        'admin_notes',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
    public function classSchedule(): BelongsTo { return $this->belongsTo(ClassSchedule::class); }
}