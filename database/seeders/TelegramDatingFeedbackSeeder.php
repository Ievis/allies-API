<?php

namespace Database\Seeders;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class TelegramDatingFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = TelegramDatingUser::query()
            ->where('username', '!=', 'levchaba')
            ->get();
        $me = TelegramDatingUser::query()
            ->where('username', 'levchaba')
            ->first();

        foreach ($users as $user) {
            $first_user_reaction = (bool)random_int(0, 1);
            $is_resolved = !$first_user_reaction;

            $feedback = TelegramDatingFeedback::create([
                'first_user_id' => $user->id,
                'second_user_id' => $me->id,
                'first_user_reaction' => $first_user_reaction,
                'second_user_reaction' => false,
                'subject' => $user->subject,
                'category' => $user->category,
                'is_resolved' => $is_resolved
            ]);

//            $cached_feedbacks = Cache::tags(['feedbacks'])->get('all') ?? collect();
//            $cached_feedbacks->push(collect($feedback)->except(['created_at', 'updated_at', 'id'])->toArray());
//            Cache::tags(['feedbacks'])->put('all', $cached_feedbacks);
        }
    }
}
