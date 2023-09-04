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

}
