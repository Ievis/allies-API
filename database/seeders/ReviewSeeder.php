<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\ReviewResult;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::factory(12)->create();

        foreach ($reviews as $review) {
            $review_result = ReviewResult::factory(rand(1, 4))
                ->create([
                    'review_id' => $review->id
                ]);
        }
    }
}
