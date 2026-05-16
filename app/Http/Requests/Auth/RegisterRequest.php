<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'         => ['required', 'string', 'max:80'],
            'last_name'          => ['required', 'string', 'max:80'],
            'email'              => ['required', 'email', 'unique:users,email', 'max:191'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'password'           => ['required', 'confirmed', Password::min(8)],
            'country'            => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', 'in:pt,en'],
            'preferred_currency' => ['nullable', 'in:AOA,EUR,USD'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'O primeiro nome é obrigatório.',
            'last_name.required'  => 'O último nome é obrigatório.',
            'email.required'      => 'O email é obrigatório.',
            'email.unique'        => 'Este email já está em uso.',
            'password.required'   => 'A password é obrigatória.',
            'password.confirmed'  => 'A confirmação da password não coincide.',
        ];
    }
}
