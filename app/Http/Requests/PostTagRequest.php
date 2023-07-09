<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostTagRequest extends FormRequest
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
            'name' => 'required|string|max:1024',
            'for' => 'required|string|max:1024',
            'id' => 'required|integer|digits_between:1,10',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        $model_name = 'App\\Models\\' . ucfirst(strtolower(request()->input('for')));
        (new $model_name())::findOrFail(request()->input('id'));

        return [
            'name.required' => 'Введите название тега',
            'name.string' => 'Введите название тега в формате строки',
            'name.max' => 'Введите название тега в формате строки с максимальным размером - 1024 символа',
            'for.required' => 'Укажите, для чего создаёте тег. Теги доступны для курсов, уроков и постов',
            'for.string' => 'Укажите, для чего создаёте тег в формате строки',
            'for.max' => 'Укажите, для чего создаёте тег в формате строки с максимальным размером - 1024 символа',
            'id.required' => 'Введите id сущности, для которой создаёте тег',
            'id.integer' => 'Введите id сущности, для которой создаёте тег в формате целого числа',
            'id.digits_between' => 'Введите id сущности, для которой создаёте тег в формате целого числа до 10 знаков',
        ];
    }
}
