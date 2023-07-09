<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'course_id' => 1,
            'payment_id' => 1,
            'rebill_id' => null,
            'card_id' => 1,
            'payment_plan_id' => 1,
            'transaction_type' => 'NEW',
            'status' => 'NEW',
            'rebill_attempts' => 0,
            'is_callback_handled' => 1,
        ];
    }
}
