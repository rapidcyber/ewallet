<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMerchant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $merchant_account = $request->route('merchant');

        $employee = $merchant_account->employees()
            ->where('user_id', auth()->id())
            ->exists();

        if ($employee == false) {
            abort(404);
        }

        return $next($request);
    }
}
