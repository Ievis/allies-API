<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'email.required' => 'Введите email',
            'email.email' => 'Введите email',
            'email.max' => 'Максимальная длина email\'а - 255 символов',
            'password.required' => 'Введите пароль',
            'password.string' => 'Введите пароль в формате строки',
            'password.max' => 'Максимальная длина пароля - 255 символов',
        ];
    }
}
