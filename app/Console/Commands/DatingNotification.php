<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\Telegram\Dating\CommandController;
use App\Models\TelegramDatingUser;
use App\Services\TelegramRequestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DatingNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dating-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $delayed_ids = (array)Cache::tags(['cron-delay'])->get('id');
        $waiting_users = $this->getWaitingUsers($delayed_ids);

        foreach ($waiting_users as $waiting_user) {
            $username = $waiting_user->username;
            if (Cache::has($username . ':' . 'register-data')) {

                continue;
            }
            if (!Cache::has($username . ':' . 'user-data')) {

                continue;
            }

            $relevant_users = $waiting_user->relevantUsersWithFeedbacks()->get();
            if ($relevant_users->isEmpty()) {
                $this->affectDelayIdCache($waiting_user, $delayed_ids);

                continue;
            }
            $relevant_user = $relevant_users->shift();
            Cache::set($username . ':' . 'relevant-users', $relevant_users);

            $this->notify($relevant_user, $waiting_user);
            $this->affectDelayIdCache($waiting_user, $delayed_ids);

            $waiting_user->is_waiting = false;
            $waiting_user->save();
            var_dump($delayed_ids);
        }
    }

    private function notify($relevant_user, $waiting_user)
    {
        $username = $waiting_user->username;
        $user_ids = $this->matchUserIds($relevant_user, $waiting_user);
        $prefix = $this->matchPrefix($relevant_user);
        $first_user_id = $user_ids['first_user_id'];
        $second_user_id = $user_ids['second_user_id'];
        $main_message_id = Cache::get($username . ':' . 'main-message-id');
        $chat_id = $waiting_user->chat_id;

        $method_name = empty($main_message_id)
            ? 'sendMessage'
            : 'editMessageText';
        $telegram_request_service = new TelegramRequestService(env('TELEGRAM_DATING_BOT_API_TOKEN'));

        $response = $telegram_request_service
            ->setMethodName($method_name)
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $main_message_id,
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
                    'inline_keyboard' => $this->getInlineContent($first_user_id, $second_user_id)
                ]),
                'parse_mode' => 'html',
            ])
            ->make();

        $response = $telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => '<strong>Мы подобрали для вас новых людей!</strong>',
                'parse_mode' => 'html',
            ])
            ->make();

        Cache::set($username . ':' . 'notification-message-id', $response->result->message_id);
    }

    private function matchUserIds($relevant_user, $waiting_user)
    {
        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks');
        $first_user_id = $feedbacks->isEmpty()
            ? $waiting_user->id
            : $relevant_user->id;
        $second_user_id = $first_user_id === $waiting_user->id
            ? $relevant_user->id
            : $waiting_user->id;

        return [
            'first_user_id' => $first_user_id,
            'second_user_id' => $second_user_id
        ];
    }

    private function matchPrefix($relevant_user)
    {
        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks');
        return $feedbacks->isEmpty()
            ? ''
            : '<strong>Вас лайкнули!</strong>' . PHP_EOL;
    }

    private function getInlineContent($first_user_id, $second_user_id)
    {
        return [
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
        ];
    }

    private function getWaitingUsers($delayed_ids)
    {
        $waiting_users_query = TelegramDatingUser::query()
            ->where('is_waiting', true);

        return empty($delayed_ids)
            ? $waiting_users_query->get()
            : $waiting_users_query->whereNotIn('id', $delayed_ids)->get();
    }

    private function affectDelayIdCache($waiting_user, &$delayed_ids, $ttl = 60)
    {
        $delayed_ids[] = $waiting_user->id;
        Cache::tags(['cron-delay'])->put('id', $delayed_ids, $ttl);
    }
}
