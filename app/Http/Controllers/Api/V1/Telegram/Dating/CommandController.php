<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CommandController extends TelegramController
{
    protected function getRelevantUsers($user)
    {
        $relevant_users = Cache::get($user->username . ':' . 'relevant-users');
        if (collect($relevant_users)->isEmpty()) {
            $relevant_users = $user->relevantUsersWithFeedbacks()->get();
        }

        return $relevant_users;
    }

    protected function getRelevantUser($user, $relevant_users, $after_liked_users = false)
    {
        $relevant_user = $after_liked_users
            ? $relevant_users->first()
            : $relevant_users->shift();
        if (!$after_liked_users) {
            Cache::set($user->username . ':' . 'relevant-users', $relevant_users);
            Cache::set($user->username . ':' . 'current-user', $relevant_user);

            return $relevant_user;
        }

        return Cache::get($user->username . ':' . 'current-user');
    }

    protected function nextUser($user, $relevant_user, $after_register = false)
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
                                'callback_data' => 'feedback-1-' . $first_user_id . '-' . $second_user_id . '-' . 0
                            ],
                            [
                                'text' => 'Следующий',
                                'callback_data' => 'feedback-0-' . $first_user_id . '-' . $second_user_id . '-' . 0
                            ]
                        ],
                        [
                            [
                                'text' => 'Взаимности',
                                'callback_data' => 'is-users-active' . '-' . 1
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }

    protected function nextUserIfExists($user, $relevant_user, $after_register = false): bool
    {
        $callback_query = $this->data->getCallbackQuery();
        if (empty($relevant_user)) {
            $this->respondWithPopup($callback_query->id,
                'Пока что нет подходящих людей.' .
                PHP_EOL .
                'Как только найдутся люди с такими же интересами, мы вам сразу сообщим.'
            );

            return false;
        }

        $this->nextUser($user, $relevant_user, $after_register);
        return true;
    }
}