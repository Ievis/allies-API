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
            'is-users-active-{decision}' => DisplayUsersController::class,
            'users-page-{page}' => UsersPaginationController::class,
            'users-show-{user_id}-{page}' => UsersShowController::class,
            'confirm-{decision}' => ConfirmCallbackController::class,
            'register-{attribute}-{value}' => RegisterCallbackController::class,
            'restart-{action}-{message_id}' => RestartCallbackController::class,
            'feedback-{decision}-{first_username}-{second_username}' => FeedbackCallbackController::class,
        ];
    }

    public function apiToken()
    {
        return env('TELEGRAM_DATING_BOT_API_TOKEN');
    }
}