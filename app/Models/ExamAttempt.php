<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model {
    use HasUlids;
    protected $fillable = ['exam_id','student_id','status','started_at','submitted_at','score','passed'];
    protected $casts = ['started_at'=>'datetime','submitted_at'=>'datetime','score'=>'decimal:2','passed'=>'boolean'];

    public function exam(): BelongsTo     { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo  { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function answers(): HasMany    { return $this->hasMany(ExamAnswer::class, 'attempt_id'); }
}
