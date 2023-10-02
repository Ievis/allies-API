<?php

namespace App\Console\Commands;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeedbacksUpsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:feedbacks-upsert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feedbacks = Cache::tags(['feedbacks'])->get('all')?->unique()?->values() ?? collect();
        $query = TelegramDatingFeedback::query();

        foreach ($feedbacks as $feedback) {
            $query = $query->orWhere('first_user_id', $feedback['first_user_id'])
                ->where('second_user_id', $feedback['second_user_id'])
                ->where('subject', $feedback['subject'])
                ->where('category', $feedback['category']);
        }
        $feedbacks_to_update = $feedbacks->isEmpty()
            ? collect()
            : $query->get()->unique();

        $feedbacks = $feedbacks->map(function ($feedback) use ($feedbacks_to_update) {
            $feedback_to_update = $feedbacks_to_update->where('first_user_id', $feedback['first_user_id'])
                ->where('second_user_id', $feedback['second_user_id'])
                ->where('subject', $feedback['subject'])
                ->where('category', $feedback['category'])
                ->first();

            return $feedback_to_update
                ? array_merge(['id' => $feedback_to_update->id], $feedback)
                : array_merge(['id' => 0], $feedback);
        })
            ->values()
            ->toArray();

        TelegramDatingFeedback::upsert(
            $feedbacks, ['id'], ['first_user_reaction', 'second_user_reaction', 'is_resolved']
        );
        Cache::tags(['feedbacks'])->forget('all');
    }
}
