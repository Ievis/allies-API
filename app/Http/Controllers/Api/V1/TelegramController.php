<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TelegramRequestFormerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    private TelegramRequestFormerService $telegram_request_service;
    private array $commands = [
        '/start' => 'start',
    ];

    private int $chat_id;
    private string $text;

    public function __construct()
    {
        $this->telegram_request_service = new TelegramRequestFormerService();
    }

    public function index(Request $request)
    {
        $request_body = $request->getContent();
        $data = json_decode($request_body);
        $message = $data->message;

        $this->chat_id = $message->chat->id;
        $this->text = $message->text;

        if (array_key_exists($this->text, $this->commands)) {
            $method_name = $this->commands[$this->text];
            $this->{$method_name}();
        }
    }

    public function start()
    {
        $this->telegram_request_service
            ->setMethodName('sendMessage')
            ->setParams([
                'chat_id' => $this->chat_id,
                'text' => 'Привет!'
            ])
            ->make('GET');
    }
}
