<?php

namespace App\Rules;

use App\Models\Course;
use App\Models\PaymentPlan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CourseHasPaymentPlan implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $course_id = request()->post('course_id');
        $payment_plan_id = request()->post('payment_plan_id');

        $course = Course::find($course_id);
        $payment_plan = PaymentPlan::find($payment_plan_id);

        $payment_plans = $course
            ?->payment_plans()
            ?->get();
        if (!$payment_plans?->contains($payment_plan)) {
            $fail('Укажите ценовую политику, которая есть у данного курса');
        }
    }
}
