<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use stdClass;

class TelegramRequestService
{
    private string $api_token;
    private string $base_url;
    private string $full_url;
    private array $parameters;

    public function __construct(string $api_token = '')
    {
        $this->setApiToken($api_token);
        $this->formBaseUrl();
        $this->setParams();
    }

    public function setMethodName(string $method_name): TelegramRequestService
    {
        $this->full_url = $this->base_url . '/' . $method_name;

        return $this;
    }

    public function setParams(array $parameters = []): TelegramRequestService
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function make(string $http_verb = 'GET'): stdClass
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