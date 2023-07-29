<?php

namespace App\Http\Controllers\Api\V1\Telegram\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostTelegramChargeRequest;
use App\Models\Lesson;
use App\Models\TelegramConversation;
use App\Models\TelegramUser;
use App\Services\TelegramRequestFormerService;
use App\Services\UserService;
use Illuminate\Http\Request;
use stdClass;

class ConversationController extends Controller
{
    private TelegramRequestFormerService $telegram_request_service;
    private array $commands = [
        '/start' => 'start',
        '/error' => 'error',
        '/queue' => 'queue',
        '/exit' => 'exit',
        '/conversations' => 'conversations'
    ];

    private array $callback_queries = [
        'lesson-charge-{telegram_conversation_id}-{message_id}' => 'beginConversation',
        'exit-{decision}-{message_id}' => 'endConversation'
    ];

    private array $callback_query_args;

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
        $method_name = $this->commands[$command] ?? $this->callback_queries[$command];
        $this->{$method_name}();
    }

    private function validateCommand()
    {
        $command = $this->message->text ?? $this->callback_query->data ?? null;

        foreach ($this->callback_queries as $callback_query => $method_name) {
            $args_start_position = strpos($callback_query, '-{');
            $callback_query_prefix = substr($callback_query, 0, $args_start_position);

            if (str_starts_with($command, $callback_query_prefix)) {
                $this->setCallbackArgs($callback_query_prefix);

                return $callback_query;
            }
        }

        return array_key_exists($command, $this->commands)
            ? $command
            : '/error';
    }

    private function setCallbackArgs(string $prefix)
    {
        $callback_query = $this->callback_query->data;
        $last_delimiter_position = strlen($prefix);
        $args_with_delimiters = substr($callback_query, $last_delimiter_position + 1, strlen($callback_query));

        $arg_names = [];
        foreach ($this->callback_queries as $callback_query => $method_name) {
            if (str_starts_with($callback_query, $prefix)) {
                preg_match_all('#\{(.*?)\}#', $callback_query, $arg_names);
                $arg_names = $arg_names[1];
            }
        }

        $args = [];
        foreach ($arg_names as $arg_name) {
            if (substr_count($args_with_delimiters, '-') === 0) {
                $args[$arg_name] = $args_with_delimiters;

                break;
            }

            $delimiter_position = strpos($args_with_delimiters, '-');
            $args[$arg_name] = substr($args_with_delimiters, 0, $delimiter_position);
            $args_with_delimiters = substr($args_with_delimiters, $delimiter_position + 1, strlen($args_with_delimiters));

        }

        $this->callback_query_args = $args;
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
            if (UserService::isNotStudent($user)) {
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

    private function getActiveConversation()
    {
        $telegram_user = $this->getTelegramUser();

        return TelegramConversation::where('student_id', $telegram_user->user_id)
            ->where('is_active', true)
            ->orWhere('curator_id', $telegram_user->user_id)
            ->where('is_active', true)
            ->first();
    }

    private function getUser()
    {
        $telegram_user = TelegramUser::where('username', $this->username)->first();
        return $telegram_user->user()->first();
    }

    public function error()
    {
        $user = $this->getUser();
        $user_active_conversation = $this->getActiveConversation();

        if ($user_active_conversation) {
            $telegram_student = $user_active_conversation->studentTelegram()->first();
            $telegram_curator = $user_active_conversation->curatorTelegram()->first();

            if (UserService::isNotStudent($user)) {
                $this->respondWithMessage($this->message->text, $telegram_student->chat_id);

                return;
            }

            $this->respondWithMessage($this->message->text, $telegram_curator->chat_id);

            return;
        }

        $this->respondWithMessage('Вы пока не участвуете в обсуждении. Ваше сообщение никто не видит.');
    }

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

    public function queue()
    {
        $telegram_user = TelegramUser::where('username', $this->username)->first();
        $user = $telegram_user->user()->first();
        if (empty($user)) {
            $this->error();

            return;
        }
        if (!UserService::isNotStudent($user)) {
            $this->error();

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
            ->map(function ($telegram_conversation, $number) {
                if (empty($telegram_conversation->toArray())) return null;

                $lesson = $telegram_conversation->getRelation('lesson');
                $course = $lesson->getRelation('course');

                return [
                    'course_name' => $course->name,
                    'lesson_name' => $lesson->title,
                    'time_ago' => $telegram_conversation->created_at->locale('ru')->diffForHumans(),
                    'button' => [
                        'text' => $number + 1,
                        'callback_data' => 'lesson-charge-' . $telegram_conversation->id . '-' . $this->message->message_id,
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
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $telegram_conversations
                ]),
                'parse_mode' => 'html',
            ])
            ->make('GET');
    }

    private function getTelegramUser()
    {
        return TelegramUser::where('username', $this->username)->first();
    }

    public function beginConversation()
    {
        $message_id = $this->callback_query_args['message_id'];
        $this->deleteMessage($message_id);
        $this->deleteMessage($this->callback_query->message->message_id);
        $telegram_conversation_id = $this->callback_query_args['telegram_conversation_id'];
        $telegram_conversation = TelegramConversation::find($telegram_conversation_id);

        if ($telegram_conversation->is_active) {
            return;
        }

        $telegram_user = $this->getTelegramUser();

        $telegram_student = $telegram_conversation->studentTelegram()->first();
        $telegram_curator = $telegram_conversation->curatorTelegram()->first();

        $telegram_conversation->update([
            'curator_id' => $telegram_user->user_id,
            'is_started' => true,
            'is_active' => true
        ]);

        $student_message = 'Здравствуйте! Куратор начал обсуждение по уроку.' . PHP_EOL . '<strong>Задавайте свои вопросы.</strong>';
        $curator_message = 'Вы начали обсуждение.' . PHP_EOL . '<strong>Обсуждение активно!</strong>';

        $this->respondWithMessage($student_message, $telegram_student->chat_id);
        $this->respondWithMessage($curator_message, $telegram_user->chat_id);
    }

    private function exit()
    {
        $user_active_conversation = $this->getActiveConversation();

        if (empty($user_active_conversation)) {
            $this->respondWithMessage('<strong>Вы не пока не участвуете ни в каком обсуждении!</strong>');

            return;
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => 'Вы уверены, что хотите закончить обсуждение?',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[
                        [
                            'text' => 'Да',
                            'callback_data' => 'exit-' . 1 . '-' . $this->message->message_id,
                        ],
                        [
                            'text' => 'Нет',
                            'callback_data' => 'exit-' . 0 . '-' . $this->message->message_id,
                        ],
                    ]]
                ]),
                'parse_mode' => 'html',
            ])
            ->make('GET');
    }

    private function endConversation()
    {
        $user = $this->getUser();
        $user_active_conversation = $this->getActiveConversation();

        if (empty($user_active_conversation)) {
            $this->respondWithMessage('<strong>Вы не пока не участвуете ни в каком обсуждении!</strong>');

            return;
        }

        $decision = $this->callback_query_args['decision'];
        $message_id = $this->callback_query_args['message_id'];
        if ($decision == 0) {
            $this->deleteMessage($this->callback_query->message->message_id);
            $this->deleteMessage($message_id);

            return;
        }

        $user_active_conversation->update([
            'is_resolved' => true,
            'is_active' => false,
        ]);

        $student_telegram = $user_active_conversation->studentTelegram()->first();
        $curator_telegram = $user_active_conversation->curatorTelegram()->first();

        $this->deleteMessage($this->callback_query->message->message_id);
        $this->deleteMessage($message_id);

        if (UserService::isNotStudent($user)) {
            $this->respondWithMessage('Куратор завершил обсуждение.' . PHP_EOL . 'Можете начинать другое.', $student_telegram->chat_id);
        } else {
            $this->respondWithMessage('Ученик завершил обсуждение.' . PHP_EOL . 'Можете начинать другое.', $curator_telegram->chat_id);
        }

        $this->respondWithMessage('Вы завершили обсуждение.' . PHP_EOL . 'Можете начинать другое.');
    }

    private function conversations()
    {
        $started_conversations = $this->getStartedConversations();

        if (empty($started_conversations)) {
            $message = 'У вас пока нет начатых обсуждений.';
        } else {
            $message = '<strong>Начатые обсуждения ' . '(' . $started_conversations->count() . ')</strong>';

            $started_conversations = $started_conversations
                ->map(function ($started_conversation, $number) {
                    return [
                        'text' => $number + 1,
                        'callback_data' => 'lesson-charge-' . $started_conversation->id,
                    ];
                })
                ->chunk(5)
                ->map(function ($chunk) {
                    return $chunk->values();
                })
                ->toArray();
        }

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'reply_markup' => json_encode([
                    'inline_keyboard' => $started_conversations
                ]),
                'parse_mode' => 'html',
            ])
            ->make('GET');

    }

    private function getStartedConversations()
    {
        $telegram_user = $this->getTelegramUser();

        return TelegramConversation::where('student_id', $telegram_user->user_id)
            ->where('is_started', true)
            ->orWhere('curator_id', $telegram_user->user_id)
            ->where('is_started', true)
            ->get();
    }

    private function respondWithMessage(string $message, int $chat_id = null)
    {
        $chat_id = $chat_id ?? $this->chat_id;

        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'html',
            ])
            ->make('GET');
    }

    private function deleteMessage(int $message_id = null, int $chat_id = null)
    {
        $chat_id = $chat_id ?? $this->chat_id;

        $this->telegram_request_service
            ->setMethodName('deleteMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'parse_mode' => 'html',
            ])
            ->make('GET');
    }
}
