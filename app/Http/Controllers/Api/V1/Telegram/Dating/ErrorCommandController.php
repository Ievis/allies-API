<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ErrorCommandController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();
        $register_data = new RegisterData($username);

        if ($register_data->exists()) {
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_service->setRegisterData($register_data);
            $register_service->proceed();
        } else {
            $this->respondWithMessage('Неверная команда!');
        }
    }
}