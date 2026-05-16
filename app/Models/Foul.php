<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Foul extends Model {
    use HasUlids;
    protected $fillable = ['student_id','course_id','description','amount','currency','status','paid_at','due_date','created_by'];
    protected $casts = ['paid_at'=>'datetime','due_date'=>'date'];

    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function course(): BelongsTo  { return $this->belongsTo(Course::class); }
}
