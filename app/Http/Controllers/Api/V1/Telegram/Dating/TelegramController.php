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
            'reset-{attribute}' => ResetCallbackController::class,
            'confirm-{decision}' => ConfirmCallbackController::class,
            'register-{attribute}-{value}' => RegisterCallbackController::class,
            'restart-{decision}-{message_id}' => RestartCallbackController::class,
            'feedback-{decision}-{first_user_id}-{second_user_id}' => FeedbackCallbackController::class,
        ];
    }

    public function apiToken()
    {
        return env('TELEGRAM_DATING_BOT_API_TOKEN');
    }
}