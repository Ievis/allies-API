<?php

namespace App\Http\Requests;

use App\Exceptions\RespondWithMessageException;
use App\Exceptions\ValidationException;
use App\Models\Category;
use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostConsultationRequest extends FormRequest
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
            'name' => 'required|string|max:128',
            'phone' => 'required|string|max:128',
            'email' => 'email|string|max:128',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'name.required' => 'Введите имя',
            'name.string' => 'Введите имя в формате строки',
            'name.max' => 'Введите имя в формате строки с максимальным размером 128 символов',
            'phone.required' => 'Введите номер телефона в формате строки с максимальным размером 128 символов',
            'phone.string' => 'Введите номер телефон в формате строки с максимальным размером 128 символов',
            'phone.max' => 'Введите номер телефон в формате строки с максимальным размером 128 символов',
            'email.string' => 'Введите свой email в формате строки',
            'email.email' => 'Введите свой email',
            'email.max' => 'Введите свой email в формате строки с максимальным размером 128 символов',
        ];
    }
}
