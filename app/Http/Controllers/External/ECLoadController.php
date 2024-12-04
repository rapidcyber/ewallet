<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\ECPay\ECLoadTransactRequest;
use App\Http\Requests\ECPay\ECLoadVariantRequest;
use App\Traits\WithHttpResponses;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;
use Illuminate\Http\Request;

class ECLoadController extends Controller
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
            config('services.ecpay.accounts.ecload.password'),
            'aes-128-cbc',
            config('services.ecpay.crypto.ecload.secret'),
            0,
            config('services.ecpay.crypto.ecload.iv'),
        );

        $username = openssl_encrypt(
            config('services.ecpay.accounts.ecload.username'),
            'aes-128-cbc',
            config('services.ecpay.crypto.ecload.secret'),
            0,
            config('services.ecpay.crypto.ecload.iv'),
        );

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json',
            'user-key' => $user_key,
        ];

        $data = [
            'accountId' => (int) config('services.ecpay.accounts.ecload.account_id'),
            'branchId' => (int) config('services.ecpay.accounts.ecload.branch_id'),
            'username' => $username,
        ];

        $response = Http::withHeaders($headers)->post(
            config('services.ecpay.hosts.ecload') . "/ecLoad/auth/login",
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
    private function get_request(
        string $endpoint,
        $data = [],
        string $token = '',
    ) {
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
            config('services.ecpay.hosts.ecload') . '/' . $endpoint,
            $data,
        );
    }

    /**
     * Summary of balance
     * @throws \Exception
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function balance() {
        try {
            $token = $this->generate_token();
            $response = $this->get_request('/ecLoad/v1/inquire/wallet', [], $token);

            if ($response->failed()) {
                throw new Exception("[ECPAY] WALLET GET REQUEST FAILED : " . $response->body());
            }

            $data = json_decode($response->body(), true);
            return $this->success($data);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of telcos
     * @throws \Exception
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function telcos()
    {
        try {
            $token = $this->generate_token();
            $response = $this->get_request('/ecLoad/v1/telco', [], $token);

            if ($response->failed()) {
                throw new Exception("[ECPAY] TELCO LIST GET REQUEST FAILED : " . $response->body());
            }

            $telcos = json_decode($response->body(), true);
            return $this->success($telcos);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of variants
     * @param \App\Http\Requests\ECPay\ECLoadVariantRequest $request
     * @throws \Exception
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function variants(ECLoadVariantRequest $request)
    {
        $validated = $request->validated();
        try {
            $token = $this->generate_token();
            $response = $this->get_request(
                '/ecLoad/v1/telco/variants',
                ['ServiceName' => $validated['service_name']],
                $token,
            );

            if ($response->failed()) {
                throw new Exception("[ECPAY] TELCO VARIANT LIST GET REQUEST FAILED : " . $response->body());
            }

            $variants = json_decode($response->body(), true);
            return $this->success($variants);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
