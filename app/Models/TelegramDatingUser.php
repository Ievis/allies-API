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

    public function feedbacks()
    {
        return $this->hasMany(TelegramDatingFeedback::class, 'second_user_id')
            ->union($this->hasMany(TelegramDatingFeedback::class, 'second_user_id')->toBase());
    }

    public function relevantUsers()
    {
        return TelegramDatingUser::query()
            ->where('id', '!=', $this->id)
            ->where('subject', $this->subject)
            ->where('category', $this->category);
    }
}
