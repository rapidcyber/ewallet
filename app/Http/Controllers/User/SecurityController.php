<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\ChangePinRequest;
use App\Http\Requests\Security\ChangePasswordRequest;
use App\Models\OTP;
use App\Traits\WithHttpResponses;
use App\Traits\WithNumberGeneration;
use App\Traits\WithSMS;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{

    use WithHttpResponses, WithNumberGeneration, WithSMS;

    /**
     * Generate OTP for changing password verification
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_otp()
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $otp = $this->generate_otp_code($user->phone_number, 'change_pass');
            $this->sendSMS("Repay OTP \n\n$otp->code is your verification code\n\nUse this code to verify your phone number.", "+$otp->contact", 'sign_up_otp');
            DB::commit();
            return $this->success([
                'verification_id' => $otp->verification_id,
                'code' => config('app.debug') ? $otp->code : '',
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Change user password
     * 
     * @param \App\Http\Requests\Security\ChangePasswordRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function change_password(ChangePasswordRequest $request)
    {
        $validated = $request->validated();

        $verification_id = $validated['verification_id'];
        $password = $validated['password'];

        $otp = OTP::where([
            'verification_id' => $verification_id,
            'contact' => auth()->user()->phone_number,
            'type' => 'change_pass',
        ])->first();

        if (empty($otp) || $otp->code !== $validated['code']) {
            return $this->error('Invalid verification code', 499);
        }

        try {
            DB::transaction(function () use ($otp, $password) {
                auth()->user()->update([
                    'password' => Hash::make($password),
                ]);

                $otp->delete();
            });

            auth()->user()->tokens()->update(['revoked' => true]);
            return $this->success('Password changed');
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Change user PIN
     * 
     * @param \App\Http\Requests\Security\ChangePinRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function change_pin(ChangePinRequest $request)
    {
        $validated = $request->validated();

        $verification_id = $validated['verification_id'];
        $code = $validated['code'];
        $pin = $validated['pin'];

        $otp = OTP::where([
            'verification_id' => $verification_id,
            'code' => $code,
            'contact' => auth()->user()->phone_number,
        ])->first();

        if (empty($otp)) {
            return $this->error('Invalid verification code', 499);
        }

        try {
            DB::transaction(function () use ($otp, $pin) {
                auth()->user()->update([
                    'pin' => Hash::make($pin),
                ]);

                $otp->delete();
            });

            return $this->success('Pin changed');
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
