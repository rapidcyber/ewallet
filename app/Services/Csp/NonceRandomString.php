<?php

namespace App\Services\Csp;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Spatie\Csp\Nonce\NonceGenerator;

class NonceRandomString implements NonceGenerator
{
    public function generate(): string
    {
        $myNonce = empty($_SERVER['CSP_NONCE']) ? Str::random(16) : $_SERVER['CSP_NONCE']; // determine the value for `$myNonce` however you want
    
        Vite::useCspNonce($myNonce);
        
        return $myNonce;
    }
}
