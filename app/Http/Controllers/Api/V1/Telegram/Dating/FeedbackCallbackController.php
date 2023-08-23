<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;

class FeedbackCallbackController extends CommandController
{
    public function __invoke()
    {
        $chat_id = $this->data->getChatId();
        $callback_query = $this->data->getCallbackQuery();
        $decision = $this->callback_query_args['decision'];
        $is_endless = $this->callback_query_args['is_endless'];
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

            if (!$this->nextUserIfExists($user, $relevant_user)) die();

            return;
        }
        $user = Cache::get($this->data->getUsername() . ':' . 'user-data');
        $user_id = $user->id;
        if ($feedback->first_user_id == $user_id) {
            $user = TelegramDatingUser::find($user_id);

            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);
            if (!$this->nextUserIfExists($user, $relevant_user)) die();

            return;
        }

        if ($feedback->is_resolved) {
            if (!$is_endless) return;

            $relevant_users = $this->getRelevantUsers($user);
            $relevant_user = $this->getRelevantUser($user, $relevant_users);
            if (!$this->nextUserIfExists($user, $relevant_user)) die();

            return;
        }

        $feedback->update([
            'second_user_reaction' => $decision,
            'is_resolved' => true
        ]);

        if ($decision) {
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
                                    'callback_data' => 'feedback-1-' . $first_user_id . '-' . $second_user_id . '-' . 1
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

        if (!$this->nextUserIfExists($user, $relevant_user)) die();
    }
}