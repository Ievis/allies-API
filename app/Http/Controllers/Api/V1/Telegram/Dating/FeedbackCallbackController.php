<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingFeedback;
use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;

class FeedbackCallbackController extends CommandController
{
    private TelegramDatingUser $user;
    private TelegramDatingUser $showed_user;
    private TelegramDatingUser $first_user;
    private TelegramDatingUser $second_user;
    private string $first_username;
    private string $second_username;
    private bool $decision;
    private array $feedback;
    private array $feedbacks;
    private bool $is_feedbacks_affectable = true;

    private function setUser()
    {
        $username = $this->data->getUsername();
        $this->user = Cache::get($username . ':' . 'user-data');
    }

    private function setShowedUser()
    {
        return $this->first_username == $this->user->username
            ? $this->user
            : Cache::get($this->second_username . ':' . 'user-data');
    }

    private function setFeedbacks()
    {
        $this->feedbacks = Cache::tags(['feedbacks'])->get('all');
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
        $first_user_reaction = $this->getFirstUserReaction();
        $second_user_reaction = $this->getSecondUserReaction();

        return $first_user_reaction == true and $second_user_reaction == true
            or $first_user_reaction == false;
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

        $this->checkFeedbacksForCurrentUser();
    }

    private function validateFeedback()
    {
        return in_array($this->feedback, $this->feedbacks);
    }

    private function affectShowedUserCache()
    {
        $showed_relevant_users = Cache::get($this->showed_user->username . ':' . 'relevant-users');
        if ($showed_relevant_users->contains($this->user)) {
            if ($this->isResolved()) {
                $showed_relevant_users->reject(function ($user) {
                    return $user == $this->user->id;
                });
                Cache::set($this->showed_user->username . ':' . 'relevant-users', $showed_relevant_users);

                return;
            }
            $showed_relevant_users->map(function ($user) {
                if ($user->id == $this->user->id) {
                    $user->setRelation('firstUserFeedbacks', $this->feedback);
                }

                return $user;
            });
            Cache::set($this->showed_user->username . ':' . 'relevant-users', $showed_relevant_users);

            return;
        }

        $showed_current_user = Cache::get($this->showed_user->username . ':' . 'current-user');
        if ($showed_current_user->id == $this->user->id) {
            $showed_current_user->setRelation('firstUserFeedbacks', $this->feedback);
            Cache::set($this->showed_user->username . ':' . 'current-user', $showed_current_user);

            $this->is_feedbacks_affectable = false;
        }
    }

    private function checkFeedbacksForCurrentUser()
    {
        $current_user = Cache::get($this->user->username . ':' . 'current-user');
        $feedbacks = $current_user->getRelation('firstUserFeedbacks');
        if ($feedbacks->isNotEmpty()) {
            $feedback = $feedbacks->first();
            $feedback->second_user_reaction = $this->decision;
            $feedback->is_resolved = true;

            $this->feedback = $feedback;
        }
    }

    private function affectFeedbacksCache()
    {
        if ($this->is_feedbacks_affectable) {
            $this->feedbacks[] = $this->feedback;
            Cache::tags(['feedbacks'])->put('all', $this->feedbacks);
        }
    }

