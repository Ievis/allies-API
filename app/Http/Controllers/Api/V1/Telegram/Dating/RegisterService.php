<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class RegisterService extends CommandController
{
    private array $user_data;

    public function setUserData(array $user_data)
    {
        $this->user_data = $user_data;
    }

    public function proceed()
    {
        foreach ($this->user_data as $field_name => $field_data) {
            if ($field_data['is_completed']) continue;

            if ($field_data['is_pending']) {
                $username = $this->data->getUsername();
                $text = $this->data->getMessage()->text;

                $this->user_data[$field_name]['pending'] = false;
                $this->user_data[$field_name]['is_completed'] = true;
                $this->user_data[$field_name]['value'] = $text;

                Cache::set($username, $this->user_data);
            }

            $this->{$field_data['method']}();
            break;
        }
    }

    public function name()
    {
        $username = $this->data->getUsername();
        $this->user_data['name']['is_pending'] = true;

        Cache::set($username, $this->user_data);
        $this->respondWithMessage('Введите имя');
    }

    public function subject()
    {
        $username = $this->data->getUsername();
        $this->user_data['subject']['is_pending'] = true;

        Cache::set($username, $this->user_data);
        $this->respondWithMessage('Введите предмет');
    }

    public function category()
    {
        $username = $this->data->getUsername();
        $this->user_data['category']['is_pending'] = true;

        Cache::set($username, $this->user_data);

        $this->respondWithMessage('Введите категорию');
    }
}