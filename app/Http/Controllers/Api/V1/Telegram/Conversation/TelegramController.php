<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Http\Controllers\Api\V1\Telegram\AbstractTelegramController;

class TelegramController extends AbstractTelegramController
{
    public function commands()
    {
        return [
            '/start' => StartCommandController::class,
            '/error' => ErrorCommandController::class,
            '/queue' => QueueCommandController::class,
            '/exit' => ExitCommandController::class,
        ];
    }

    public function callbackQueries()
    {
        return [
            'lesson-charge-{telegram_conversation_id}-{message_id}' => ChargeCallbackController::class,
            'exit-{decision}-{message_id}' => ExitCallbackController::class
        ];
    }

    public function apiToken()
    {
        return env('TELEGRAM_CONSULTATIONS_API_TOKEN');
    }
}