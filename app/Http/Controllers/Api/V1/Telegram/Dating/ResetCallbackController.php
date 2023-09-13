<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class ResetCallbackController extends CommandController
{
    public function __invoke()
    {
        $attribute = $this->input('attribute');

        $username = $this->data->getUsername();
        $message_id = $this->data->getCallbackQuery()->message->message_id;
        $this->deleteMessage($message_id);

        if(Cache::has($username . 'user-data')) {
            die();
        }

        $register_data = new RegisterData($username);
        $fields = $register_data->get('fields');
        $fields[$attribute]['is_completed'] = false;
        $register_data->set('fields', $fields);

        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_service->setRegisterData($register_data);
        $register_service->proceed();
    }
}