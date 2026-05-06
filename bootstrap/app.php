<?php

use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\UseRequestAssetOrigin;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login'); // redirect to login when some user is not logged in
        $middleware->append(SecureHeaders::class);
        $middleware->append(PreventBackHistory::class);
        $middleware->web(replace: [
            ValidateCsrfToken::class => VerifyCsrfToken::class,
        ]);
        $middleware->web(append: [
            UseRequestAssetOrigin::class,
        ]);
        $middleware->alias([
            'admin.ability' => \App\Http\Middleware\EnsureAdminAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'Upload failed because the total file size is too large. Please upload fewer files or smaller files and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()
                ->withInput($request->except('cert_uploads'))
                ->withErrors(['cert_uploads' => $message]);
        });
    })->create();
