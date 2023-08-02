<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Services\UserService;

class ErrorCommandController extends CommandController
{
    public function __invoke()
    {
        $message = $this->data->getMessage();
        $user = $this->getUser();
        $user_active_conversation = $this->getActiveConversation();

        if ($user_active_conversation) {
            $telegram_student = $user_active_conversation->studentTelegram()->first();
            $telegram_curator = $user_active_conversation->curatorTelegram()->first();

            if (UserService::isNotStudent($user)) {
                $this->respondWithMessage($message->text, $telegram_student->chat_id);

                return;
            }

            $this->respondWithMessage($message->text, $telegram_curator->chat_id);

            return;
        }

        $this->respondWithMessage('Вы пока не участвуете в обсуждении. Ваше сообщение никто не видит.');
    }
}