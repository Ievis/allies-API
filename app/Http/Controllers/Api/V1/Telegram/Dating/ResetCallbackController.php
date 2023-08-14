<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class ResetCallbackController extends CommandController
{
    public function __invoke()
    {
        $message_id = $this->data->getCallbackQuery()->message->message_id;
        $this->deleteMessage($message_id);

        $attribute = $this->callback_query_args['attribute'];

        $username = $this->data->getUsername();
        $user_data = Cache::get($username . ':' . 'register-data');

        $user_data[$attribute]['is_completed'] = false;


        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_service->setUserData($user_data);
        $register_service->proceed();
    }
}