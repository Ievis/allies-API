<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Http\Controllers\Api\V1\Telegram\AbstractTelegramController;

class TelegramController extends AbstractTelegramController
{
    public function commands()
    {
        return [
            '/start' => StartCommandController::class,
            '/error' => ErrorCommandController::class,
        ];
    }

    public function callbackQueries()
    {
        return [
            'restart-{decision}-{message_id}' => RestartCallbackController::class,
            'feedback-{decision}-{chat_id}' => RestartCallbackController::class,
        ];
    }

    public function apiToken()
    {
        return env('TELEGRAM_DATING_BOT_API_TOKEN');
    }
}