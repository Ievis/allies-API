<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UserData
{
    public TelegramDatingUser $user;
    public Collection $liked_users;
    public Collection $relevant_users;
    public null|TelegramDatingUser $current_user;
    public null|string $main_message_id;
    public null|string $notification_message_id;

    public function __construct(string $username)
    {
        $user_data = Cache::get($username . ':' . 'user-data');

        $this->user = $user_data->get('user');
        $this->liked_users = $user_data->get('liked_users');
        $this->relevant_users = $user_data->get('relevant_users');
        $this->current_user = $user_data->get('current_user');
        $this->main_message_id = $user_data->get('main_message_id');
        $this->notification_message_id = $user_data->get('notification_message_id');
    }

    public function save()
    {
        Cache::set($this->user->username . ':' . 'user-data', collect([
            'user' => $this->user,
            'liked_users' => $this->liked_users,
            'relevant_users' => $this->relevant_users,
            'current_user' => $this->current_user,
            'main_message_id' => $this->main_message_id,
            'notification_message_id' => $this->notification_message_id
        ]));
    }
}