<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Log;

class ResetCallbackController extends CommandController
{
    public function __invoke()
    {
        $action = $this->input('action');
        $message_id = $this->input('message_id');
        $callback_query = $this->data->getCallbackQuery();
        $username = $this->data->getUsername();
        $this->deleteMessage($callback_query->message->message_id);
        $this->deleteMessage($message_id);

        if ($action == 'cancel') {
            $this->deleteMessage($callback_query->message->message_id);
            $this->deleteMessage($message_id);

            return;
        }
        if ($action == 'restart') {
            $fields = [
                'name' => [
                    'is_completed' => false,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => null,
                    'method' => 'name'
                ],
                'subject' => [
                    'is_completed' => false,
                    'is_pending' => false,
                    'type' => 'callback',
                    'value' => null,
                    'method' => 'subject'
                ],
                'category' => [
                    'is_completed' => false,
                    'is_pending' => false,
                    'type' => 'callback',
                    'value' => null,
                    'method' => 'category'
                ],
                'city' => [
                    'is_completed' => false,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => null,
                    'method' => 'city'
                ],
                'about' => [
                    'is_completed' => false,
                    'is_pending' => false,
                    'type' => 'text',
                    'value' => null,
                    'method' => 'about'
                ],
            ];

            $register_service = new RegisterService();
            $register_service->setTelegramUserData($this->data);
            $register_service->setCallbackArgs($this->callback_query_args);

            $register_data = new RegisterData($username, [
                'fields' => $fields
            ]);

            $register_data->save();
            $register_service->setRegisterData($register_data);
            $register_service->proceed();
        }
        if ($action == 'update') {
            return;
        }
    }
}