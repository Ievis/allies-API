<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class RefreshDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-db';

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
        Cache::clear();
        Artisan::call('migrate:rollback');
        Artisan::call('migrate');
        Artisan::call('db:seed');
        Artisan::call('db:seed --class=TelegramDatingUserSeeder');
        Artisan::call('db:seed --class=TelegramDatingFeedbackSeeder');
        Artisan::call('db:seed --class=CitySeeder');
    }
}
