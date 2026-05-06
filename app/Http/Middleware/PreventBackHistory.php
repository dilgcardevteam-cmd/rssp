<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (Auth::guard('web')->check() || Auth::guard('admin')->check()) {
            $headers = $response->headers;
            $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $headers->set('Pragma', 'no-cache');
            $headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }
}
