<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Models\TelegramConversation;
use App\Services\UserService;

class QueueCommandController extends CommandController
{
    public function __invoke()
    {
        $chat_id = $this->data->getChatId();
        $message = $this->data->getMessage();

        $telegram_user = $this->getTelegramUserOrDie();
        $user = $telegram_user->user()->first();
        if (!UserService::isNotStudent($user)) {
            $this->respondWithMessage('Вы пока не участвуете в обсуждении. Ваше сообщение никто не видит.');

            return;
        }
        if (UserService::isAdmin($user)) {
            $telegram_conversations = TelegramConversation::with('lesson.course')
                ->where('is_started', false)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $courses = $user
                ->courses()
                ->whereHas('users', function ($query) {
                    return $query->where('is_teacher', true);
                })
                ->with('lessons')
                ->get();

            $lesson_ids = $courses
                ->map(function ($course) {
                    return $course->getRelation('lessons');
                })
                ->collapse()
                ->values()
                ->pluck('id')
                ->toArray();

            $telegram_conversations = TelegramConversation::with('lesson.course')
                ->whereIn('lesson_id', $lesson_ids)
                ->where('is_started', false)
                ->orderByDesc('created_at')
                ->get();
        }

        $telegram_conversations_count = $telegram_conversations->count();
        $telegram_conversations = $telegram_conversations
            ->map(function ($telegram_conversation, $number) use ($message) {
                if (empty($telegram_conversation->toArray())) return null;

                $lesson = $telegram_conversation->getRelation('lesson');
                $course = $lesson->getRelation('course');

                return [
                    'course_name' => $course->name,
                    'lesson_name' => $lesson->title,
                    'time_ago' => $telegram_conversation->created_at->locale('ru')->diffForHumans(),
                    'button' => [
                        'text' => $number + 1,
                        'callback_data' => 'lesson-charge-' . $telegram_conversation->id . '-' . $message->message_id,
                    ]
                ];
            })
            ->reject(function ($telegram_conversation) {
                return $telegram_conversation === null;
            });

        $queue_list = $telegram_conversations->map(function ($telegram_conversation, $number) {
            return '<strong>' . $number + 1 . ') ' . $telegram_conversation['time_ago'] . '.' . '</strong>' . PHP_EOL . '<b>' . 'Курс. ' . '</b>' . $telegram_conversation['course_name'] . PHP_EOL . '<b>' . 'Урок. ' . '</b>' . $telegram_conversation['lesson_name'] . PHP_EOL;
        })->implode('');

        $telegram_conversations = $telegram_conversations
            ->map(function ($telegram_conversation) {
                return $telegram_conversation['button'];
            })
            ->chunk(5)
            ->map(function ($chunk) {
                return $chunk->values();
            })
//            ->merge(
//                collect([[
//                    [
//                        'text' => 'Назад',
//                        'callback_data' => 'page-' . 2,
//                    ],
//                    [
//                        'text' => 'Вперед',
//                        'callback_data' => 'page-' . 1,
//                    ],
//                ]])
//            )
            ->toArray();

        $message = empty($telegram_conversations)
            ? '<strong>Очередь пуста</strong>'
            : '<strong>Есть заявки (' . $telegram_conversations_count . ')</strong>' . PHP_EOL . $queue_list;

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $telegram_conversations
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }
}