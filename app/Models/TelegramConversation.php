<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramConversation extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function curator()
    {
        return $this->belongsTo(User::class, 'curator_id');
    }

    public function messages()
    {
        return $this->hasMany(TelegramConversationMessage::class);
    }
}
