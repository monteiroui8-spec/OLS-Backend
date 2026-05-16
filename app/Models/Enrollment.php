<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasUlids;

    protected $fillable = [
        'student_id',
        'course_id',
        'class_group_id',
        'start_date',
        'end_date',
        'status',
        'progress_pct',
        'notes',
        'payment_start_date',
        'payment_frequency',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'payment_start_date'  => 'date',
    ];

    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
}