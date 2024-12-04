<?php

namespace App\Http\Middleware;

use App\Traits\WithHttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasKYC
{
    use WithHttpResponses;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->user()->kyc()->exists() == false) {
            return $this->error('Please initiate KYC process first', 499);
        }
        return $next($request);
    }
}
