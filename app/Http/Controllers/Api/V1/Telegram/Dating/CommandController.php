<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;

class CommandController extends TelegramController
{
    protected function getUserOrDie(?string $username)
    {
        $user = TelegramDatingUser::query()
            ->where('username', $username)
            ->first();

        if (empty($user)) {
            $this->respondWithMessage('Неверная команда');

            die();
        }

        return $user;
    }
}