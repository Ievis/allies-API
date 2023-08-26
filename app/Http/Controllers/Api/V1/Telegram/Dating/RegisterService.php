<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;

class RegisterService extends CommandController
{
    private array $user_data;
    private array $attribute_messages = [
        'subject' => [
            'error' => 'Выберите один из предложенных предметов!',
            'info' => 'Выбранный предмет - '
        ],
        'category' => [
            'error' => 'Выберите одну из предложенных категорий!',
            'info' => 'Выбранная категория - '
        ],
    ];

    public function setUserData(?array $user_data)
    {
        if (empty($user_data)) {
            $this->respondWithMessage('<strong>Время жизни ваших данных до заполнения всей информации - 1 час. Попробуйте указать их ещё раз.</strong>');

            die();
        }
        $this->user_data = $user_data;
    }

    public function proceed()
    {
        foreach ($this->user_data as $field_name => $field_data) {
            if ($field_data['is_completed']) continue;

            if ($field_data['is_pending']) {
                $username = $this->data->getUsername();
                $message = $this->data->getMessage();
                $attribute_name = $this->callback_query_args['attribute'] ?? null;
                $value = $message->text ?? $this->callback_query_args['value'];

                if ($field_data['type'] === 'callback') {
                    if (!empty($message) or $field_name !== $attribute_name) {
                        $this->respondWithMessage('<strong>' . $this->getAttributeMessage($field_name, 'error') . '</strong>');

                        return;
                    }
                    if (!Cache::has($username . ':' . 'reset-bot-message-id')) {
                        $this->respondWithMessage($this->getAttributeMessage($field_name, 'info') . $value);
                    }
                }

                $this->user_data[$field_name]['is_pending'] = false;
                $this->user_data[$field_name]['is_completed'] = true;
                $this->user_data[$field_name]['value'] = $value;

                Cache::set($username . ':' . 'register-data', $this->user_data, 60 * 60);

                $summary_message_id = Cache::get($username . ':' . 'summary-message-id');
                $reset_bot_message_id = Cache::get($username . ':' . 'reset-bot-message-id');
                if ($reset_bot_message_id) {
                    Cache::forget($username . ':' . 'reset-bot-message-id');
                    $this->deleteMessage($this->data->getMessage()->message_id ?? $this->data->getCallbackQuery()->message->message_id);
                    $this->deleteMessage($reset_bot_message_id);
                }
                if ($summary_message_id) {
                    Cache::forget($username . ':' . 'summary-message-id');
                    $this->telegram_request_service
                        ->setMethodName('editMessageText')
                        ->setParams([
                                'chat_id' => $this->data->getChatId(),
                                'message_id' => $summary_message_id,
                                'text' => '<strong>Ваши данные:</strong>' .
                                    PHP_EOL .
                                    'Имя: ' . $this->user_data['name']['value'] .
                                    PHP_EOL .
                                    'Предмет: ' . $this->user_data['subject']['value'] .
                                    PHP_EOL .
                                    'Категория: ' . $this->user_data['category']['value'] .
                                    PHP_EOL .
                                    PHP_EOL .
                                    'О себе: ' . $this->user_data['about']['value'] .
                                    PHP_EOL .
                                    PHP_EOL .
                                    '<strong>Всё верно?</strong>',
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => 'Да',
                                                'callback_data' => 'confirm-1'
                                            ],
                                            [
                                                'text' => 'Нет',
                                                'callback_data' => 'confirm-0'
                                            ]
                                        ]
                                    ]
                                ]),
                                'parse_mode' => 'html',
                            ]
                        )
                        ->make();

                    return;
                }
                continue;
            }

            $this->{$field_data['method']}();
            return;
        }

        $this->confirm();
    }

    public function confirm()
    {
        $chat_id = $this->data->getChatId();
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => '<strong>Ваши данные:</strong>' .
                    PHP_EOL .
                    'Имя: ' . $this->user_data['name']['value'] .
                    PHP_EOL .
                    'Предмет: ' . $this->user_data['subject']['value'] .
                    PHP_EOL .
                    'Категория: ' . $this->user_data['category']['value'] .
                    PHP_EOL .
                    PHP_EOL .
                    '<strong>Всё верно?</strong>',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Да',
                                'callback_data' => 'confirm-1'
                            ],
                            [
                                'text' => 'Нет',
                                'callback_data' => 'confirm-0'
                            ]
                        ]
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();
    }

    public function persist()
    {
        $username = $this->data->getUsername();
        $chat_id = $this->data->getChatId();
        $name = $this->user_data['name']['value'];
        $subject = $this->user_data['subject']['value'];
        $category = $this->user_data['category']['value'];
        $about = $this->user_data['about']['value'];

        Cache::forget($username . ':' . 'register-data');
        Cache::forget($username . ':' . 'liked-users');
        Cache::forget($username . ':' . 'current-user');
        Cache::forget($username . ':' . 'relevant-users');
        $user = TelegramDatingUser::updateOrCreate([
            'username' => $username
        ], [
            'chat_id' => $chat_id,
            'name' => $name,
            'subject' => $subject,
            'category' => $category,
            'about' => $about
        ]);

        $this->respondWithMessage(' <strong>Отлично!</strong> ' . PHP_EOL . 'Ваши данные сохранены. Мы вам сообщим, когда найдём учеников со схожими интересами.');

        return $user;
    }

    private function getAttributeMessage(string $attribute_name, string $message_type)
    {
        return $this->attribute_messages[$attribute_name][$message_type];
    }

    private function setPendingStatus(string $field)
    {
        $username = $this->data->getUsername();
        $this->user_data[$field]['is_pending'] = true;

        Cache::set($username . ':' . 'register-data', $this->user_data, 60 * 60);
    }

    public function name()
    {
        $this->setPendingStatus('name');

        $response = $this->respondWithMessage('Введите имя');
        $message_id = $response->result->message_id;

        $username = $this->data->getUsername();
        if (Cache::has($username . ':' . 'summary-message-id')) {
            Cache::set($username . ':' . 'reset-bot-message-id', $message_id);
        }
    }

    public function about()
    {
        $this->setPendingStatus('about');

        $response = $this->respondWithMessage('Напишите о себе');
        $message_id = $response->result->message_id;

        $username = $this->data->getUsername();
        if (Cache::has($username . ':' . 'summary-message-id')) {
            Cache::set($username . ':' . 'reset-bot-message-id', $message_id);
        }
    }

    public function subject()
    {
        $this->setPendingStatus('subject');

        $chat_id = $this->data->getChatId();
        $response = $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => 'Укажите предмет',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Математика',
                                'callback_data' => 'register-subject-Математика'
                            ],
                            [
                                'text' => 'Физика',
                                'callback_data' => 'register-subject-Физика'
                            ]
                        ],
                        [
                            [
                                'text' => 'Химия',
                                'callback_data' => 'register-subject-Химия'
                            ],
                            [
                                'text' => 'Русский язык',
                                'callback_data' => 'register-subject-Русский язык'
                            ]
                        ],
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();

        $message_id = $response->result->message_id;

        $username = $this->data->getUsername();
        if (Cache::has($username . ':' . 'summary-message-id')) {
            Cache::set($username . ':' . 'reset-bot-message-id', $message_id);
        }
    }

    public function category()
    {
        $this->setPendingStatus('category');

        $chat_id = $this->data->getChatId();
        $response = $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => 'Укажите категорию',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'ОГЭ',
                                'callback_data' => 'register-category-ОГЭ'
                            ],
                            [
                                'text' => 'ЕГЭ',
                                'callback_data' => 'register-category-ЕГЭ'
                            ]
                        ],
                        [
                            [
                                'text' => 'Олимпиады',
                                'callback_data' => 'register-category-Олимпиады'
                            ],
                            [
                                'text' => 'Другое',
                                'callback_data' => 'register-category-Другое'
                            ]
                        ],
                    ]
                ]),
                'parse_mode' => 'html',
            ])
            ->make();

        $message_id = $response->result->message_id;

        $username = $this->data->getUsername();
        if (Cache::has($username . ':' . 'summary-message-id')) {
            Cache::set($username . ':' . 'reset-bot-message-id', $message_id);
        }
    }
}