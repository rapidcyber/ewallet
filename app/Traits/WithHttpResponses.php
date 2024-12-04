<?php

namespace App\Traits;

use App\Helpers\LogHelper;
use Exception;

trait WithHttpResponses
{
    protected function success($data = null, $message = 'success', $code = 200)
    {
        $response = ['message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
    protected function error($message, $code, $data = null)
    {
        $info = ['error' => $message];
        if (!empty($data)) {
            $info['data'] = $data;
        }

        return response()->json($info, $code);
    }

    protected function exception(Exception $e)
    {
        LogHelper::exception($e);
        return response()->json([
            'message' => 'Something went wrong, please try again later. [code: RPY599]',
        ], 500);
    }

    protected function successWithException(Exception $e)
    {
        LogHelper::exception($e);
        return response()->json(['message' => 'success'], 200);
    }


    /**
     * Return an error using the error code defined on the constants config.
     * 
     * @param string $errCode
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function errorFromCode(string $errCode)
    {
        $error = config("constants.errors.$errCode");
        return response()->json([
            'code' => $errCode,
            'message' => $error['message'],
        ], $error['code']);
    }
}
