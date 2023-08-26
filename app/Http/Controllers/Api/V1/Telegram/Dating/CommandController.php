<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class CommandController extends TelegramController
{
    public function getRelevantUsers($user)
    {
        $relevant_users = Cache::get($user->username . ':' . 'relevant-users');
        if (collect($relevant_users)->isEmpty()) {
            $relevant_users = $user->relevantUsersWithFeedbacks()->get();
        }

        return $relevant_users;
    }

    public function getRelevantUser($user, $relevant_users, $after_liked_users = false)
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

        $method_name = $after_register
            ? 'sendMessage'
            : 'editMessageText';

        return $this->telegram_request_service
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

    public function nextUserIfExists($user, $relevant_user, $after_register = false)
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

        return $this->nextUser($user, $relevant_user, $after_register);
    }

    protected function displayLikedUserWithPagination($liked_user, $enumerated_buttons, $pagination_buttons)
    {
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();

        $this->telegram_request_service
            ->setMethodName('editMessageText')
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $callback_query->message->message_id,
                'text' => 'Ник в telegram: ' .
                    '@' .
                    $liked_user->username .
                    PHP_EOL .
                    'Имя: ' .
                    $liked_user->name .
                    PHP_EOL .
                    'Предмет: ' .
                    $liked_user->subject .
                    PHP_EOL .
                    'Категория: ' .
                    $liked_user->category .
                    PHP_EOL .
                    PHP_EOL .
                    'О себе: ' .
                    $liked_user->about,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        $enumerated_buttons[0] ?? [],
                        $enumerated_buttons[1] ?? [],
                        $enumerated_buttons[2] ?? [],
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
    }

    protected function getLikedUsersIfExist($user)
    {
        $username = $this->data->getUsername();
        $callback_query = $this->data->getCallbackQuery();
        $liked_users = Cache::get($username . ':' . 'liked-users');
        if (empty($liked_users)) {
            $liked_users = $user->likedUsers()->get();
            Cache::set($username . ':' . 'liked-users', $liked_users, 3600);
        }

        if ($liked_users->isEmpty()) {
            $this->respondWithPopup($callback_query->id, 'Пока нет взаимностей в рамках текущих категории и предмета');

            return null;
        }

        return $liked_users;
    }

    protected function getLikedUsersEnumeratedButtons($liked_users, $page)
    {
        $per_page = 9;
        $liked_users = $liked_users->forPage($page, $per_page);
        $enumerated_buttons = $liked_users->map(function ($user, $number) use ($page) {
            return [
                'text' => $number + 1,
                'callback_data' => 'users-show' . '-' . $user->id . '-' . $page
            ];
        })->values()->chunk(3);

        return $enumerated_buttons->map(function ($item) {
            return $item->values();
        })->toArray();
    }

    protected function getLikedUsersPaginationButtons($liked_users, $page)
    {
        return array_values(array_filter([
            $this->getPrevPageButton($page),
            $this->getNextPageButton($liked_users, $page)
        ]));
    }

    private function hasPrevPage($page)
    {
        return $page != 1;
    }

    private function hasNextPage($liked_users, $page)
    {
        $per_page = 9;
        $next_page_liked_users = $liked_users->forPage($page + 1, $per_page);
        return $next_page_liked_users->isNotEmpty();
    }

    private function getPrevPageButton($page)
    {
        return $this->hasPrevPage($page)
            ? [
                'text' => '←',
                'callback_data' => 'users-page' . '-' . $page - 1
            ]
            : [];
    }

    private function getNextPageButton($liked_users, $page)
    {
        return $this->hasNextPage($liked_users, $page)
            ? [
                'text' => '→',
                'callback_data' => 'users-page' . '-' . $page + 1
            ]
            : [];
    }
}