<?php

namespace Database\Seeders;

use App\Models\TeacherDescription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(64)->create();

        foreach ($users as $user) {
            $user_id = $user->id;

            TeacherDescription::factory(6)
                ->create([
                    'user_id' => $user_id
                ]);
        }

        User::create([
            'name' => fake()->word(),
            'surname' => fake()->word(),
            'description' => fake()->paragraph(2),
            'role_id' => 4,
            'email' => 'admin@mail.ru',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ]);
    }
}
