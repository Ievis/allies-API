<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

class UsersShowController extends CommandController
{
    public function __invoke()
    {
        $user_id = $this->callback_query_args['user_id'];

    }
}