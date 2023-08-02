<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BadApiVersion extends Exception
{
    public function throw(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errorCode' => 404,
            'message' => 'Bad Api version,' . ' use ' . config('app.api_version') . ' instead'
        ], 404);
    }
}
