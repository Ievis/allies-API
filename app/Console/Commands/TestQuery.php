<?php

namespace App\Console\Commands;

use App\Models\TelegramDatingUser;
use Illuminate\Console\Command;

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
        $user = TelegramDatingUser::first();
        $relevant_users = $user->relevantUsersWithFeedbacks()->get()->toArray();
        $relevant_users = $user->likedUsers()->get()->toArray();
        dd($relevant_users);
    }
}
