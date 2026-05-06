<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Disable readable XSRF-TOKEN cookie issuance to avoid exposing CSRF token to JS.
     *
     * @var bool
     */
    protected $addHttpCookie = false;
}

