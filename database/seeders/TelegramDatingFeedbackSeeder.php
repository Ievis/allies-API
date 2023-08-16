<?php

namespace Database\Seeders;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TelegramDatingFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = TelegramDatingUser::all()->take(50);
        $users->shift();

        foreach ($users as $user) {
            TelegramDatingFeedback::create([
                'first_user_id' => $user->id,
                'second_user_id' => 1,
                'first_user_reaction' => true,
                'second_user_reaction' => false,
                'is_resolved' => false
            ]);
        }
    }
}
