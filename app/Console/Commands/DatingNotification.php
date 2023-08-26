<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\Telegram\Dating\CommandController;
use App\Models\TelegramDatingUser;
use App\Services\TelegramRequestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
        $delayed_ids = Cache::tags(['cron-delay'])->get('id');
        $waiting_users_query = TelegramDatingUser::query()
            ->where('is_waiting', true);
        $waiting_users = empty($delayed_ids)
            ? $waiting_users_query->get()
            : $waiting_users_query->whereNotIn('id', $delayed_ids)->get();

        foreach ($waiting_users as $waiting_user) {
            $delayed_ids = Cache::tags(['cron-delay'])->get('id');

            $relevant_users = $waiting_user->relevantUsersWithFeedbacks()->get();
            if ($relevant_users->isEmpty()) {
                $this->affectDelayIdCache($waiting_user, $delayed_ids);

                continue;
            }
            var_dump($waiting_user->name);
            $relevant_user = $relevant_users->shift();
            $feedbacks = $relevant_user->getRelation('firstUserFeedbacks');

            Cache::set($waiting_user->username . ':' . 'relevant-users', $relevant_users);
            $main_message_id = Cache::get($waiting_user->username . ':' . 'main-message-id');

            $first_user_id = $feedbacks->isEmpty()
                ? $waiting_user->id
                : $relevant_user->id;
            $second_user_id = $first_user_id === $waiting_user->id
                ? $relevant_user->id
                : $waiting_user->id;
            $prefix = $feedbacks->isEmpty()
                ? ''
                : '<strong>Вас лайкнули!</strong>' . PHP_EOL;

            $chat_id = $waiting_user->chat_id;

            $telegram_request_service = new TelegramRequestService(env('TELEGRAM_DATING_BOT_API_TOKEN'));
            $response = $telegram_request_service
                ->setMethodName('editMessageText')
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

            if (empty($response->ok)) {
                $this->affectDelayIdCache($waiting_user, $delayed_ids, 3600);

                continue;
            }

            $this->affectDelayIdCache($waiting_user, $delayed_ids, 600);
        }
    }

    private function affectDelayIdCache($waiting_user, $delayed_ids, $ttl = 300)
    {
        if (empty($delayed_ids)) {
            Cache::tags(['cron-delay'])->put('id', [$waiting_user->id], $ttl);

            return;
        }

        $delayed_ids[] = $waiting_user->id;
        Cache::tags(['cron-delay'])->put('id', $delayed_ids, $ttl);
    }
}
