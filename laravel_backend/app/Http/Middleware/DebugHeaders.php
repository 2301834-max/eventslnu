<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        Log::info('Response Headers', [
            'url' => $request->path(),
            'set_cookie_headers' => $response->headers->get('Set-Cookie'),
            'all_headers' => $response->headers->all(),
        ]);
        
        return $response;
    }
}
