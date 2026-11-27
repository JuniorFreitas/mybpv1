<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

/**
 * Alias legado do app: CSRF + verificação de origem (Sec-Fetch-Site) via PreventRequestForgery.
 *
 * @deprecated Renomear consumidores para App\Http\Middleware\PreventRequestForgery quando conveniente.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF / origin verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
