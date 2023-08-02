<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramConversationMessage extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function telegramConversation()
    {
        return $this->belongsTo(TelegramConversation::class);
    }
}
