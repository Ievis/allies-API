<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TelegramDatingFeedback extends Model
{
    use HasFactory;

    protected $table = 'telegram_dating_feedback';
    protected $guarded = false;

    public function users()
    {
        return [
            'first_user' => [
                'id' => $this->first_user_id,
                'reaction' => $this->first_user_reaction
            ],
            'second_user' => [
                'id' => $this->second_user_id,
                'reaction' => $this->second_user_reaction
            ]
        ];
    }
}
