<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherProfile extends Model {
    use HasUlids;
    protected $fillable = ['user_id','teacher_code','bio','certifications','specializations','hire_date','salary','salary_currency'];
    protected $casts = ['hire_date'=>'date','certifications'=>'array','specializations'=>'array'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function classes(): HasMany { return $this->hasMany(ClassGroup::class, 'teacher_id'); }
    public function exams(): HasMany { return $this->hasMany(Exam::class, 'teacher_id'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class, 'teacher_id'); }
}
