<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model {
    use HasUlids;
    protected $fillable = ['exam_id','text','type','options','correct','points','order'];
    protected $casts = ['options'=>'array','correct'=>'integer','points'=>'integer'];

    public function exam(): BelongsTo      { return $this->belongsTo(Exam::class); }
    public function answers(): HasMany     { return $this->hasMany(ExamAnswer::class); }
}
