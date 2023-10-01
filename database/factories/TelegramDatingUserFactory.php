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
        $subjects = [
            'Математика',
            'Физика',
            'Информатика',
            'Химия',
            'Русский язык',
            'География',
            'Биология',
            'Литература',
            'История',
            'Иностранные языки',
        ];
        return [
            'subject' => $subjects[array_rand($subjects)],
            'category' => Category::inRandomOrder()->first()->name,
            'city' => 'Москва',
            'name' => fake()->word,
            'username' => fake()->unique()->name,
            'chat_id' => '0000000000',
            'about' => fake()->paragraph(rand(1, 5)),
            'is_waiting' => false
        ];
    }
}
