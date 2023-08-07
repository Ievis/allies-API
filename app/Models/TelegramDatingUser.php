<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TelegramDatingUser extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function feedback(?bool $reaction)
    {
        return empty($reaction)
            ? DB::table('telegram_dating_feedbacks')
                ->where('first_user_id', $this->id)
                ->orWhere('second_user_id', $this->id)

            : DB::table('telegram_dating_feedbacks')
                ->where('first_user_id', $this->id)
                ->where('first_user_reaction', $reaction)
                ->where('second_user_reaction', $reaction)
                ->orWhere('second_user_id', $this->id)
                ->where('first_user_reaction', $reaction)
                ->where('second_user_reaction', $reaction);
    }
}