    public function __invoke()
    {
        $this->deleteNotificationMessageIfExists();
        $decision = $this->input('decision');
        $first_username = $this->input('first_username');
        $second_username = $this->input('second_username');

        $this->setUsernames($first_username, $second_username);
        $this->setDecision($decision);
        $this->setUser();
        $this->setShowedUser();

        $this->setFirstUser();
        $this->setSecondUser();
        $this->setFeedbacks();
        $this->setFeedback();
        if ($this->validateFeedback()) {
            return;
        }
        $this->affectFeedbacksCache();
        $this->affectShowedUserCache();
        $this->affectLikedUsersCache();

//        $users = TelegramDatingUser::whereIn('id', [$first_user_id, $second_user_id])->get();
//        $users = $users
//            ->mapWithKeys(function ($user) use ($first_user_id) {
//                return $first_user_id == $user->id
//                    ? ['first_user' => $user]
//                    : ['second_user' => $user];
//            });
//        $first_user = $users->get('first_user');
//        $second_user = $users->get('second_user');
//
//        $feedback = TelegramDatingFeedback::query()
//            ->where('first_user_id', $first_user_id)
//            ->where('second_user_id', $second_user_id)
//            ->where('subject', $first_user->subject)
//            ->where('category', $first_user->category)
//            ->first();
//
//        $user = empty($feedback)
//            ? $first_user
//            : $second_user;
//
//        if (empty($feedback)) {
//            TelegramDatingFeedback::create([
//                'first_user_id' => $first_user_id,
//                'second_user_id' => $second_user_id,
//                'first_user_reaction' => $decision,
//                'subject' => $user->subject,
//                'category' => $user->category,
//                'is_resolved' => !$decision
//            ]);
//            $relevant_users = $this->getRelevantUsers($user);
//            $relevant_user = $this->getRelevantUser($user, $relevant_users);
//            $this->nextUserIfExists($user, $relevant_user);
//
//            return;
//        }
//        $user = Cache::get($this->data->getUsername() . ':' . 'user-data');
//        if ($feedback->first_user_id == $user->id) {
//            $relevant_users = $this->getRelevantUsers($user);
//            $relevant_user = $this->getRelevantUser($user, $relevant_users);
//            $this->nextUserIfExists($user, $relevant_user);
//
//            return;
//        }
//
//        if ($feedback->is_resolved) {
//            $relevant_users = $this->getRelevantUsers($user);
//            $relevant_user = $this->getRelevantUser($user, $relevant_users);
//            $this->nextUserIfExists($user, $relevant_user);
//
//            return;
//        }
//
//        $feedback->update([
//            'second_user_reaction' => $decision,
//            'is_resolved' => true
//        ]);
//
//        if ($decision) {
//            $this->affectLikedUsersCache($first_user, $second_user);
//
//            $this->telegram_request_service
//                ->setMethodName('editMessageText')
//                ->setParams([
//                    'chat_id' => $chat_id,
//                    'message_id' => $callback_query->message->message_id,
//                    'text' => 'Ник в telegram: ' .
//                        '<strong>' . '@' . $first_user->username . '</strong>' .
//                        PHP_EOL
//                        . 'Имя: ' .
//                        $first_user->name .
//                        PHP_EOL .
//                        'Предмет: ' .
//                        $first_user->subject .
//                        PHP_EOL .
//                        'Категория: ' .
//                        $first_user->category .
//                        PHP_EOL .
//                        PHP_EOL .
//                        'О себе: ' .
//                        $first_user->about,
//                    'reply_markup' => json_encode([
//                        'inline_keyboard' => [
//                            [
//                                [
//                                    'text' => 'Следующий',
//                                    'callback_data' => 'feedback-1-' . $first_user_id . '-' . $second_user_id
//                                ]
//                            ]
//                        ]
//                    ]),
//                    'parse_mode' => 'html',
//                ])
//                ->make();
//
//            return;
//        }
//
//        $relevant_users = $this->getRelevantUsers($user);
//        $relevant_user = $this->getRelevantUser($user, $relevant_users);
//        $this->nextUserIfExists($user, $relevant_user);
    }

    private function affectLikedUsersCache()
    {
        $liked_users = Cache::get($this->user->username . ':' . 'liked-users');
        $showed_liked_users = Cache::get($this->showed_user->username . ':' . 'liked-users');

        if ($liked_users) {
            $liked_users->push($this->showed_user);
            Cache::set($this->user->username . ':' . 'liked-users', $liked_users, 3600);
        }
        if ($showed_liked_users) {
            $showed_liked_users->push($this->user);
            Cache::set($this->showed_user->username . ':' . 'liked-users', $showed_liked_users, 3600);
        }
    }
}