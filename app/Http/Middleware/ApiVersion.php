<?php

namespace App\Http\Middleware;

use App\Exceptions\BadApiVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    public function handle(Request $request, Closure $next, $api_version): Response
    {
        if ($api_version !== config('app.api_version')) throw new BadApiVersion();

        return $next($request);
    }
}
