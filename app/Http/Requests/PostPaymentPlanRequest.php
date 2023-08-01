<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\Course;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class PostPaymentPlanRequest extends FormRequest
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
            'amount' => 'required|integer|digits_between:3,5',
            'months' => 'required_without:is_annual|integer|digits_between:1,2',
            'is_annual' => 'required_without:months|boolean',
            'course_id' => 'required|integer|exists:courses,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'amount.required' => 'Введите сумму',
            'amount.integer' => 'Введите сумму в формате целого числа',
            'amount.digits_between' => 'Введите сумму в формате целого числа от 100 до 99999',
            'months.required_without' => 'Введите количество месяцев, на которое за данную сумму даётся доступ к курсу',
            'months.integer' => 'Введите количество месяцев в формате целого числа',
            'months.digits_between' => 'Введите количество месяцев в формате целого числа от 1 до 99',
            'is_annual.required_without' => 'Введите количество месяцев в формате целого числа от 1 до 99',
            'course_id.required' => 'Укажите курс',
            'course_id.integer' => 'Укажите id курса',
            'course_id.exists' => 'Укажите существующий курс',
        ];
    }
}
