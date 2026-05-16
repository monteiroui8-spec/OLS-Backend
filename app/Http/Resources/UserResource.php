<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'first_name'         => $this->first_name,
            'last_name'          => $this->last_name,
            'full_name'          => $this->full_name,
            'email'              => $this->email,
            'username'           => $this->username,
            'phone'              => $this->phone,
            'cpf'                => $this->cpf,
            'role'               => $this->role,
            'status'             => $this->status,
            'avatar_url'         => $this->avatar_url,
            'country'            => $this->country,
            'preferred_language' => $this->preferred_language,
            'preferred_currency' => $this->preferred_currency,
            'email_verified_at'  => $this->email_verified_at?->toISOString(),
            'last_login_at'      => $this->last_login_at?->toISOString(),
            'created_at'         => $this->created_at->toISOString(),
            'has_paid_first_time'=> $this->when($this->role === 'student', function() {
                if (!$this->studentProfile) return false;
                return \App\Models\Payment::where('student_id', $this->studentProfile->id)
                    ->where('status', 'paid')
                    ->exists();
            }),

            // Perfis — incluídos apenas quando carregados com load()
            'student_profile' => $this->whenLoaded('studentProfile', fn () => [
                'id'            => $this->studentProfile?->id,
                'student_code'  => $this->studentProfile?->student_code,
                'current_level' => $this->studentProfile?->current_level,
                'date_of_birth' => $this->studentProfile?->date_of_birth?->toDateString(),
                'address'       => $this->studentProfile?->address,
                'nationality'   => $this->studentProfile?->nationality,
                'notes'         => $this->studentProfile?->notes,
            ]),

            'teacher_profile' => $this->whenLoaded('teacherProfile', fn () => [
                'id'           => $this->teacherProfile?->id,
                'employee_id'  => $this->teacherProfile?->employee_id,
                'specialties'  => $this->teacherProfile?->specialties,
                'bio'          => $this->teacherProfile?->bio,
                'hire_date'    => $this->teacherProfile?->hire_date?->toDateString(),
                'hourly_rate'  => $this->teacherProfile?->hourly_rate,
            ]),

            'admin_profile' => $this->whenLoaded('adminProfile', fn () => [
                'department'   => $this->adminProfile?->department,
                'position'     => $this->adminProfile?->position,
            ]),
        ];
    }
}
