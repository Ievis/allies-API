<?php

namespace App\Http\Resources\V1\PaymentPlan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->with = [
            'success' => true
        ];

        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'months' => $this->months,
            'is_annual' => $this->is_annual
        ];
    }
}
