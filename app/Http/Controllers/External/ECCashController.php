<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Traits\WithHttpResponses;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;

class ECCashController extends Controller
{
    use WithHttpResponses;

    /**
     * Summary of generate_ecload_token
     * @throws \Exception
     * @return mixed
     */
    private function generate_token()
    {
        $user_key = openssl_encrypt(
            config('services.ecpay.accounts.eccash.password'),
            'aes-128-cbc',
            config('services.ecpay.crypto.eccash.secret'),
            0,
            config('services.ecpay.crypto.eccash.iv'),
        );

        $username = openssl_encrypt(
            config('services.ecpay.accounts.eccash.username'),
            'aes-128-cbc',
            config('services.ecpay.crypto.eccash.secret'),
            0,
            config('services.ecpay.crypto.eccash.iv'),
        );

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json',
            'user-key' => $user_key,
        ];

        $data = [
            'accountId' => (int) config('services.ecpay.accounts.eccash.account_id'),
            'branchId' => (int) config('services.ecpay.accounts.eccash.branch_id'),
            'username' => $username,
        ];

        $response = Http::withHeaders($headers)->post(
            config('services.ecpay.hosts.eccash') . "/api/Authentication/Login",
            $data,
        );
        if ($response->failed()) {
            throw new Exception("[ECPAY] AUTHENTICATION FAILED : " . $response->reason());
        }

        $data = json_decode($response->body(), true);
        return $data['Token'];
    }

    /**
     * Summary of get
     * @param string $endpoint
     * @param mixed $data
     * @param string $token
     * @return Response
     */
    private function get_request(string $endpoint, $data = [], string $token = '')
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json',
        ];

        if (empty($token) == false) {
            $headers['authorization'] = $token;
        }

        return Http::withHeaders($headers)->get(
            config('services.ecpay.hosts.eccash') . '/' . $endpoint,
            $data,
        );
    }

    /**
     * Summary of services
     * @throws \Exception
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function services()
    {
        try {
            $token = $this->generate_token();
            $response = $this->get_request('/api/v1/ecCash/GetServices', [], $token);

            if ($response->failed()) {
                throw new Exception("[ECPAY] ECCASH SERVICES REQUEST FAILED : " . $response->body());
            }

            $data = json_decode($response->body(), true);
            return $this->success($data['Data']);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
