<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model {
    use HasUlids, SoftDeletes;
    protected $fillable = ['name','type','mime_type','size_bytes','storage_key','public_url','course_id','uploaded_by_id','is_public','downloads'];
    protected $casts = ['is_public'=>'boolean','downloads'=>'integer'];

    public function course(): BelongsTo      { return $this->belongsTo(Course::class); }
    public function uploadedBy(): BelongsTo  { return $this->belongsTo(User::class, 'uploaded_by_id'); }
}
