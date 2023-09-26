<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StartCommandController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();
        $chat_id = $this->data->getChatId();
        $user_data = new UserData($username);

        $register_data = Cache::get($username . ':register-data');
        if ($register_data) {
            $summary_message_id = $register_data->get('summary_message_id');
            $reset_bot_message_id = $register_data->get('reset_bot_message_id');
            $confirm_message_id = $register_data->get('confirm_message_id');
            if ($reset_bot_message_id) {
                $this->deleteMessage($reset_bot_message_id);
            }
            if ($confirm_message_id) {
                $this->deleteMessage($confirm_message_id);
            }
            if ($summary_message_id) {
                $this->deleteMessage($summary_message_id);
            }
            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_data = new RegisterData($username);
            $register_data->set('fields', $register_data->getFields());
            $register_data->delete('summary_message_id');
            $register_data->delete('confirm_message_id');
            $register_data->delete('reset_bot_message_id');
            $register_data->save();
            $register_service->setRegisterData($register_data);
            $register_service->proceed();

            die();
        }

        $main_message_id = $user_data->get('main_message_id');
        if ($main_message_id) {
            $register_data = new RegisterData($username);
            $reset_bot_message_id = $register_data->get('reset_bot_message_id');
            $confirm_message_id = $register_data->get('confirm_message_id');
            if ($reset_bot_message_id) {
                $this->deleteMessage($reset_bot_message_id);
            }
            if ($confirm_message_id) {
                $this->deleteMessage($confirm_message_id);
            }
            $message = $this->data->getMessage();
            $this->telegram_request_service
                ->setMethodName('sendMessage')
                ->setParams([
                    'chat_id' => $chat_id,
                    'text' => '<strong>Вы уже вводили свои данные.</strong>' .
                        PHP_EOL .
                        '<strong>Вы можете ввести их заново, изменить интересующие из имеющихся, или отменить действие команды /start</strong>' .
                        PHP_EOL .
                        '<strong>В случае, если Вы введете свои данные заново, все Ваши имеющиеся симпатии и реакции сохранятся, и вы сможете к ним вернуться, когда установите такие же категорию и предмет, которые имели ранее.</strong>' .
                        PHP_EOL .
                        PHP_EOL .
                        '<strong>Выберите опцию ниже...</strong>',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => 'Ввести заново',
                                    'callback_data' => 'restart-restart' . '-' . $message->message_id
                                ]
                            ],
                            [
                                [
                                    'text' => 'Изменить',
                                    'callback_data' => 'restart-update' . '-' . $message->message_id
                                ]
                            ],
                            [
                                [
                                    'text' => 'Отменить',
                                    'callback_data' => 'restart-cancel' . '-' . $message->message_id
                                ]
                            ],
                        ]
                    ]),
                    'parse_mode' => 'html',
                ])
                ->make();

            return;
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->data->getChatId(),
                'text' => 'Привет, это бот для знакомств учеников!' . PHP_EOL . '<strong>Для начала нам нужно узнать твоё имя, предмет и категорию...</strong>',
                'parse_mode' => 'html',
            ])
            ->make();

        $register_service = new RegisterService();
        $register_service->setTelegramUserData($this->data);
        $register_service->setCallbackArgs($this->callback_query_args);

        $register_data = new RegisterData($username);
        $register_data->save();
        $register_service->setRegisterData($register_data);
        $register_service->proceed();
    }
}