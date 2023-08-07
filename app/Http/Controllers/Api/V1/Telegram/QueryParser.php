<?php

namespace App\Http\Controllers\Api\V1\Telegram;

use Illuminate\Support\Facades\Log;

class QueryParser
{
    private array $arg_names = [];
    private array $args = [];
    private array $callback_queries;
    private string $callback_query;

    public function __construct(array $callback_queries)
    {
        $this->callback_queries = $callback_queries;
    }

    public function setCallbackArgs(string $prefix, string $callback_query)
    {
        $last_delimiter_position = strlen($prefix);
        $args_with_delimiters = substr($callback_query, $last_delimiter_position + 1, strlen($callback_query));

//        $this->setArgNames($callback_query, $prefix);
        $arg_names = [];
        foreach ($this->callback_queries as $callback_query_key => $method_name) {
            if (str_starts_with($callback_query_key, $prefix)) {
                preg_match_all('#\{(.*?)\}#', $callback_query_key, $arg_names);
                $arg_names = $arg_names[1];
            }
        }


//        $this->setArgs($args_with_delimiters);
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

        return $args;
    }

    private function setArgNames(string $callback_query, string $prefix)
    {
        foreach ($this->callback_queries as $callback_query => $method_name) {
            if (str_starts_with($callback_query, $prefix)) {
                preg_match_all('#\{(.*?)\}#', $callback_query, $arg_names);
                $this->arg_names = $arg_names[1];
            }
        }
    }

    private function setArgs(string $args_with_delimiters)
    {
        foreach ($this->arg_names as $arg_name) {
            if (substr_count($args_with_delimiters, '-') === 0) {
                $this->args[$arg_name] = $args_with_delimiters;

                break;
            }

            $delimiter_position = strpos($args_with_delimiters, '-');
            $this->args[$arg_name] = substr($args_with_delimiters, 0, $delimiter_position);
            $args_with_delimiters = substr($args_with_delimiters, $delimiter_position + 1, strlen($args_with_delimiters));
        }
    }
}