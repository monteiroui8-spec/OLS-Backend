<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model {
    use HasUlids, SoftDeletes;
    protected $fillable = ['title','class_group_id','course_id','teacher_id','duration','max_attempts','status','due_date','start_date','end_date','total_points','pass_score'];
    protected $casts = ['due_date'=>'datetime','start_date'=>'datetime','end_date'=>'datetime'];

    public function questions(): HasMany { return $this->hasMany(Question::class)->orderBy('order'); }
    public function attempts(): HasMany  { return $this->hasMany(ExamAttempt::class); }
    public function assignments(): HasMany { return $this->hasMany(ExamAssignment::class); }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(TeacherProfile::class, 'teacher_id'); }

    public function getAverageScoreAttribute(): ?float
    {
        return $this->attempts()->where('status','completed')->avg('score');
    }
    public function scopePublished($q)  { return $q->where('status','published'); }
    public function scopeForTeacher($q, $id) { return $q->where('teacher_id', $id); }
}
