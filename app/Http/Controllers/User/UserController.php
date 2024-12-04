<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\PasswordResetOTPRequest;
use App\Http\Requests\User\PasswordResetRequest;
use App\Http\Requests\User\UserPreviewRequest;
use App\Http\Requests\User\VerifyEmailRequest;
use App\Mail\RepayMail;
use App\Models\OTP;
use App\Models\PasswordResetCode;
use App\Models\User;
use App\Traits\WithHttpResponses;
use App\Traits\WithMail;
use App\Traits\WithNumberGeneration;
use App\Traits\WithSMS;
use App\Traits\WithValidPhoneNumber;
use DB;
use Exception;

class UserController extends Controller
{
    use WithHttpResponses, WithValidPhoneNumber, WithNumberGeneration, WithMail, WithSMS;


    /**
     * Summary of details
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details()
    {
        $user = auth()->user();
        $user->profile;
        $user->roles;
        $user->linked_rh_account;

        $user->pending_update = !empty($user->profile_update_request) && !empty($user->profile_update_request->selfie_image_id);
        $user->kyced = !empty($user->kyc) && !empty($user->kyc->selfie_image_id);
        unset($user->kyc, $user->profile_update_request);
        return $this->success($user);
    }

    /**
     * Summary of preview
     * @param \App\Http\Requests\User\UserPreviewRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function preview(UserPreviewRequest $request)
    {
        $validated = $request->validated();

        $iso = $validated['phone_iso'];
        $phone_number = $validated['phone_number'];

        $phone_info = $this->phonenumber_info($phone_number, $iso);
        if ($phone_info == false) {
            return $this->error('Invalid phone number', 499);
        }

        $user = User::where([
            'phone_number' => $phone_info->getCountryCode() . $phone_info->getNationalNumber(),
        ]);

        if ($user->exists() == false) {
            return $this->error('Number is not registered to repay.', 499);
        }

        $user = $user->first();

        $parts = explode(' ', $user->name);
        foreach ($parts as &$part) {
            $obs = str_repeat('*', 3);
            $part = str_replace('.', '', substr_replace($part, $obs, 1));
        }
        $name = implode(' ', $parts);
        return $this->success($name);
    }

    /**
     * Summary of generate_email_otp
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_email_otp()
    {
        /// for resending
        $otp = OTP::where([
            'contact' => auth()->user()->email,
            'type' => 'sign_up',
        ])->first();

        DB::beginTransaction();
        try {
            if (empty($otp) == false) {
                $otp->delete();
            }

            $otp = $this->generate_otp_code(auth()->user()->email, 'sign_up');
            $this->sendMail($otp->contact, new RepayMail(
                "Repay Email Verification",
                [
                    "Repay OTP",
                    "$otp->code is your verification code",
                    "Use this code to verify your Email address",
                ],
            ));
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
     * Summary of verify_email
     * @param \App\Http\Requests\User\VerifyEmailRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_email(VerifyEmailRequest $request)
    {

        $validated = $request->validated();
        $verification_id = $validated['verification_id'];
        $code = $validated['code'];

        $otp = OTP::where('verification_id', $verification_id)
            ->where('code', $code)
            ->where('verified_at', null)
            ->first();

        if (empty($otp)) {
            return $this->error(config('constants.messages.invalid_otp'), 499);
        }

        if (now()->isAfter($otp->expires_at)) {
            $otp->delete();
            return $this->error(config('constants.messages.invalid_otp'), 499);
        }


        DB::beginTransaction();
        try {
            auth()->user()->email_verified_at = now();
            auth()->user()->save();
            $otp->delete();

            DB::commit();

            return $this->success(['verified' => true]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of rh_unlink
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function rh_unlink()
    {
        if (empty(auth()->user()->linked_rh_account) == false) {
            auth()->user()->linked_rh_account->delete();
        }
        return $this->success();
    }

    /**
     * Summary of reset_password_otp
     * @param \App\Http\Requests\User\PasswordResetOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function reset_password_otp(PasswordResetOTPRequest $request)
    {
        $validated = $request->validated();
        $phone_number = $validated['phone_number'];

        $verification_id = bin2hex(date_format(now(), 'md') . mt_rand(100000, 999999));
        $code = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);

        DB::beginTransaction();
        try {
            $passwordCode = PasswordResetCode::firstOrNew([
                'contact' => $phone_number,
            ]);

            $passwordCode->code = $code;
            $passwordCode->verification_id = $verification_id;
            $passwordCode->expires_at = now()->addMinutes(5);
            $passwordCode->save();

            DB::commit();
            $this->sendSMS("Your password reset code is $code", $phone_number, 'verification_otp');

            return $this->success([
                'verification_id' => $passwordCode->verification_id,
                'code' => config('app.debug') ? $passwordCode->code : '',
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of reset_password
     * @param \App\Http\Requests\User\PasswordResetRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function reset_password(PasswordResetRequest $request)
    {
        $validated = $request->validated();

        $verification_id = $validated['verification_id'];
        $code = $validated['code'];

        $reset_code = PasswordResetCode::where([
            'verification_id' => $verification_id,
            'code' => $code,
        ])->first();

        if (empty($reset_code)) {
            return $this->error('Invalid Verification Code', 499);
        }

        if (now()->isAfter($reset_code->expires_at)) {
            $reset_code->delete();
            return $this->error('Verification code is expired', 499);
        }

        try {
            $reset_code->delete();
            return $this->success(['message' => 'success']);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
