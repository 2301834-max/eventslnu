<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Incoming request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'session_id' => session()->getId(),
        ]);

        $response = $next($request);

        Log::info('Outgoing response', [
            'path' => $request->path(),
            'status' => $response->status(),
            'set_cookie' => $response->headers->get('Set-Cookie'),
            'session_id' => session()->getId(),
        ]);

        return $response;
    }
}
