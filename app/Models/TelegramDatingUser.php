<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
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
        $cached_feedbacks = collect(Cache::tags(['feedbacks'])->get('all'));
        $liked_feedbacks = $cached_feedbacks->where('first_user_reaction', true)
            ->where('second_user_id', $this->id)
            ->where('subject', $this->subject)
            ->where('category', $this->category)
            ->where('is_resolved', false)
            ->unique()
            ->values();
        $first_excluded_ids = $cached_feedbacks->where('first_user_id', $this->id)
            ->where('subject', $this->subject)
            ->where('category', $this->category)
            ->pluck('second_user_id')
            ->unique()
            ->values()
            ->toArray();
        $second_excluded_ids = $cached_feedbacks->where('second_user_id', $this->id)
            ->where('subject', $this->subject)
            ->where('category', $this->category)
            ->where('is_resolved', true)
            ->pluck('first_user_id')
            ->unique()
            ->values()
            ->toArray();
        $excluded_ids = array_merge($first_excluded_ids, $second_excluded_ids);
        $included_ids = $liked_feedbacks->pluck('first_user_id')->unique()->toArray();

        $relevant_liked_users = $this
            ->relevantUsers()
            ->whereNotIn('id', $excluded_ids)
            ->whereHas('firstUserFeedbacks', function ($query) {
                return $query->where('first_user_reaction', true)
                    ->where('second_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', false);
            })
            ->orWhereIn('id', $included_ids)
            ->where('id', '!=', $this->id)
            ->whereNotIn('id', $excluded_ids)
            ->with(['firstUserFeedbacks' => function ($query) {
                return $query->where('first_user_reaction', true)
                    ->where('second_user_id', $this->id)
                    ->where('subject', $this->subject)
                    ->where('category', $this->category)
                    ->where('is_resolved', false);
            }]);

        $relevant_unliked_users = $this
            ->relevantUsers()
            ->whereNotIn('id', $excluded_ids)
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
            ->limit(500)
            ->get()
            ->map(function ($user) use ($liked_feedbacks, $included_ids) {
                if (in_array($user->id, $included_ids)) {
                    $liked_feedback = $liked_feedbacks->where('first_user_id', $user->id)->first();
                    $user->setRelation('firstUserFeedbacks', collect([$liked_feedback]));
                }

                return $user;
            });
    }

    public function likedUsers()
    {
        $feedbacks = Cache::tags(['feedbacks'])->get('all') ?? collect();

        $first_cached_liked_feedbacks = $feedbacks
            ->where('first_user_id', $this->id)
            ->where('first_user_reaction', true)
            ->where('second_user_reaction', true)
            ->where('subject', $this->subject)
            ->where('category', $this->category)
            ->where('is_resolved', true);
        $second_cached_liked_feedbacks = $feedbacks
            ->where('second_user_id', $this->id)
            ->where('first_user_reaction', true)
            ->where('second_user_reaction', true)
            ->where('subject', $this->subject)
            ->where('category', $this->category)
            ->where('is_resolved', true);
        $cached_liked_feedbacks = $first_cached_liked_feedbacks->union($second_cached_liked_feedbacks);

        $included_ids = $cached_liked_feedbacks->map(function ($feedback) {
            return $feedback['first_user_id'] == $this->id
                ? $feedback['second_user_id']
                : $feedback['first_user_id'];
        });

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
            })
            ->orWhere('id', '!=', $this->id)
            ->whereIn('id', $included_ids);
    }
}
