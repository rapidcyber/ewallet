<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\WithHttpResponses;
use App\Traits\WithImageUploading;
use App\Traits\WithNotification;
use App\Traits\WithPushNotification;
use DB;
use Exception;

/**
 * Summary of TestController
 */
class TestController extends Controller
{

    use WithHttpResponses, WithPushNotification, WithImageUploading, WithNotification;


    public function test()
    {


        DB::beginTransaction();

        try {


            $this->alert(
                User::find(1),
                "notification",
                '',
                "Test",
            );


            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->successWithException($ex);
        }
    }
}
