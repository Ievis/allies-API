<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DisplayUsersController extends CommandController
{
    public function __invoke()
    {
        $this->deleteNotificationMessageIfExists();
        $username = $this->data->getUsername();
        $decision = $this->callback_query_args['decision'];
        $user = Cache::get($username . ':' . 'user-data');
        if ($decision) {
            $users_pagination_controller = new UsersPaginationController();
            $users_pagination_controller->setTelegramUserData($this->data);
            $users_pagination_controller->setCallbackArgs($this->callback_query_args);

            $users_pagination_controller();
            return;
        }

        $relevant_users = $this->getRelevantUsers($user);
        $relevant_user = $this->getRelevantUser($user, $relevant_users, true);
        $this->nextUserIfExists($user, $relevant_user);
    }
}