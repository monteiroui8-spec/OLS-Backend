<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUlids, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'first_name','last_name','email','username','phone','cpf','password',
        'role','status','avatar_url','country',
        'preferred_language','preferred_currency',
        'email_verified_at','last_login_at',
        // Dados pessoais do aluno
        'bi_number','birth_date','gender','nationality',
        'address','province','marital_status',
        'guardian_name','guardian_phone',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'two_factor_enabled'=> 'boolean',
        'birth_date'        => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isTeacher(): bool  { return $this->role === 'teacher'; }
    public function isStudent(): bool  { return $this->role === 'student'; }
    public function isActive(): bool   { return $this->status === 'active'; }

    public function scopeActive($query)        { return $query->where('status', 'active'); }
    public function scopeByRole($query, $role) { return $query->where('role', $role); }
    public function scopeSearch($query, $s)
    {
        $term = '%' . mb_strtolower($s) . '%';

        return $query->where(fn ($q) =>
            $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
              ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
              ->orWhereRaw('LOWER(email) LIKE ?', [$term])
        );
    }
}