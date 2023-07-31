<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Models\TelegramConversation;
use Illuminate\Support\Facades\Log;

class ChargeCallbackController extends CommandController
{
    public function __invoke()
    {
        $callback_query = $this->data->getCallbackQuery();

        Log::info(print_r($this->callback_query_args, true));


        $message_id = $this->callback_query_args['message_id'];


        $this->deleteMessage($message_id);
        $this->deleteMessage($callback_query->message->message_id);
        $telegram_conversation_id = $this->callback_query_args['telegram_conversation_id'];
        $telegram_conversation = TelegramConversation::find($telegram_conversation_id);

        if ($telegram_conversation->is_active) {
            return;
        }

        $telegram_user = $this->getTelegramUserOrDie();
        $telegram_student = $telegram_conversation->studentTelegram()->first();

        $telegram_conversation->update([
            'curator_id' => $telegram_user->user_id,
            'is_started' => true,
            'is_active' => true
        ]);

        $student_message = 'Здравствуйте! Куратор начал обсуждение по уроку.' . PHP_EOL . '<strong>Задавайте свои вопросы.</strong>';
        $curator_message = 'Вы начали обсуждение.' . PHP_EOL . '<strong>Обсуждение активно!</strong>';

        $this->respondWithMessage($student_message, $telegram_student->chat_id);
        $this->respondWithMessage($curator_message, $telegram_user->chat_id);
    }
}