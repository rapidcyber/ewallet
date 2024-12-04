<?php

namespace App\Traits;

use Str;

trait WithTSEKYCTrait
{
    /**
     * Handles the generation of request header signature.
     * 
     * @param string $method
     * @param string $path
     * @return array
     */
    private function generate_url_headers(string $method, string $path, ?string $request_id = null)
    {
        $timestamp = now();
        $timestamp = $timestamp->format(DATE_RFC3339);

        $stringToSign = $method . "\n" . $path . "\n" . $timestamp;
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, config('services.trusting_social.secret'), true));

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "TV " . config('services.trusting_social.access') . ":" . $signature,
            'X-TV-Timestamp' => $timestamp,
        ];

        if (empty($request_id) == false) {
            $headers['X-Request-ID'] = $request_id;
        }

        $url = config('services.trusting_social.url') . $path;

        return [$url, $headers];
    }
}
