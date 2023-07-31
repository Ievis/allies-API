<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Models\TelegramConversation;
use App\Models\TelegramUser;

class CommandController extends TelegramController
{
    protected function getActiveConversation()
    {
        $telegram_user = $this->getTelegramUserOrDie();

        return TelegramConversation::where('student_id', $telegram_user->user_id)
            ->where('is_active', true)
            ->orWhere('curator_id', $telegram_user->user_id)
            ->where('is_active', true)
            ->first();
    }

    protected function getTelegramUserOrDie()
    {
        $username = $this->data->getUsername();
        $chat_id = $this->data->getChatId();

        $telegram_user = TelegramUser::where('username', $username)->first();

        if (empty($telegram_user)) {
            $this->telegram_request_service
                ->setMethodName('sendMessage')
                ->setParams([
                    'chat_id' => $chat_id,
                    'text' => 'Ваш ник не прикреплён к аккаунту!' . PHP_EOL . '<strong>Укажите свой ник в личном кабинете.</strong>',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => 'Войти', 'url' => 'https://google.com']
                            ]
                        ]
                    ]),
                    'parse_mode' => 'html',
                ])
                ->make();

            die();
        }

        if (empty($telegram_user->chat_id)) {
            $telegram_user->update([
                'chat_id' => $chat_id
            ]);
        }

        return $telegram_user;
    }

    protected function getUser()
    {
        $telegram_user = $this->getTelegramUserOrDie();

        return $telegram_user->user()->first();
    }

    protected function respondWithMessage(string $message, ?int $chat_id = null)
    {
        $chat_id = $chat_id ?? $this->data->getChatId();

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'html',
            ])
            ->make();
    }

    protected function deleteMessage(?int $message_id = null, ?int $chat_id = null)
    {
        $chat_id = $chat_id ?? $this->data->getChatId();

        $this->telegram_request_service
            ->setMethodName('deleteMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'parse_mode' => 'html',
            ])
            ->make();
    }
}