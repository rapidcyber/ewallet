<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\UPay\AutopostRequest;

class UPayController extends Controller
{
    // @TODO:
    public function auto_post(AutopostRequest $request)
    {
        $validated = $request->validated();

        return response()->json([
            'message' => 'success',
        ], 200);
    }
}
