<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class UsersPaginationController extends CommandController
{
    public function __invoke()
    {
        $page = $this->callback_query_args['page'] ?? 1;
        $username = $this->data->getUsername();
        $user = Cache::get($username . ':' . 'user-data');
        $liked_users = $this->getLikedUsersIfExist($user);
        if (empty($liked_users)) die();
        $enumerated_buttons = $this->getLikedUsersEnumeratedButtons($liked_users, $page);
        $pagination_buttons = $this->getLikedUsersPaginationButtons($liked_users, $page);

        $per_page = 9;
        $liked_users = $liked_users->forPage($page, $per_page)->values();
        $liked_user = $liked_users->first();
        $this->displayLikedUserWithPagination($liked_user, $enumerated_buttons, $pagination_buttons);
    }
}