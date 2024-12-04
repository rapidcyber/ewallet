<?php

namespace App\Traits;

use App\Models\Merchant;
use App\Models\OTP;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithOTP
{
    use WithNumberGeneration, WithSMS;

    public function generate_otp(User|Merchant $entity, $type)
    {
        if (!in_array($type, ['sign_in', 'sign_up', 'change_pass', 'transaction'])) {
            return null;
        }

        $otp = OTP::where([
            'contact' => $entity->phone_number,
            'type' => $type,
        ])->first();

        DB::beginTransaction();
        try {
            if (empty($otp) == false) {
                $otp->delete();
            }

            $otp = $this->generate_otp_code($entity->phone_number, $type);
            $this->sendSMS("Repay OTP \n\n$otp->code is your transaction OTP code\n\nUse this code to authorize your transaction.", $otp->contact, 'transaction');
            DB::commit();

            return [
                'verification_id' => $otp->verification_id,
                'code' => config('app.debug') ? $otp->code : '',
            ];
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);

            return null;
        }
    }
}
