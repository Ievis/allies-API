<?php

namespace App\Http\Requests;

use App\Exceptions\ValidationException;
use App\Models\Course;
use App\Models\PaymentPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

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
        $course_id = request()->post('course_id');
        $payment_plan_id = request()->post('payment_plan_id');
        $course = Course::findOrFail($course_id);
        $payment_plan = PaymentPlan::findOrFail($payment_plan_id);
        $payment_plans = $course->payment_plans()->get();
        if (!$payment_plans->contains($payment_plan)) {
            abort(404);
        }

        return [
            'course_id' => 'required|integer',
            'payment_plan_id' => 'required|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Укажите id курса',
            'course_id.integer' => 'Укажите id курса в целочисленном формате',
            'payment_plan_id.required' => 'Укажите id курса',
            'payment_plan_id.integer' => 'Укажите id курса в целочисленном формате',
        ];
    }
}
