<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Log;

class CommandController extends TelegramController
{
    public UserData $user_data;

    public function setUserData(UserData|array $user_data = []): UserData
    {
        $this->user_data = $user_data instanceof UserData
            ? $user_data
            : new UserData($this->data->getUsername(), $user_data);

        $user = $this->user_data->get('user');
        if (empty($user)) {
            $this->respondWithMessage(
                'Ваши данные устарели.' .
                PHP_EOL .
                '<strong>Пройдите процедуру регистрации ещё раз!.</strong>' .
                PHP_EOL
            );

            die();
        }

        return $this->user_data;
    }

    private function getRelevantUsers()
    {
        $user = $this->user_data->get('user');
        $relevant_users = $this->user_data->get('relevant_users');

        if ($relevant_users->isEmpty()) {
            $relevant_users = $user->relevantUsersWithFeedbacks();
            $this->user_data->set('relevant_users', $relevant_users);
        }

        return $relevant_users;
    }

    public function getRelevantUser($after_liked_users = false)
    {
        $relevant_users = $this->getRelevantUsers();

        $relevant_user = $after_liked_users
            ? $relevant_users->first()
            : $relevant_users->shift();
        if (!$after_liked_users) {
            $this->user_data->set('relevant_users', $relevant_users);
            $this->user_data->set('current_user', $relevant_user);

            return $relevant_user;
        }

        return $this->user_data->get('current_user');
    }

    public function nextUserIfExists($relevant_user, $after_register = false)
    {
        $user = $this->user_data->get('user');

        if (!empty($relevant_user)) {
            if ($user->is_waiting) {
                $user->is_waiting = false;
                $user->save();
            }

            return $this->nextUser($user, $relevant_user, $after_register);
        }

        $this->showLikesButton();
        if (!$user->is_waiting) {
            $user->is_waiting = true;
            $user->save();
        }

        return false;
    }

    private function nextUser($user, $relevant_user, $after_register = false)
    {
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks') ?? collect();

        $first_username = $feedbacks->isEmpty()
            ? $user->username
            : $relevant_user->username;
        $second_username = $first_username === $user->username
            ? $relevant_user->username
            : $user->username;
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
                    $user->subject .
                    PHP_EOL .
                    'Категория: ' .
                    $user->category .
                    PHP_EOL .
                    'Город: ' .
                    $relevant_user->city .
                    PHP_EOL .
                    PHP_EOL .
                    'О себе: ' .
                    $relevant_user->about,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '❤️',
                                'callback_data' => 'feedback-1-' . $first_username . '-' . $second_username
                            ],
                            [
                                'text' => '➡️',
                                'callback_data' => 'feedback-0-' . $first_username . '-' . $second_username
                            ]
                        ],
                        [
                            [
                                'text' => 'Симпатии',
                                'callback_data' => 'is-users-active' . '-' . 1
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }

    protected function showLikesButton()
    {
        $main_message_id = $this->user_data->get('main_message_id');

        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $method_name = empty($main_message_id)
            ? 'sendMessage'
            : 'editMessageText';

        $response = $this->telegram_request_service
            ->setMethodName($method_name)
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $callback_query->message->message_id,
                'text' => 'Пока что для вас нет новых пользователей',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Симпатии',
                                'callback_data' => 'is-users-active' . '-' . 1
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html'
            ])
            ->make();

        if ($response->ok and $method_name == 'sendMessage') {
            $this->user_data->set('main_message_id', $response->result->message_id);
        }
    }

    protected function revealUser()
    {
        $user = $this->user_data->get('user');
        $current_user = $this->user_data->get('current_user');

        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $this->telegram_request_service
            ->setMethodName('editMessageText')
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $callback_query->message->message_id,
                'text' => 'Ник в telegram: ' .
                    '@' .
                    $current_user->username .
                    PHP_EOL .
                    'Имя: ' .
                    $current_user->name .
                    PHP_EOL .
                    'Предмет: ' .
                    $user->subject .
                    PHP_EOL .
                    'Категория: ' .
                    $user->category .
                    PHP_EOL .
                    'Город: ' .
                    $current_user->city .
                    PHP_EOL .
                    PHP_EOL .
                    'О себе: ' .
                    $current_user->about,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Слелующий',
                                'callback_data' => 'feedback-1' . '-' . $current_user->username . '-' . $user->username
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();

        $current_user->is_revealed = true;
        $this->user_data->set('current_user', $current_user);
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
                    'Город: ' .
                    $liked_user->city .
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
        $liked_users = $this->user_data->get('liked_users');

        $callback_query = $this->data->getCallbackQuery();
        if (collect($liked_users)->isEmpty()) {
            $liked_users = $user->likedUsers()->get();
            $this->user_data->set('liked_users', $liked_users);
        }

        if ($liked_users->isEmpty()) {
            $this->respondWithPopup($callback_query->id, 'Пока нет симпатий в рамках текущих категории и предмета');

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

    protected function deleteNotificationMessageIfExists()
    {
        $notification_message_id = $this->user_data->get('notification_message_id');
        if ($notification_message_id) {
            $this->deleteMessage($notification_message_id);

            $this->user_data->set('notification_message_id', null);
        }
    }
}