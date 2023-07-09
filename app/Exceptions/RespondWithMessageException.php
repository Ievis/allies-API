<?php

namespace App\Exceptions;

use Exception;

class RespondWithMessageException extends Exception
{
    public $response;

    public function __construct($message)
    {
        $this->response = response()->json([
            'success' => false,
            'message' => $message,
        ], 400)
            ->header('Charset', 'utf-8')
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        parent::__construct();
    }
}
