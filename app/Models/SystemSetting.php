<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model {
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';
    public $timestamps    = false;
    protected $fillable   = ['key','value','description','updated_by'];
    protected $casts      = ['updated_at'=>'datetime'];

    public static function get(string $key, $default = null)
    {
        return self::find($key)?->value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
    }
}
