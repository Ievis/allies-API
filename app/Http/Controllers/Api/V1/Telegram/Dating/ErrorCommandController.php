<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ErrorCommandController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();
        $user_data = Cache::get($username . ':' . 'register-data');

        if ($user_data) {
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_service->setUserData($user_data);
            $register_service->proceed();
        } else {
            $this->respondWithMessage('Неверная команда!');
        }
    }
}