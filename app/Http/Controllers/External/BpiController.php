<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BpiController extends Controller
{
    public function callback(Request $request)
    {
        if ($request->has('code')) {
            Log::info($request->all());
            $code = $request->code;

            $oath_base_uri = config('services.bpi.host_oauth_url');
            $client_id = config('services.bpi.client_id');
            $client_secret = config('services.bpi.client_secret');

            $response = Http::asForm()->post($oath_base_uri, [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error($response->body());
            }

            $response = $response->json();
            Log::info($response);
        }
    }

    public function login()
    {
        $oath_base_uri = config('services.bpi.host_oauth_url');
        $client_id = config('services.bpi.client_id');
        $callback = config('services.bpi.callback_url');
        $scope = 'transactionalAccountsforRecurringDebits recurringDebits recurringDebitsDeLink';

        $url = $oath_base_uri . '/authorize?response_type=code&scope=' . $scope . '&client_id=' . $client_id . '&redirect_uri=' . $callback;

        return redirect($url, 302);
    }
}
