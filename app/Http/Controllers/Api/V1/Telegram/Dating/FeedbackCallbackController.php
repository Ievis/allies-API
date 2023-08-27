<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;

class FeedbackCallbackController extends CommandController
{
    public function __invoke()
    {
        $this->deleteNotificationMessageIfExists();
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $decision = $this->callback_query_args['decision'];
        $first_user_id = $this->callback_query_args['first_user_id'];
        $second_user_id = $this->callback_query_args['second_user_id'];

        $users = TelegramDatingUser::whereIn('id', [$first_user_id, $second_user_id])->get();
        $users = $users
            ->mapWithKeys(function ($user) use ($first_user_id) {
                return $first_user_id == $user->id
                    ? ['first_user' => $user]
                    : ['second_user' => $user];
            });
        $first_user = $users->get('first_user');
        $second_user = $users->get('second_user');

        $feedback = TelegramDatingFeedback::query()
            ->where('first_user_id', $first_user_id)
            ->where('second_user_id', $second_user_id)
            ->where('subject', $first_user->subject)
            ->where('category', $first_user->category)
            ->first();

        $user = empty($feedback)
            ? $first_user
            : $second_user;

        if (empty($feedback)) {
            TelegramDatingFeedback::create([
                'first_user_id' => $first_user_id,
                'second_user_id' => $second_user_id,
                'first_user_reaction' => $decision,
                'subject' => $user->subject,
                'category' => $user->category,
                'is_resolved' => !$decision
            ]);
            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);
            $this->nextUserIfExists($user, $relevant_user);

            return;
        }
        $user = Cache::get($this->data->getUsername() . ':' . 'user-data');
        if ($feedback->first_user_id == $user->id) {
            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);
            $this->nextUserIfExists($user, $relevant_user);

            return;
        }

        if ($feedback->is_resolved) {
            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);
            $this->nextUserIfExists($user, $relevant_user);

            return;
        }

        $feedback->update([
            'second_user_reaction' => $decision,
            'is_resolved' => true
        ]);

        if ($decision) {
            $this->affectLikedUsersCache($first_user, $second_user);

            $this->telegram_request_service
                ->setMethodName('editMessageText')
                ->setParams([
                    'chat_id' => $chat_id,
                    'message_id' => $callback_query->message->message_id,
                    'text' => 'Ник в telegram: ' .
                        '<strong>' . '@' . $first_user->username . '</strong>' .
                        PHP_EOL
                        . 'Имя: ' .
                        $first_user->name .
                        PHP_EOL .
                        'Предмет: ' .
                        $first_user->subject .
                        PHP_EOL .
                        'Категория: ' .
                        $first_user->category .
                        PHP_EOL .
                        PHP_EOL .
                        'О себе: ' .
                        $first_user->about,
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => 'Следующий',
                                    'callback_data' => 'feedback-1-' . $first_user_id . '-' . $second_user_id
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
        $relevant_user = $this->getRelevantUser($user, $relevant_users);
        $this->nextUserIfExists($user, $relevant_user);
    }

    private function affectLikedUsersCache($first_user, $second_user)
    {
        $first_liked_users = Cache::get($first_user->username . ':' . 'liked-users');
        $second_liked_users = Cache::get($second_user->username . ':' . 'liked-users');

        if ($first_liked_users) {
            $first_liked_users->push($second_user);
            Cache::set($first_user->username . ':' . 'liked-users', $first_liked_users, 3600);
        }
        if ($second_liked_users) {
            $second_liked_users->push($first_user);
            Cache::set($second_user->username . ':' . 'liked-users', $second_liked_users, 3600);
        }
    }

    private function deleteNotificationMessageIfExists()
    {
        $notification_message_id = Cache::get($this->data->getUsername() . ':' . 'notification-message-id');
        if($notification_message_id) {
            $this->deleteMessage($notification_message_id);
        }
    }
}