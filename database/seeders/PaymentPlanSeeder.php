<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\PaymentPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $payment_plan = new PaymentPlan([
                'amount' => 3600,
                'months' => 1,
                'is_annual' => false,
                'course_id' => $course->id,
            ]);

            $payment_plan->save();
        }
    }
}
