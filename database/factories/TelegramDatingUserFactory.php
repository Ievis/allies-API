<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TelegramDatingUser>
 */
class TelegramDatingUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => Subject::inRandomOrder()->first()->name,
            'category' => Category::inRandomOrder()->first()->name,
            'name' => fake()->word,
            'username' => fake()->word,
            'about' => fake()->paragraph(rand(1, 5))
        ];
    }
}
