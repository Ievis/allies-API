<?php

namespace App\Http\Controllers\Api\V1\Telegram;

use stdClass;

class TelegramUserData
{
    protected int $chat_id;
    protected string $username;
    protected ?stdClass $message;
    protected ?stdClass $callback_query;

    public function __construct(int $chat_id, string $username, ?stdClass $message, ?stdClass $callback_query)
    {
        $this->chat_id = $chat_id;
        $this->username = $username;
        $this->message = $message;
        $this->callback_query = $callback_query;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chat_id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return stdClass|null
     */
    public function getMessage(): ?stdClass
    {
        return $this->message;
    }

    /**
     * @return stdClass|null
     */
    public function getCallbackQuery(): ?stdClass
    {
        return $this->callback_query;
    }
}