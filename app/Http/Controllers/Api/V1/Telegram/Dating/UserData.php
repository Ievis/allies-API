<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UserData
{
    public Collection $liked_users;
    public Collection $relevant_users;

    public null|TelegramDatingUser $user;
    public null|TelegramDatingUser $current_user;
    public null|string $main_message_id;
    public null|string $notification_message_id;

    public function __construct(string $username, array $user_data = [])
    {
        $user_data = $user_data
            ? $user_data
            : Cache::get($username . ':' . 'user-data');
        $user_data = collect($user_data);

        $this->liked_users = $user_data->get('liked_users', collect());
        $this->relevant_users = $user_data->get('relevant_users', collect());
        $this->user = $user_data->get('user', null);
        $this->current_user = $user_data->get('current_user', null);
        $this->main_message_id = $user_data->get('main_message_id', null);
        $this->notification_message_id = $user_data->get('notification_message_id', null);
    }

    public function save(): bool
    {
        Cache::set($this->user->username . ':' . 'user-data', collect([
            'user' => $this->user,
            'liked_users' => $this->liked_users,
            'relevant_users' => $this->relevant_users,
            'current_user' => $this->current_user,
            'main_message_id' => $this->main_message_id,
            'notification_message_id' => $this->notification_message_id
        ]));

        return true;
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
        $this->{$key} = $this->{$key} instanceof Collection
            ? collect()
            : null;

        return true;
    }

    public function exists(string $key): bool
    {
        return collect($this->{$key})->isNotEmpty();
    }
}