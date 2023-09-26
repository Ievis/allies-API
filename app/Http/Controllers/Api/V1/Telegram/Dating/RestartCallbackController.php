<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Log;

class RestartCallbackController extends CommandController
{
    public function __invoke()
    {
        $action = $this->input('action');
        $message_id = $this->input('message_id');
        $callback_query = $this->data->getCallbackQuery();
        $username = $this->data->getUsername();
        $this->deleteMessage($callback_query->message->message_id);
        $this->deleteMessage($message_id);

        if ($action == 'cancel') {
            $this->deleteMessage($callback_query->message->message_id);
            $this->deleteMessage($message_id);

            return;
        }
        if ($action == 'restart') {
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_data = new RegisterData($username);
            $register_data->save();
            $register_service->setRegisterData($register_data);
            $register_service->proceed();
        }
        if ($action == 'update') {
            $this->setUserData();
            $user = $this->user_data->get('user');
            $register_data = new RegisterData($username);
            $register_data->set('fields', [
                'name' => [
                    'is_completed' => true,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => $user->name,
                    'method' => 'name'
                ],
                'subject' => [
                    'is_completed' => true,
                    'is_pending' => false,
                    'type' => 'callback',
                    'value' => $user->subject,
                    'method' => 'subject'
                ],
                'category' => [
                    'is_completed' => true,
                    'is_pending' => false,
                    'type' => 'callback',
                    'value' => $user->category,
                    'method' => 'category'
                ],
                'city' => [
                    'is_completed' => true,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => $user->city,
                    'method' => 'city'
                ],
                'about' => [
                    'is_completed' => true,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => $user->about,
                    'method' => 'about'
                ],
            ]);

            $this->deleteMessage($this->user_data->get('main_message_id'));
            $this->deleteMessage($this->user_data->get('greeting_message_id'));
            $response = $this->telegram_request_service
                ->setMethodName('sendMessage')
                ->setParams([
                    'chat_id' => $this->data->getChatId(),
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
                            ],
                            [
                                [
                                    'text' => 'Город',
                                    'callback_data' => 'reset-city'
                                ]
                            ],
                            [
                                [
                                    'text' => 'О себе',
                                    'callback_data' => 'reset-about'
                                ]
                            ],
                        ]
                    ]),
                    'parse_mode' => 'html',
                ])
                ->make();

            if ($response->ok) {
                $register_data->set('confirm_message_id', $response->result->message_id);
            }
            $register_data->set('summary_message_id', $this->user_data->get('summary_message_id'));
            $register_data->save();
        }
    }
}