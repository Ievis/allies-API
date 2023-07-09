<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\Lesson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostProblemRequest extends FormRequest
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
        $lesson_id = request()->post('lesson_id');
        $problems_count = Lesson::findOrFail($lesson_id)
            ?->problems()
            ?->where('is_modification', false)->count();
        $max_number_in_lesson = $route_name === 'problems.create' ? $problems_count + 1 : $problems_count;

        return [
            'title' => 'required|string|max:255',
            'condition' => 'required|string|max:8192',
            'solution' => 'required|string|max:8192',
            'answer' => 'required|string|max:255',
            'lesson_id' => 'required|integer',
            'number_in_lesson' => 'integer|nullable|between:1,' . $max_number_in_lesson,
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'title.required' => 'Укажите название задачи',
            'title.max' => 'Максимальная длина названия - :max символов',
            'condition.required' => 'Укажите условие задачи',
            'condition.max' => 'Максимальная длина условия - :max символов',
            'solution.required' => 'Укажите решение задачи',
            'solution.max' => 'Максимальная длина решения - :max символов',
            'answer.required' => 'Укажите ответ задачи',
            'answer.max' => 'Максимальная длина ответа - :max символов',
            'lesson_id.required' => 'Укажите id урока, для которого собираетесь добавить задачу - от :min до :max',
            'lesson_id.integer' => 'id урока, для которого собираетесь добавить задачу - целое число от :min до :max',
            'number_in_lesson.integer' => 'id урока, для которого собираетесь добавить задачу - целое число от :min до :max',
            'number_in_lesson.between' => 'id урока, для которого собираетесь добавить задачу - целое число от :min до :max',
        ];
    }
}
