<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $auth_user_roles = auth()->user()->roles->pluck('slug')->toArray();
        
        foreach ($roles as $role) {
            if (in_array($role, $auth_user_roles)) {
                return $next($request);
            }
        }

        return redirect('/');
    }
}
