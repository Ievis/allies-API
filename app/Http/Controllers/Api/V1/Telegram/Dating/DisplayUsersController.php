<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DisplayUsersController extends CommandController
{
    public function __invoke()
    {
        $this->setUserData();
        $this->deleteNotificationMessageIfExists();
        $decision = $this->input('decision');
        $user = $this->user_data->get('user');
        if ($decision) {
            $users_pagination_controller = new UsersPaginationController();
            $users_pagination_controller->setTelegramUserData($this->data);
            $users_pagination_controller->setCallbackArgs($this->callback_query_args);

            $users_pagination_controller($this->user_data);
            return;
        }

        $relevant_user = $this->getRelevantUser(true);
        $this->nextUserIfExists($relevant_user);
    }
}