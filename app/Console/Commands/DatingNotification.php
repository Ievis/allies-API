<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\Telegram\Dating\CommandController;
use App\Http\Controllers\Api\V1\Telegram\Dating\UserData;
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
    private TelegramRequestService $telegram_service;

    /**
     * Execute the console command.
     */

    public function __construct()
    {
        parent::__construct();
        $this->telegram_service = new TelegramRequestService(env('TELEGRAM_DATING_BOT_API_TOKEN'));
    }

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
                $this->telegram_service
                    ->setMethodName('deleteMessage')
                    ->setParams([
                        'chat_id' => $waiting_user->chat_id,
                        'message_id' => $waiting_user->main_message_id,
                        'parse_mode' => 'html',
                    ])
                    ->make();
                $waiting_user->main_message_id = null;
                $waiting_user->is_waiting = false;
                $waiting_user->save();

                $this->telegram_service
                    ->setMethodName('sendMessage')
                    ->setParams([
                        'chat_id' => $waiting_user->chat_id,
                        'text' => '<strong>Вы бездействовали в течение долгого времени.</strong>' .
                            PHP_EOL .
                            '<strong>Пройдите процедуру регистрации ещё раз.</strong>' .
                            PHP_EOL .
                            '<strong>Не бойтесь, ваши симпатии сохранились!</strong>',
                        'parse_mode' => 'html',
                    ])
                    ->make();
                continue;
            }
            $user_data = new UserData($username);

            $relevant_users = $waiting_user->relevantUsersWithFeedbacks();
            if ($relevant_users->isEmpty()) {
                $this->affectDelayIdCache($waiting_user, $delayed_ids);

                continue;
            }
            $relevant_user = $relevant_users->shift();
            $is_notified = $this->notify($relevant_user, $waiting_user, $user_data);
            $this->affectDelayIdCache($waiting_user, $delayed_ids);

            if ($is_notified) {
                $user_data->set('relevant_users', $relevant_users);
                $user_data->set('current_user', $relevant_user);
                $waiting_user->is_waiting = false;
                $waiting_user->save();
                $user_data->save();
            }
        }
    }

    private function notify($relevant_user, $waiting_user, UserData $user_data)
    {
        $user_ids = $this->matchUsernames($relevant_user, $waiting_user);
        $prefix = $this->matchPrefix($relevant_user);
        $first_username = $user_ids['first_username'];
        $second_username = $user_ids['second_username'];
        $main_message_id = $user_data->get('main_message_id');
        $chat_id = $waiting_user->chat_id;

        $method_name = empty($main_message_id)
            ? 'sendMessage'
            : 'editMessageText';

        $response = $this->telegram_service
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
                    'inline_keyboard' => $this->getInlineContent($first_username, $second_username)
                ]),
                'parse_mode' => 'html',
            ])
            ->make();

        $response = $this->telegram_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => '<strong>Мы подобрали для вас новых людей!</strong>',
                'parse_mode' => 'html',
            ])
            ->make();
        if ($response->ok) {
            $user_data->set('notification_message_id', $response->result->message_id);

            return true;
        }

        return false;
    }

    private function matchUsernames($relevant_user, $waiting_user)
    {
        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks') ?? collect();
        $first_username = $feedbacks->isEmpty()
            ? $waiting_user->username
            : $relevant_user->username;
        $second_username = $first_username === $waiting_user->username
            ? $relevant_user->username
            : $waiting_user->username;

        return [
            'first_username' => $first_username,
            'second_username' => $second_username
        ];
    }

    private function matchPrefix($relevant_user)
    {
        $feedbacks = $relevant_user->getRelation('firstUserFeedbacks') ?? collect();

        return $feedbacks->isEmpty()
            ? ''
            : '<strong>Вас лайкнули!</strong>' . PHP_EOL;
    }

    private function getInlineContent($first_username, $second_username)
    {
        return [
            [
                [
                    'text' => 'Показать',
                    'callback_data' => 'feedback-1-' . $first_username . '-' . $second_username . '-' . 0
                ],
                [
                    'text' => 'Следующий',
                    'callback_data' => 'feedback-0-' . $first_username . '-' . $second_username . '-' . 0
                ]
            ],
            [
                [
                    'text' => 'Симпатии',
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
