<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostPaymentPlanRequest;
use App\Http\Resources\V1\PaymentPlan\PaymentPlanCollectionResource;
use App\Http\Resources\V1\PaymentPlan\PaymentPlanResource;
use App\Services\PaymentPlanService;

class PaymentPlanController extends Controller
{
    public function index(): PaymentPlanCollectionResource
    {

    }

    public function store(PostPaymentPlanRequest $request)
    {
        $data = $request->validated();
        $payment = PaymentPlanService::createPaymentPlan($data);

        return new PaymentPlanResource($payment);
    }

    public function show()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
