<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConfirmCallbackController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();

        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $user_data = Cache::get($username);
        $this->telegram_request_service
            ->setMethodName('editMessageText')
            ->setParams([
                    'chat_id' => $chat_id,
                    'message_id' => $callback_query->message->message_id,
                    'text' => '<strong>Ваши данные:</strong>' .
                        PHP_EOL .
                        'Имя: ' . $user_data['name']['value'] .
                        PHP_EOL .
                        'Предмет: ' . $user_data['subject']['value'] .
                        PHP_EOL .
                        'Категория: ' . $user_data['category']['value'],
                    'parse_mode' => 'html'
//                'reply_markup' => json_encode([
//                        'inline_keyboard' => [
//
//                        ]
                ]
            )
            ->make();

        $decision = $this->callback_query_args['decision'];
        if ($decision) {
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $user_data = Cache::get($username);
            $register_service->setUserData($user_data);
            $register_service->persist();

            return;
        }

        Cache::set($username . ':' . 'summary-message-id', $callback_query->message->message_id);
        $chat_id = $this->data->getChatId();
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => '<strong>Какое из указанных ниже полей хотите изменить?</strong>',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Имя',
                                'callback_data' => 'reset-name'
                            ]
                        ],
                        [
                            [
                                'text' => 'Предмет',
                                'callback_data' => 'reset-subject'
                            ]
                        ],
                        [
                            [
                                'text' => 'Категория',
                                'callback_data' => 'reset-category'
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }
}