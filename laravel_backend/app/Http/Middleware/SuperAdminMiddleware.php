<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('super-admin.login');
        }

        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access - super admin only');
        }

        return $next($request);
    }
}
