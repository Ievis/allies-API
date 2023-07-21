<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostTelegramChargeRequest;
use App\Models\Lesson;
use App\Models\TelegramConversation;
use App\Models\TelegramUser;
use App\Models\User;
use App\Policies\LessonPolicy;
use App\Services\LessonAccessService;
use App\Services\TelegramRequestFormerService;
use App\Services\UserService;
use Database\Seeders\CourseUserSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use stdClass;

class TelegramController extends Controller
{
    private TelegramRequestFormerService $telegram_request_service;
    private array $commands = [
        '/start' => 'start',
        '/error' => 'error',
        '/queue' => 'queue',
    ];

    private int $chat_id;
    private string $username;
    private stdClass $data;
    private ?stdClass $message;
    private ?stdClass $callback_query;

    public function __construct()
    {
        $this->telegram_request_service = new TelegramRequestFormerService();
    }

    public function index(Request $request)
    {
        $request_body = $request->getContent();
        $this->data = json_decode($request_body);

        $this->setMessageIfExists();
        $this->setCallbackQueryIfExists();
        $this->setChatId();
        $this->setUsername();

        $this->handle();
    }

    private function setUsername()
    {
        $this->username = $this->message->from->username ?? $this->callback_query->from->username;
    }

    private function handle()
    {
        $command = $this->validateCommand();
        $method_name = $this->commands[$command];

        $this->{$method_name}();
    }

    private function validateCommand()
    {
        $command = $this->message->text ?? $this->callback_query->data ?? null;

        return array_key_exists($command, $this->commands)
            ? $command
            : '/error';
    }

    private function setChatId()
    {
        $this->chat_id = $this->message->chat->id ?? $this->callback_query->message->chat->id ?? null;
    }

    private function setMessageIfExists()
    {
        $this->message = $this->data->message ?? null;
    }

    private function setCallbackQueryIfExists()
    {
        $this->callback_query = $this->data->callback_query ?? null;
    }

    public function start()
    {
        $telegram_user = TelegramUser::where('username', $this->username)->first();
        $user = $telegram_user?->user()?->first();

        if ($telegram_user and empty($telegram_user->chat_id)) {
            $telegram_user->chat_id = $this->chat_id;

            $telegram_user->save();
        }

        $message = $telegram_user
            ? 'Вы успешно авторизованы! Можете отправить запрос на обсуждение на нашем сайте, после чего с вами свяжется наш куратор.'
            : 'Привет от Alles! Это наш бот для общения учеников кураторами. Чтобы общаться с кураторами, необходимо указать свой ник в телеграм-аккаунте, с которого сейчас пишите, в личном кабинете на нашем сайте.';

        $url = $telegram_user
            ? [['text' => 'Выбрать урок для обсуждения на сайте', 'url' => 'https://alles-online.ru']]
            : [['text' => 'Войти', 'url' => 'https://alles-online.ru']];

        if ($telegram_user) {
            if (UserService::isTeacher($user)) {
                $message = 'Вы успешно авторизованы! Можете приступать к общению с учениками. Чтобы посмотреть очередь заявок от учеников, введите команду /queue.';
                $url = [];
            }
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => [$url]
                ])
            ])
            ->make('GET');
    }

    public function error()
    {
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => 'Вы пока не участвуете в обсуждении. Ваше сообщение никто не видит.',
            ])
            ->make('GET');
    }

    public function charge(PostTelegramChargeRequest $request)
    {
        $data = $request->validated();
        $lesson = Lesson::find($data['lesson_id']);

        $this->authorize('view', $lesson);

        $user = auth()->user();
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

    public function queue()
    {
        $telegram_user = TelegramUser::where('username', $this->username)->first();
        $user = $telegram_user->user()->first();
        if (empty($user)) {
            $this->error();

            return;
        }
        if (!UserService::isTeacher($user)) {
            $this->error();

            return;
        }
        if (UserService::isAdmin($user)) {
            $telegram_conversations = TelegramConversation::where('is_resolved', false)->get();
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

            $telegram_conversations = TelegramConversation::whereIn('lesson_id', $lesson_ids)
                ->where('is_resolved', false)
                ->get();
        }

        $telegram_conversations = $telegram_conversations
            ->map(function ($telegram_conversation) {
                if (empty($telegram_conversation->toArray())) return null;

                return [
                    [
                        'text' => 'Урок ' . $telegram_conversation->lesson_id,
                        'callback_data' => 'lesson-charge-' . $telegram_conversation->lesson_id,
                    ]
                ];
            })
            ->reject(function ($telegram_conversation) {
                return $telegram_conversation === null;
            })
            ->toArray();

        $message = empty($telegram_conversations)
            ? 'Очередь пуста'
            : 'Есть заявки';

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $telegram_conversations
                ])
            ])
            ->make('GET');
    }
}
