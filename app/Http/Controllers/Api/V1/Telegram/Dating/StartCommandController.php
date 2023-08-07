<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StartCommandController extends CommandController
{
    public function __invoke()
    {
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->data->getChatId(),
                'text' => 'Привет, это бот для знакомств учеников!' . PHP_EOL . '<strong>Для начала нам нужно узнать твоё имя, предмет и категорию...</strong>',
                'parse_mode' => 'html',
            ])
            ->make();

        $user_data = [
            'name' => [
                'is_completed' => false,
                'is_pending' => false,
                'value' => null,
                'method' => 'name'
            ],
            'subject' => [
                'is_completed' => false,
                'is_pending' => false,
                'value' => null,
                'method' => 'subject'
            ],
            'category' => [
                'is_completed' => false,
                'is_pending' => false,
                'value' => null,
                'method' => 'category'
            ],
        ];

        Cache::set($this->data->getUsername(), $user_data, 60);

        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_service->setUserData($user_data);
        $register_service->proceed();

    }
}