<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostTeacherDescriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->whereIn('role_id', [User::TEACHER, User::MAIN_TEACHER]);
                })
            ],
            'description' => 'required|string|max:1024',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Введите id пользователя',
            'user_id.integer' => 'Введите id пользователя в целочисленном формате',
            'user_id.exists' => 'Данный пользователь не существует или не является учителем',
            'description.required' => 'Укажите описание',
            'description.string' => 'Укажите описание в формате строки',
            'description.max' => 'Укажите описание в формате строки с максимальным размером - 1024 символа',
        ];
    }
}
