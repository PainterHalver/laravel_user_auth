<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfirmCsrfInQueryState
{
    /**
     * Make sure request has a correct query param `state` set to csrf.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! isset($request['state']) || $request['state'] !== csrf_token()) {
            abort(401, 'Invalid state parameter');
        }

        return $next($request);
    }
}
