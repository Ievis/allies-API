<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Services\UserService;

class StartCommandController extends CommandController
{
    public function __invoke()
    {
        $chat_id = $this->data->getChatId();
        $telegram_user = $this->getTelegramUserOrDie();
        $user = $telegram_user->user()->first();

        if (empty($telegram_user->chat_id)) {
            $telegram_user->chat_id = $chat_id;

            $telegram_user->save();
        }

        $message = 'Вы успешно авторизованы! Можете отправить запрос на обсуждение на нашем сайте, после чего с вами свяжется наш куратор.';
        $url = [['text' => 'Выбрать урок для обсуждения на сайте', 'url' => 'https://google.com']];

        if (UserService::isNotStudent($user)) {
            $message = 'Вы успешно авторизованы! Можете приступать к общению с учениками. Чтобы посмотреть очередь заявок от учеников, введите команду /queue.';
            $url = [];
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [$url]
                ])
            ])
            ->make();
    }
}