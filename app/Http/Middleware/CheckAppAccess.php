<?php

namespace App\Http\Middleware;

use App\Models\AppAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $access_key = $request->header('x-access-key');
        $exists = AppAccess::where('access', $access_key)->exists();
        if ($exists == false) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => 'Invalid Credentials',
            ], 403);
        } 

        return $next($request);
    }
}
