<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Log;

class StartCommandController extends CommandController
{
    public function __invoke()
    {
        Log::info(123);
    }
}