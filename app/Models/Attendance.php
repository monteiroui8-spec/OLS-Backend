<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model {
    use HasUlids;
    protected $fillable = ['class_group_id','student_id','teacher_id','date','status','notes'];
    protected $casts = ['date'=>'date'];

    public function student(): BelongsTo    { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function teacher(): BelongsTo    { return $this->belongsTo(TeacherProfile::class, 'teacher_id'); }
    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
}
