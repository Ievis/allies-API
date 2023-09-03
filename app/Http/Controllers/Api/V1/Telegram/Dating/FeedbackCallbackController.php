<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FeedbackCallbackController extends CommandController
{
    private TelegramDatingUser $user;
    private TelegramDatingUser $showed_user;
    private TelegramDatingUser $first_user;
    private TelegramDatingUser $second_user;
    private UserData $showed_user_data;
    private string $first_username;
    private string $second_username;
    private bool $decision;
    private bool $is_matched;
    private array $feedback;
    private Collection $feedbacks;
    private bool $is_feedbacks_affectable = true;

    public function __invoke()
    {
//        die();
        $this->setUserData();
        $this->deleteNotificationMessageIfExists();
        $decision = $this->input('decision');
        $first_username = $this->input('first_username');
        $second_username = $this->input('second_username');
        $this->setUsernames($first_username, $second_username);
        $this->setDecision($decision);

        $this->setShowedUserData();
        $this->setFirstUser();
        $this->setSecondUser();

        $this->setFeedbacks();
        $this->setFeedback();

        if (!$this->validateFeedback() or !$this->validateInstantFeedback() or $this->isRevealed()) {
            $relevant_user = $this->getRelevantUser();
            $this->nextUserIfExists($relevant_user);
            $this->user_data->save();

            return;
        }

        $this->affectFeedbacksCache();
        $this->affectShowedUserCache();
        $this->affectLikedUsersCache();

        if ($this->is_matched) {
            $this->revealUser();
            $this->user_data->save();
            $this->showed_user_data->save();

            return;
        }

        $relevant_user = $this->getRelevantUser();
        $this->nextUserIfExists($relevant_user);
        $this->user_data->save();
        $this->showed_user_data->save();
    }

    private function setShowedUserData()
    {
        $user = $this->user_data->get('user');
        $this->user = $user;
        $username = $this->first_username == $user->username
            ? $this->second_username
            : $this->first_username;

        $this->showed_user_data = new UserData($username);
        $this->showed_user = $this->showed_user_data->get('user');
    }

    private function setFeedbacks()
    {
        $this->feedbacks = Cache::tags(['feedbacks'])->get('all') ?? collect();
    }

    private function setUsernames($first_username, $second_username)
    {
        $this->first_username = $first_username;
        $this->second_username = $second_username;
    }

    private function setDecision($decision)
    {
        $this->decision = $decision;
    }

    private function setFirstUser()
    {
        $this->first_user = $this->first_username == $this->user->username
            ? $this->user
            : $this->showed_user;
    }

    private function setSecondUser()
    {
        $this->second_user = $this->second_username == $this->user->username
            ? $this->user
            : $this->showed_user;
    }

    private function getFirstUserReaction()
    {
        return $this->first_user->id == $this->user->id
            ? $this->decision
            : true;
    }

    private function getSecondUserReaction()
    {
        return $this->second_user->id == $this->user->id
            ? $this->decision
            : false;
    }

    private function isResolved()
    {
        $second_user_reaction = $this->getSecondUserReaction();

        $this->is_matched = $second_user_reaction;
        return $this->is_matched or !$this->decision;
    }

    private function isRevealed()
    {
        $current_user = $this->user_data->get('current_user');
        $is_revealed = $current_user->is_revealed ?? null;

        return !is_null($is_revealed);
    }

    private function setFeedback()
    {
        $this->feedback = [
            'first_user_id' => $this->first_user->id,
            'second_user_id' => $this->second_user->id,
            'first_user_reaction' => $this->getFirstUserReaction(),
            'second_user_reaction' => $this->getSecondUserReaction(),
            'subject' => $this->user->subject,
            'category' => $this->user->category,
            'is_resolved' => $this->isResolved(),
        ];
    }

    private function validateFeedback()
    {
        return !$this->feedbacks->contains($this->feedbacks);
    }

    private function validateInstantFeedback(): bool
    {
        $current_user = $this->user_data->get('current_user');
        $instant_feedback = $current_user->instant_feedback ?? null;

        return is_null($instant_feedback) or !$instant_feedback->is_resolved;
    }

    private function affectShowedUserCache()
    {
        $showed_relevant_users = $this->showed_user_data->get('relevant_users');
        $showed_current_user = $this->showed_user_data->get('current_user');

        if ($showed_relevant_users->contains($this->user)) {
            if (!$this->getFirstUserReaction()) {
                $showed_relevant_users->reject(function ($user) {
                    return $user == $this->user->id;
                });
                $this->showed_user_data->set('relevant_users', $showed_relevant_users);

                return;
            }
            if ($this->getSecondUserReaction()) {
                $showed_relevant_users->map(function ($user) {
                    if ($user->id == $this->user->id) {
                        $user->setRelation('firstUserFeedbacks', $this->feedback);
                    }

                    return $user;
                });
                $this->showed_user_data->set('relevant_users', $showed_relevant_users);

                return;
            }
        }

        $showed_current_user_id = $showed_current_user->id ?? null;
        if ($showed_current_user_id == $this->user->id) {
            $showed_current_user->instant_feedback = $this->feedback;
            $this->showed_user_data->set('current_user', $showed_current_user);

            $this->is_feedbacks_affectable = false;
        }
    }

    private function affectFeedbacksCache()
    {
        if ($this->is_feedbacks_affectable) {
            $this->feedbacks->push($this->feedback);
            Cache::tags(['feedbacks'])->put('all', $this->feedbacks);
        }
    }

    private function affectLikedUsersCache()
    {
        if (!$this->is_matched) return;

        $current_user = $this->user_data->get('current_user');
        $current_user->is_revealed = true;
        $this->user_data->set('current_user', $current_user);

        $liked_users = $this->user_data->get('liked_users');
        $showed_liked_users = $this->showed_user_data->get('liked_users');

        if ($liked_users) {
            $liked_users->push($this->showed_user);
            $this->user_data->set('liked_users', $liked_users);
        }
        if ($showed_liked_users) {
            $showed_liked_users->push($this->user);
            $this->showed_user_data->set('liked_users', $showed_liked_users);
        }
    }
}