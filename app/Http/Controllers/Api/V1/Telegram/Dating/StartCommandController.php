<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class StartCommandController extends CommandController
{
    public function __invoke()
    {
        $user_data = $this->setUserData();
        $username = $this->data->getUsername();

        $main_message_id = $user_data->get('main_message_id');
        if ($main_message_id) {
            $this->deleteMessage($main_message_id);
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->data->getChatId(),
                'text' => 'Привет, это бот для знакомств учеников!' . PHP_EOL . '<strong>Для начала нам нужно узнать твоё имя, предмет и категорию...</strong>',
                'parse_mode' => 'html',
            ])
            ->make();

        $fields = [
            'name' => [
                'is_completed' => false,
                'is_pending' => false,
                'type' => 'text',
                'value' => null,
                'method' => 'name'
            ],
            'subject' => [
                'is_completed' => false,
                'is_pending' => false,
                'type' => 'callback',
                'value' => null,
                'method' => 'subject'
            ],
            'category' => [
                'is_completed' => false,
                'is_pending' => false,
                'type' => 'callback',
                'value' => null,
                'method' => 'category'
            ],
            'about' => [
                'is_completed' => false,
                'is_pending' => false,
                'type' => 'text',
                'value' => null,
                'method' => 'about'
            ],
        ];

        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_data = new RegisterData($username, [
            'fields' => $fields
        ]);
        $register_data->save();
        $register_service->setRegisterData($register_data);
        $register_service->proceed();

    }
}