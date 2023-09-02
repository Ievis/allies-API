<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class RegisterData
{
    public string $username;
    public array $fields;
    public null|int $summary_message_id;
    public null|int $reset_bot_message_id;

    public function __construct($username, array $register_data = [])
    {
        $register_data = $register_data
            ? $register_data
            : Cache::get($username . ':' . 'register-data');
        $register_data = collect($register_data);

        $this->username = $username;
        $this->fields = $register_data->get('fields');
        $this->summary_message_id = $register_data->get('summary_message_id');
        $this->reset_bot_message_id = $register_data->get('reset_bot_message_id');
    }

    public function get(string $key): mixed
    {
        return $this->{$key};
    }

    public function set(string $key, mixed $value): bool
    {
        $this->{$key} = $value;

        return true;
    }

    public function delete(string $key): bool
    {
        $this->{$key} = null;

        return true;
    }

    public function save(): bool
    {
        $register_data = collect([
            'fields' => $this->fields,
            'summary_message_id' => $this->summary_message_id,
            'reset_bot_message_id' => $this->reset_bot_message_id
        ]);
        Cache::set($this->username . ':' . 'register-data', $register_data, 60 * 60);

        return true;
    }

    public function exists(): bool
    {
        return isset($this->fields);
    }

    public function flush(): bool
    {
        return Cache::forget($this->username . ':' . 'register-data');
    }
}