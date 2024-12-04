<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Http;

trait WithECPayFunctions
{
    private function generate_ecpay_token()
    {
        $response = $this->ecpay_post_request(
            '/api/Authentication/Login',
            [
                'AccountId' => (int) config('services.ecpay.accounts.onepay.account_id'),
                'BranchId' => (int) config('services.ecpay.accounts.onepay.branch_id'),
                'UserName' => config('services.ecpay.accounts.onepay.username'),
                'Password' => config('services.ecpay.accounts.onepay.password'),
            ]
        );

        if ($response->failed()) {
            throw new Exception("[ECPAY] AUTHENTICATION FAILED");
        }

        $data = json_decode($response->body(), true);
        return $data['Token'];
    }

    private function ecpay_post_request(string $endpoint, $data = [], $token = '')
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json'
        ];

        if (empty($token) == false) {
            $headers['authorization'] = "Bearer $token";
        }

        return Http::withHeaders($headers)
            ->post(
                config('services.ecpay.hosts.onepay') . '/' . $endpoint,
                $data,
            );
    }

    private function ecpay_get_request(string $endpoint, $data = [], string $token = '')
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json',
        ];

        if (empty($token) == false) {
            $headers['authorization'] = "Bearer $token";
        }

        return Http::withHeaders($headers)->get(
            config('services.ecpay.hosts.onepay') . '/' . $endpoint,
            $data,
        );
    }
}
