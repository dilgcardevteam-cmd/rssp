<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UseRequestAssetOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        URL::useAssetOrigin($request->root());

        return $next($request);
    }
}
