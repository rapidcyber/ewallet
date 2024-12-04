<?php

use App\Http\Middleware\CheckAppAccess;
use App\Http\Middleware\CheckBookingType;
use App\Http\Middleware\CheckMerchant;
use App\Http\Middleware\CheckPartnerCredentials;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckSession;
use App\Http\Middleware\ECPayWebhookMiddleware;
use App\Http\Middleware\HasKYC;
use App\Http\Middleware\HasProfileUpdateRequest;
use App\Http\Middleware\IsSystemAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Spatie\Csp\AddCspHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'scopes' => CheckScopes::class,
            'scope' => CheckForAnyScope::class,
            'partners' => CheckPartnerCredentials::class,

            'isSystemAdmin' => IsSystemAdmin::class,
            'checkSession' => CheckSession::class,
            'role' => CheckRole::class,
            'merchant' => CheckMerchant::class,
            'bookingtype' => CheckBookingType::class,
            'ecpayWebhook' => ECPayWebhookMiddleware::class,
            'hasKYC' => HasKYC::class,
            'hasProfileUpdateRequest' => HasProfileUpdateRequest::class,
            'add-csp-headers' => AddCspHeaders::class,
            'checkAppAccess' => CheckAppAccess::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
