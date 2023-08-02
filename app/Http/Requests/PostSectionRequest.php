<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\Course;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PostSectionRequest extends FormRequest
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
            'course_id' => 'required|integer'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        Course::findOrFail(request()->input('course_id'));

        return [
            'name.required' => 'Введите название тега',
            'name.string' => 'Введите название тега в формате строки',
            'name.max' => 'Введите название тега в формате строки с максимальным размером - 1024 символа',
            'course_id.required' => 'Введите id курса',
            'course_id.integer' => 'Введите id курса в целочисленном формате',
        ];
    }
}
