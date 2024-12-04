<?php

namespace App\Services\Csp;

use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Nonce\NonceGenerator;

class ViteNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        return Vite::cspNonce();
    }
}
