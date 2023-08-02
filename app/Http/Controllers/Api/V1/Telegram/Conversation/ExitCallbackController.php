<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Services\UserService;

class ExitCallbackController extends CommandController
{
    public function __invoke()
    {
        $callback_query = $this->data->getCallbackQuery();

        $user = $this->getUser();
        $user_active_conversation = $this->getActiveConversation();

        if (empty($user_active_conversation)) {
            $this->respondWithMessage('<strong>Вы не пока не участвуете ни в каком обсуждении!</strong>');

            return;
        }

        $decision = $this->callback_query_args['decision'];
        $message_id = $this->callback_query_args['message_id'];
        if ($decision == 0) {
            $this->deleteMessage($callback_query->message->message_id);
            $this->deleteMessage($message_id);

            return;
        }

        $user_active_conversation->update([
            'is_resolved' => true,
            'is_active' => false,
        ]);

        $student_telegram = $user_active_conversation->studentTelegram()->first();
        $curator_telegram = $user_active_conversation->curatorTelegram()->first();

        $this->deleteMessage($callback_query->message->message_id);
        $this->deleteMessage($message_id);

        if (UserService::isNotStudent($user)) {
            $this->respondWithMessage('Куратор завершил обсуждение.' . PHP_EOL . 'Можете начинать другое.', $student_telegram->chat_id);
        } else {
            $this->respondWithMessage('Ученик завершил обсуждение.' . PHP_EOL . 'Можете начинать другое.', $curator_telegram->chat_id);
        }

        $this->respondWithMessage('Вы завершили обсуждение.' . PHP_EOL . 'Можете начинать другое.');
    }
}