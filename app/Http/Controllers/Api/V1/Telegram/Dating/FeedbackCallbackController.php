<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeedbackCallbackController extends CommandController
{
    public function __invoke()
    {
//        die();
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
            ->first();

        $user = empty($feedback)
            ? $first_user
            : $second_user;

        if ($decision and !empty($feedback)) {
            $this->telegram_request_service
                ->setMethodName('editMessageText')
                ->setParams([
                    'chat_id' => $chat_id,
                    'message_id' => $callback_query->message->message_id,
                    'text' => 'Ник в telegram: ' .
                        '<strong>' . $first_user->username . '</strong>' .
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
                                    'callback_data' => 'feedback-0-' . $first_user_id . '-' . $second_user_id
                                ]
                            ]
                        ]
                    ]),
                    'parse_mode' => 'html',
                ])
                ->make();
            $feedback->update([
                'second_user_reaction' => $decision,
                'is_resolved' => true
            ]);

            return;
        }

        if (empty($feedback)) {
            TelegramDatingFeedback::create([
                'first_user_id' => $first_user_id,
                'second_user_id' => $second_user_id,
                'first_user_reaction' => $decision,
                'is_resolved' => !$decision
            ]);

            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);

            if (empty($relevant_user)) {
                $this->respondWithMessage(
                    '<strong>Пока что нет подходящих людей.</strong>' .
                    PHP_EOL .
                    'Как только найдутся люди с такими же интересами, мы вам сразу сообщим.'
                );

                return;
            }

            $this->nextUser($user, $relevant_user);

            return;
        }
        $feedback->update([
            'second_user_reaction' => $decision,
            'is_resolved' => true
        ]);

        $relevant_users = $this->getRelevantUsers($user);
        $relevant_user = $this->getRelevantUser($user, $relevant_users);

        if (empty($relevant_user) and !$decision) {
            $this->respondWithMessage(
                '<strong>Пока что нет подходящих людей.</strong>' .
                PHP_EOL .
                'Как только найдутся люди с такими же интересами, мы вам сразу сообщим.'
            );

            return;
        }

        $this->nextUser($user, $relevant_user);
    }

    private function getRelevantUsers($user)
    {
        $relevant_users = Cache::get($user->username . ':' . 'relevant-users');
        if (collect($relevant_users)->isEmpty()) {
            $relevant_users = $user->relevantUsersWithFeedbacks()->get();
        }

        return $relevant_users;
    }

    private function getRelevantUser($user, $relevant_users)
    {
        $relevant_user = $relevant_users->shift();
        Cache::set($user->username . ':' . 'relevant-users', $relevant_users);

        return $relevant_user;
    }
}