<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model {
    use HasUlids;
    protected $fillable = ['student_id','teacher_id','title','type','course_id','class_group_id','grade','max_grade','date','notes'];
    protected $casts = ['date'=>'date','grade'=>'decimal:2'];

    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(TeacherProfile::class, 'teacher_id'); }
    public function course(): BelongsTo  { return $this->belongsTo(Course::class); }
}
