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
        $decision = $this->callback_query_args['decision'];
        $first_user_id = $this->callback_query_args['first_user_id'];
        $second_user_id = $this->callback_query_args['second_user_id'];

        $users = TelegramDatingUser::whereIn('id', [$first_user_id, $second_user_id])->get();

        $users = $users
            ->mapWithKeys(function ($item) use ($first_user_id) {
                return $first_user_id == $item->id
                    ? ['first_user' => $item]
                    : ['second_user' => $item];
            });
        $first_user = $users->get('first_user');
        $second_user = $users->get('second_user');

        $feedback = TelegramDatingFeedback::query()
            ->where('first_user_id', $first_user_id)
            ->where('second_user_id', $second_user_id)
            ->first();

        if (empty($feedback)) {
            TelegramDatingFeedback::create([
                'first_user_id' => $first_user_id,
                'second_user_id' => $second_user_id,
                'first_user_reaction' => $decision
            ]);

            if (empty($decision)) {
                $relevant_users = Cache::get($first_user->username . ':' . 'relevant-users');
                if($relevant_users->isEmpty()) {
                    $relevant_users = $first_user
                        ->relevantUsers()
                        ->whereDoesntHave('feedbacks', function ($query) use ($first_user) {
                            return $query->where('first_user_id', $first_user->id)
                                ->orWhere('is_resolved', true);
                        })
                        ->limit(5)
                        ->get();
                }
                $relevant_user = $relevant_users->shift();
                if(empty($relevant_user)) {
                    $this->respondWithMessage(
                        '<strong>Пока что нет подходящих людей.</strong>' .
                        PHP_EOL .
                        'Как только найдутся люди с такими же интересами, мы вам сразу сообщим.'
                    );

                    return;
                }
                Cache::set($first_user->username . ':' . 'relevant-users', $relevant_users);

                Log::info(print_r($relevant_user, true));

                $chat_id = $this->data->getChatId();
                $callback_query = $this->data->getCallbackQuery();
                $this->telegram_request_service
                    ->setMethodName('editMessageText')
                    ->setParams([
                        'chat_id' => $chat_id,
                        'message_id' => $callback_query->message->message_id,
                        'text' => 'Имя: ' .
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
                                        'callback_data' => 'feedback-1-' . $first_user_id . '-' . $relevant_user->id
                                    ],
                                    [
                                        'text' => 'Следующий',
                                        'callback_data' => 'feedback-0-' . $first_user_id . '-' . $relevant_user->id
                                    ]
                                ]
                            ]
                        ]),
                        'parse_mode' => 'html',
                    ])
                    ->make();
            }
        } else {
            $feedback->update([
                'second_user_reaction' => $decision
            ]);
        }
    }
}