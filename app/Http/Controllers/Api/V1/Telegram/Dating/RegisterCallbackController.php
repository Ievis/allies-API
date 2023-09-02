<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class RegisterCallbackController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();
        $callback_query = $this->data->getCallbackQuery();
        $this->deleteMessage($callback_query->message->message_id);

        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_data = new RegisterData($username);
        $register_service->setRegisterData($register_data);
        $register_service->proceed();
    }
}