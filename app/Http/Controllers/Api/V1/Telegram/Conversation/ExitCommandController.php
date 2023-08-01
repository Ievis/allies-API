<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

class ExitCommandController extends CommandController
{
    public function __invoke()
    {
        $message = $this->data->getMessage();
        $chat_id = $this->data->getChatId();

        $user_active_conversation = $this->getActiveConversation();

        if (empty($user_active_conversation)) {
            $this->respondWithMessage('<strong>Вы не пока не участвуете ни в каком обсуждении!</strong>');

            return;
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => 'Вы уверены, что хотите закончить обсуждение?',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[
                        [
                            'text' => 'Да',
                            'callback_data' => 'exit-' . 1 . '-' . $message->message_id,
                        ],
                        [
                            'text' => 'Нет',
                            'callback_data' => 'exit-' . 0 . '-' . $message->message_id,
                        ],
                    ]]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }
}