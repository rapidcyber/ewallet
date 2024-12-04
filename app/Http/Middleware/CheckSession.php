<?php

namespace App\Http\Middleware;

use App\Traits\WithHttpResponses;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{

    use WithHttpResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = Auth::guard('api')->check();
        if ($auth == false) {
            return $this->errorFromCode('invalid_session');
        }

        auth()->setUser(Auth::guard('api')->user());
        return $next($request);
    }
}
