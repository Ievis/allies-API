<?php

namespace App\Console\Commands;

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
        $feedbacks = Cache::tags(['feedbacks'])->get('all')->uniquie();
        DB::table('telegram_dating_feedback')->upsert($feedbacks, ['first_user_id', 'second_user_id'], ['first_user_reaction', 'second_user_reaction', 'is_resolved']);
    }
}
