<?php

namespace App\Console\Commands;

use App\Models\TelegramDatingUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-query';

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
        $user = TelegramDatingUser::query()
            ->where('username', 'levchaba')
            ->first();

        $relevant_users = $user->relevantUsersWithFeedbacks();

        $cached_feedbacks = collect(Cache::tags(['feedbacks'])->get('all'));
        $liked_feedbacks = $cached_feedbacks->where('first_user_reaction', true)
            ->where('second_user_id', $user->id)
            ->where('subject', $user->subject)
            ->where('category', $user->category)
            ->where('is_resolved', false)
            ->unique()
            ->values();
        $first_excluded_ids = $cached_feedbacks->where('first_user_id', $user->id)
            ->where('subject', $user->subject)
            ->where('category', $user->category)
            ->pluck('second_user_id')
            ->unique()
            ->values()
            ->toArray();
        $second_excluded_ids = $cached_feedbacks->where('second_user_id', $user->id)
            ->where('subject', $user->subject)
            ->where('category', $user->category)
            ->pluck('first_user_id')
            ->unique()
            ->values()
            ->toArray();
        $excluded_ids = array_merge($first_excluded_ids, $second_excluded_ids);
        $included_user_ids = $liked_feedbacks->pluck('first_user_id')->toArray();

        dd($relevant_users, $included_user_ids, $excluded_ids);
    }
}
