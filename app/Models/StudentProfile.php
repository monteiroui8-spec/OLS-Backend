<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model {
    use HasUlids;
    protected $fillable = ['user_id','student_code','current_level','enrollment_date','notes'];
    protected $casts = ['enrollment_date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class, 'student_id'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class, 'student_id'); }
    public function examAttempts(): HasMany { return $this->hasMany(ExamAttempt::class, 'student_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class, 'student_id'); }
    public function fouls(): HasMany { return $this->hasMany(Foul::class, 'student_id'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class, 'student_id'); }
}
