<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UPayAutopostMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $whitelist = explode(",", config('services.unionbank.upay_whitelist'));
        if (app()->environment(['production']) == false) {
            $system_whitelist = explode(",", config('services.system.whitelist'));
            array_push($whitelist, ...$system_whitelist);
        }

        if (in_array($request->ip(), $whitelist) == false) {
            return response(null, 404);
        }

        $apiKey = config('services.unionbank.upay_api_key');
        $apiKeyIsValid = (
            !empty($apiKey)
            && $request->header('x-api-key') == $apiKey
        );

        if ($apiKeyIsValid == false) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => 'Invalid API Key',
            ], 403);
        }

        return $next($request);
    }
}
