<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

class LogHelper
{
    public static function exception(Exception $exception)
    {
        return Log::error($exception->getMessage(), [
            'trace' => $exception->getTraceAsString(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
        ]);
    }

    public static function message(string $message) {
        return Log::error($message);
    }
}