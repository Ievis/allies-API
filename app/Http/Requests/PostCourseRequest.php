<?php

namespace App\Http\Requests;

use App\Exceptions\RespondWithMessageException;
use App\Exceptions\ValidationException;
use App\Models\Category;
use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostCourseRequest extends FormRequest
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
        $route_name = request()->route()->getName();
        $preview_requirement = $route_name === 'courses.create'
            ? 'required'
            : 'nullable';

        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'preview' => $preview_requirement . '|image|max:15360',
            'is_visible' => 'nullable|boolean',
            'description' => 'required|string|max:2048',
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
            'name.max' => 'Максимальная длина названия курса - 255 символов',
            'category_id.required' => 'Укажите категорию',
            'category_id.integer' => 'id категории - целое число',
            'category_id.exists' => 'Укажите существующую категорию',
            'subject_id.required' => 'Укажите предмет',
            'subject_id.integer' => 'id предмета - целое число',
            'subject_id.exists' => 'Укажите существующий предмет',
            'preview.required' => 'Загрузите фотографию курса',
            'preview.image' => 'Загрузите фотографию курса',
            'price.required' => 'Введите цену курса',
            'price.integer' => 'Цена должна быть целым числом',
            'price.digits_between' => 'Цена курса может быть от 100 до 99999 рублей',
            'is_visible.boolean' => 'Укажите видмиость курса в булевом формате',
            'description.required' => 'Введите описание',
            'description.max' => 'Максимальная длина описание курса - 2048 символов',
        ];
    }
}
