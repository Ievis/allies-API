<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Log;

class CommandController extends TelegramController
{
    public function nextUser($user, $relevant_user, $after_register = false)
    {
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();

        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks');

        $first_user_id = $feedbacks->isEmpty()
            ? $user->id
            : $relevant_user->id;
        $second_user_id = $first_user_id === $user->id
            ? $relevant_user->id
            : $user->id;
        $prefix = $feedbacks->isEmpty()
            ? ''
            : '<strong>Вас лайкнули!</strong>' . PHP_EOL;

        Log::info(print_r($user->toArray(), true));
        Log::info(print_r($relevant_user->toArray(), true));

        $method_name = $after_register
            ? 'sendMessage'
            : 'editMessageText';

        $this->telegram_request_service
            ->setMethodName($method_name)
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $callback_query->message->message_id,
                'text' => $prefix . 'Имя: ' .
                    $relevant_user->name .
                    PHP_EOL .
                    'Предмет: ' .
                    $relevant_user->subject .
                    PHP_EOL .
                    'Категория: ' .
                    $relevant_user->category .
                    PHP_EOL .
                    PHP_EOL .
                    'О себе: ' .
                    $relevant_user->about,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Показать',
                                'callback_data' => 'feedback-1-' . $first_user_id . '-' . $second_user_id
                            ],
                            [
                                'text' => 'Следующий',
                                'callback_data' => 'feedback-0-' . $first_user_id . '-' . $second_user_id
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }
}