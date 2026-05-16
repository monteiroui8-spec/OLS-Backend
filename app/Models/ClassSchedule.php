<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model {
    use HasUlids;
    protected $fillable = ['class_group_id','day_of_week','start_time','end_time','room','is_recurring'];
    protected $casts = ['is_recurring'=>'boolean','day_of_week'=>'integer'];

    public function classGroup(): BelongsTo { return $this->belongsTo(ClassGroup::class); }
}
