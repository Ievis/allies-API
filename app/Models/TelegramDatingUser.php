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

    public function firstUserFeedbacks()
    {
        return $this->hasMany(TelegramDatingFeedback::class, 'first_user_id');
    }

    public function secondUserFeedbacks()
    {
        return $this->hasMany(TelegramDatingFeedback::class, 'second_user_id');
    }

    public function relevantUsers()
    {
        return TelegramDatingUser::query()
            ->where('id', '!=', $this->id)
            ->where('subject', $this->subject)
            ->where('category', $this->category);
    }

    public function relevantUsersWithFeedbacks()
    {
        $relevant_liked_users = $this
            ->relevantUsers()
            ->whereHas('firstUserFeedbacks', function ($query) {
                return $query->where('first_user_reaction', true)
                    ->where('second_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', false);
            })
            ->with(['firstUserFeedbacks' => function ($query) {
                return $query->where('first_user_reaction', true)
                    ->where('second_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', false);
            }]);

        $relevant_unliked_users = $this
            ->relevantUsers()
            ->whereDoesntHave('firstUserFeedbacks', function ($query) {
                return $query->where('second_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category);
            })
            ->whereDoesntHave('secondUserFeedbacks', function ($query) {
                return $query->where('first_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category);
            });

        return $relevant_liked_users
            ->union($relevant_unliked_users)
            ->limit(5);
    }

    public function likedUsers()
    {
        return $this::query()
            ->where('id', '!=', $this->id)
            ->whereHas('firstUserFeedbacks', function ($query) {
                return $query->where('second_user_id', $this->id)
                    ->where('first_user_reaction', true)
                    ->where('second_user_reaction', true)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', true);
            })
            ->orWhereHas('secondUserFeedbacks', function ($query) {
                return $query->where('first_user_id', $this->id)
                    ->where('first_user_reaction', true)
                    ->where('second_user_reaction', true)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', true);
            });
    }
}
