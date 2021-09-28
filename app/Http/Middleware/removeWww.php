<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class removeWww
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (substr($request->header('host'), 0, 4) == 'www.') {
            $request->headers->set('host', 'mybp.com.br');
            return \Redirect::to($request->path());
        }

        return $next($request);
    }
}
