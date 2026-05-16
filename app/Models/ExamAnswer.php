<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model {
    use HasUlids;
    protected $fillable = ['attempt_id','question_id','answer','is_correct'];
    protected $casts = ['is_correct'=>'boolean','answer'=>'integer'];

    public function attempt(): BelongsTo  { return $this->belongsTo(ExamAttempt::class); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}
