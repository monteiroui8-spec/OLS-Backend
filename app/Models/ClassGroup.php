<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassGroup extends Model {
    use HasUlids, SoftDeletes;
    protected $fillable = ['name','course_id','teacher_id','year','capacity','room','is_active'];
    protected $casts = ['is_active'=>'boolean','capacity'=>'integer'];

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(TeacherProfile::class, 'teacher_id'); }
    public function schedules(): HasMany { return $this->hasMany(ClassSchedule::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'enrollments', 'class_group_id', 'student_id')
                    ->withPivot('status','progress_pct','start_date')
                    ->withTimestamps();
    }

    public function hasCapacity(): bool
    {
        return $this->students()->wherePivot('status','active')->count() < $this->capacity;
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeSearch($q, $s) { return $q->where('name','ilike',"%{$s}%"); }
}
