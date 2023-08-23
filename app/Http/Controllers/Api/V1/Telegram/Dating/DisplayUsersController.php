<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DisplayUsersController extends CommandController
{
    public function __invoke()
    {
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $username = $this->data->getUsername();
        $decision = $this->callback_query_args['decision'];
        $user = Cache::get($username . ':' . 'user-data');
        if ($decision) {
            $liked_users = $user->likedUsers()->get();

            if ($liked_users->isEmpty()) {
                $this->respondWithPopup($callback_query->id, 'Пока нет взаимностей в рамках текущих категории и предмета');

                return;
            }

            $liked_users_count = $liked_users->count();
            Cache::set($username . ':' . 'liked-users', $liked_users, 3600);

            $liked_users = $liked_users->slice(0, 10);
            $enumerated_buttons = $liked_users->map(function ($user, $number) {
                return [
                    'text' => $number + 1,
                    'callback_data' => 'users-show' . '-' . $user->id
                ];
            })
                ->chunk(5);

            $enumerated_buttons = $enumerated_buttons->map(function ($item) {
                return $item->values();
            })
                ->toArray();

            $pagination_buttons = $liked_users_count > 10
                ?
//                    [
//                        'text' => 'Назад',
//                        'callback_data' => 'users-page' . '-' . $user->id
//                    ],
                [
                    [
                        'text' => '→',
                        'callback_data' => 'users-page' . '-' . 2
                    ]
                ]

                :
                [
                    //
                ];

            $first_liked_user = $liked_users->first();
            $this->telegram_request_service
                ->setMethodName('editMessageText')
                ->setParams([
                    'chat_id' => $chat_id,
                    'message_id' => $callback_query->message->message_id,
                    'text' => 'Имя: ' .
                        $first_liked_user->name .
                        PHP_EOL .
                        'Предмет: ' .
                        $first_liked_user->subject .
                        PHP_EOL .
                        'Категория: ' .
                        $first_liked_user->category .
                        PHP_EOL .
                        PHP_EOL .
                        'О себе: ' .
                        $first_liked_user->about,
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            $enumerated_buttons[0] ?? [],
                            $enumerated_buttons[1] ?? [],
                            $pagination_buttons,
                            [
                                [
                                    'text' => 'Назад',
                                    'callback_data' => 'is-users-active' . '-' . 0
                                ]
                            ]
                        ]
                    ]),
                    'parse_mode' => 'html',
                ])
                ->make();

            return;
        }

        $relevant_users = $this->getRelevantUsers($user);
        $relevant_user = $this->getRelevantUser($user, $relevant_users, true);
        $this->nextUserIfExists($user, $relevant_user);
    }
}