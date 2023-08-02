<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostTelegramChargeRequest;
use App\Models\Lesson;
use App\Models\TelegramConversation;

class ConversationController extends Controller
{
    public function charge(PostTelegramChargeRequest $request)
    {
        $data = $request->validated();
        $lesson = Lesson::find($data['lesson_id']);

        $this->authorize('view', $lesson);

        $user = auth()->user();

        $active_conversation = $user->activeConversation()->first();
        if ($active_conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже подавали заявку на обсуждение. Сначала дождитесь, пока куратор с вами свяжется и завершите текущее.',
            ]);
        }

        $telegram_user = $user->telegramUser()->first();

        if (empty($telegram_user)) {
            return response()->json([
                'success' => false,
                'message' => 'Введите свой ник в телеграм в личном кабинете.',
            ]);
        } else {
            if (empty($telegram_user->chat_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Для обсуждения начните общение с нашим ботом.',
                    'url' => 'https://t.me/Alles_Test_Bot',
                ]);
            }
        }

        TelegramConversation::create([
            'student_id' => $user->id,
            'lesson_id' => $lesson->id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Отлично! Скоро с вами свяжется наш куратор, и вы сможете обсудить вопросы по уроку ' . $lesson->title,
        ]);
    }
}
