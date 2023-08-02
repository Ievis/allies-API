<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostUserRequest extends FormRequest
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
        $route_name = request()->route()->getName();

        $unique_exception = ($route_name === 'users.create')
            ? ''
            : ',email,' . request('user')->id;

        return [
            'email' => 'required|email|string|max:255|unique:users' . $unique_exception,
            'password' => 'required|string|min:6|max:255|confirmed',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:15360'
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
            'email.unique' => 'Данный email уже занят',
            'password.required' => 'Введите пароль',
            'password.min' => 'Минимальная длина пароля - 6 символов',
            'password.max' => 'Максимальная длина пароля - 255 символов',
            'password.confirmed' => 'Введённые пароли не совпадают',
            'name.required' => 'Введите имя',
            'name.max' => 'Максимальная длина имени - 255 символов',
            'image.image' => 'Загрузите фотографию',
            'image.max' => 'Максимальный размер фотографии - 15 Мб',
        ];
    }
}
