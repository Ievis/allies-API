<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UsersShowController extends CommandController
{
    public function __invoke()
    {
        $username = $this->data->getUsername();
        $user = Cache::get($username . ':' . 'user-data');
        $liked_user_id = $this->callback_query_args['user_id'];
        $page = $this->callback_query_args['page'];
        $liked_users = $this->getLikedUsersIfExist($user);

        $liked_user = $liked_users->first(function ($user) use ($liked_user_id) {
            return $user->id == $liked_user_id;
        });

        $enumerated_buttons = $this->getLikedUsersEnumeratedButtons($liked_users, $page);
        $pagination_buttons = $this->getLikedUsersPaginationButtons($liked_users, $page);
        $this->displayLikedUserWithPagination($liked_user, $enumerated_buttons, $pagination_buttons);
    }
}