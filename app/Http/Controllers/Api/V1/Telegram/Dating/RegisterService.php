<?php

namespace App\Http\Controllers\Api\V1\Telegram\Dating;

use App\Models\TelegramDatingUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegisterService extends CommandController
{
    private RegisterData $register_data;
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

    public function setRegisterData(?RegisterData $register_data)
    {
        if (empty($register_data)) {
            $this->respondWithMessage('<strong>Попробуйте зарегистрироваться ещё раз</strong>');

            die();
        }

        $this->register_data = $register_data;
    }

    public function proceed()
    {
        $fields = $this->register_data->get('fields');
        $summary_message_id = $this->register_data->get('summary_message_id');
        $reset_bot_message_id = $this->register_data->get('reset_bot_message_id');

        foreach ($fields as $field_name => $field_data) {
            if ($field_data['is_completed']) continue;

            if ($field_data['is_pending']) {
                $message = $this->data->getMessage();
                $attribute_name = $this->input('attribute') ?? null;
                $value = $message->text ?? $this->input('value');

                if ($field_data['type'] === 'callback') {
                    if (!empty($message) or $field_name !== $attribute_name) {
                        $this->respondWithMessage('<strong>' . $this->getAttributeMessage($field_name, 'error') . '</strong>');

                        return;
                    }
                    if (empty($reset_bot_message_id)) {
                        $this->respondWithMessage($this->getAttributeMessage($field_name, 'info') . $value);
                    }
                }

                $this->setField($field_name, $value);
                if ($summary_message_id) {
                    $this->deleteMessage($this->data->getMessage()->message_id ?? $this->data->getCallbackQuery()->message->message_id);
                    $this->deleteMessage($reset_bot_message_id);
                    $this->editSummaryMessage($summary_message_id);

                    $this->register_data->delete('summary_message_id');
                    $this->register_data->delete('reset_bot_message_id');
                    $this->register_data->save();
                    return;
                }

                $this->register_data->save();
                continue;
            }

            $this->{$field_data['method']}();
            return;
        }

        $this->confirm();
    }

    private function editSummaryMessage($summary_message_id)
    {
        $fields = $this->register_data->get('fields');
        $this->telegram_request_service
            ->setMethodName('editMessageText')
            ->setParams([
                    'chat_id' => $this->data->getChatId(),
                    'message_id' => $summary_message_id,
                    'text' => '<strong>Ваши данные:</strong>' .
                        PHP_EOL .
                        'Имя: ' . $fields['name']['value'] .
                        PHP_EOL .
                        'Предмет: ' . $fields['subject']['value'] .
                        PHP_EOL .
                        'Категория: ' . $fields['category']['value'] .
                        PHP_EOL .
                        PHP_EOL .
                        'О себе: ' . $fields['about']['value'] .
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
    }

    private function setField($field_name, $value)
    {
        $fields = $this->register_data->get('fields');

        $fields[$field_name]['is_pending'] = false;
        $fields[$field_name]['is_completed'] = true;
        $fields[$field_name]['value'] = $value;
        $this->register_data->set('fields', $fields);
    }

    public function confirm()
    {
        $fields = $this->register_data->get('fields');

        $chat_id = $this->data->getChatId();
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $chat_id,
                'text' => '<strong>Ваши данные:</strong>' .
                    PHP_EOL .
                    'Имя: ' . $fields['name']['value'] .
                    PHP_EOL .
                    'Предмет: ' . $fields['subject']['value'] .
                    PHP_EOL .
                    'Категория: ' . $fields['category']['value'] .
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
        $fields = $this->register_data->get('fields');
        $name = $fields['name']['value'];
        $subject = $fields['subject']['value'];
        $category = $fields['category']['value'];
        $about = $fields['about']['value'];

        $user = TelegramDatingUser::updateOrCreate([
            'username' => $username
        ], [
            'chat_id' => $chat_id,
            'name' => $name,
            'subject' => $subject,
            'category' => $category,
            'about' => $about
        ]);
        $this->register_data->flush();

        $this->respondWithMessage(' <strong>Отлично!</strong> ' . PHP_EOL . 'Ваши данные сохранены. Мы вам сообщим, когда найдём учеников со схожими интересами.');

        return $user;
    }

    private function getAttributeMessage(string $attribute_name, string $message_type)
    {
        return $this->attribute_messages[$attribute_name][$message_type];
    }

    private function setPendingStatus(string $attribute)
    {
        $fields = $this->register_data->get('fields');
        $fields[$attribute]['is_pending'] = true;
        $this->register_data->set('fields', $fields);

        $this->register_data->save();
    }

    private function checkForResetMessage($message_id)
    {
        $summary_message_id = $this->register_data->get('summary_message_id');
        if ($summary_message_id) {
            $this->register_data->set('reset_bot_message_id', $message_id);
            $this->register_data->save();
        }
    }

    public function name()
    {
        $this->setPendingStatus('name');

        $response = $this->respondWithMessage('Введите имя');
        $message_id = $response->result->message_id;
        $this->checkForResetMessage($message_id);
    }

    public function about()
    {
        $this->setPendingStatus('about');

        $response = $this->respondWithMessage('Напишите о себе');
        $message_id = $response->result->message_id;
        $this->checkForResetMessage($message_id);
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
        $this->checkForResetMessage($message_id);
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
        $this->checkForResetMessage($message_id);
    }
}