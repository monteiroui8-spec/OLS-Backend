<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model {
    use HasUlids;
    protected $fillable = ['student_id','course_id','enrollment_id','description','amount','currency','amount_aoa','due_date','paid_at','status','method','transaction_ref','proof_url','receipt_url','invoice_number','notes','rejection_reason'];
    protected $casts = ['due_date'=>'date','paid_at'=>'datetime','amount'=>'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Payment $p) {
            if (!$p->invoice_number) {
                $year  = now()->year;
                $count = self::whereYear('created_at', $year)->count() + 1;
                $p->invoice_number = 'INV-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function student(): BelongsTo { return $this->belongsTo(StudentProfile::class, 'student_id'); }
    public function course(): BelongsTo  { return $this->belongsTo(Course::class); }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function scopeOverdue($query)
    {
        return $query->where('status','pending')->where('due_date','<', now()->toDateString());
    }

    public function scopeForStudent($query, $studentId) { return $query->where('student_id', $studentId); }
}
