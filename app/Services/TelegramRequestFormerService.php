<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;
use stdClass;

class TelegramRequestFormerService
{
    private string $api_token;
    private string $base_url;
    private string $full_url;
    private array $parameters;

    public function __construct(string $api_token = '')
    {
        $this->setApiToken($api_token);
        $this->formBaseUrl();
        $this->setParams([]);
    }

    public function setMethodName(string $method_name): TelegramRequestFormerService
    {
        $this->full_url = $this->base_url . '/' . $method_name;

        return $this;
    }

    public function setParams(array $parameters): TelegramRequestFormerService
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function make(string $http_verb): stdClass
    {
        $response = Http::{$http_verb}($this->full_url, $this->parameters);

        return json_decode($response);
    }

    private function formBaseUrl(): void
    {
        $this->base_url = env('TELEGRAM_BASE_API_URL') . 'bot' . $this->api_token;
    }

    private function setApiToken(string $api_token): void
    {
        $this->api_token = $api_token === ''
            ? env('TELEGRAM_API_TOKEN')
            : $api_token;
    }
}