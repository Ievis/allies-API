<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;

class UsersPaginationController extends CommandController
{
    public function __invoke(?UserData $user_data = null)
    {
//        die();
        $this->setUserData($user_data ?? []);
        $user = $this->user_data->get('user');
        $page = $this->input('page') ?? 1;

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