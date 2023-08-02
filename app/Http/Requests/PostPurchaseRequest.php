<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\Course;
use App\Models\PaymentPlan;
use App\Rules\CourseHasPaymentPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory;

class PostPurchaseRequest extends FormRequest
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
            'course_id' => 'required|integer|exists:courses,id',
            'payment_plan_id' => [
                'required',
                'integer',
                'exists:payment_plans,id',
                new CourseHasPaymentPlan()
            ],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Укажите курс',
            'course_id.integer' => 'Укажите id курса в целочисленном формате',
            'course_id.exists' => 'Укажите существующий курс',
            'payment_plan_id.required' => 'Укажите ценовую политику',
            'payment_plan_id.integer' => 'Укажите id ценовой политики в целочисленном формате',
            'payment_plan_id.exists' => 'Укажите существующую ценовую политику',
        ];
    }
}
