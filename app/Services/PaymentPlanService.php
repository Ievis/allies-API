<?php

namespace App\Services;

use App\Models\PaymentPlan;

class PaymentPlanService
{
    public static function createPaymentPlan(array $data): PaymentPlan
    {
        return PaymentPlan::create($data);
    }

    public static function updatePaymentPlan(): PaymentPlan
    {

    }

    public static function deletePaymentPlan(): PaymentPlan
    {

    }
}
