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

        $fields = $register_data->get('fields');
        $is_register_in_action = false;
        array_walk($fields, function ($field) use (&$is_register_in_action) {
            if ($field['is_pending']) {
                $is_register_in_action = true;
            }
        });
        if ($is_register_in_action) {
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_service->setRegisterData($register_data);
            $register_service->proceed();
        } else {
            $message = $this->data->getMessage();
            $this->deleteMessage($message->message_id);
        }
    }
}