<?php

namespace App\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidationException extends HttpResponseException
{
    public function __construct(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => array_map(function ($item) {
                return $item[0];
            }, $validator->errors()->toArray())
        ], 400)
            ->header('Charset', 'utf-8')
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        parent::__construct($response);
    }
}
