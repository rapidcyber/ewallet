<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRegisterPushTokenRequest;
use App\Http\Requests\Auth\AuthSignInRequest;
use App\Http\Requests\Auth\AuthVerifyOTPRequest;
use App\Http\Requests\Auth\AuthVerifyPinRequest;
use App\Models\AuthAttempt;
use App\Models\OTP;
use App\Models\User;
use App\Traits\WithHttpResponses;
use App\Traits\WithNumberGeneration;
use App\Traits\WithSMS;
use App\Traits\WithValidPhoneNumber;
use Auth;
use DB;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use WithHttpResponses, WithSMS, WithValidPhoneNumber, WithNumberGeneration;



    /**
     * Check if the user is restricted to authenticate.
     * 
     * @param \App\Models\User $user
     * @return bool
     */
    private function is_restricted(Authenticatable|User $user): bool
    {
        $attempt = AuthAttempt::firstOrNew(['user_id' => $user->id]);
        if (empty($attempt->restricted_until) == false && now()->isBefore($attempt->restricted_until)) {
            return true;
        }

        return false;
    }

    /**
     * Invoking this function means the User already attempted and failed to authenticate.
     * 
     * Returns `true` if the maximum attempt threshold is reached, `false` otherwise.
     * 
     * @param \App\Models\User $user
     * @return bool 
     */
    private function is_max_auth_attempt(AuthAttempt $attempt): bool
    {
        $attempt->count += 1;
        if ($attempt->count >= 3) {
            $attempt->restricted_until = now()->addMinutes(config('app.debug') ? 1 : 30);
        }
        $attempt->save();
        $restricted = $this->is_restricted($attempt->user);
        if ($restricted) {
            return true;
        }

        return false;
    }

    /**
     * Sign-in with `phone_number` and `password`.
     * Generates `verification_id` and `otp` for `verify_otp` function.
     * 
     * @param \App\Http\Requests\Auth\AuthSignInRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function sign_in(AuthSignInRequest $request)
    {
        $validated = $request->validated();
        $phone_number = $validated['phone_number'];
        $password = $validated['password'];

        $user = User::where('phone_number', str_replace('+', '', $phone_number))->first();
        if (empty($user)) {
            return $this->errorFromCode('invalid_credentials');
        }

        $restricted = $this->is_restricted($user);
        if ($restricted) {
            return $this->errorFromCode('auth_restricted');
        }

        $otp = OTP::where(['contact' => $phone_number, 'type' => 'sign_in'])->first();
        if (empty($otp) == false) {
            $otp->delete();
        }

        $auth = auth()->attempt(['phone_number' => $user->phone_number, 'password' => $password]);
        $attempt = AuthAttempt::firstOrNew(['user_id' => $user->id]);

        if ($auth == false) {
            $is_max = $this->is_max_auth_attempt($attempt);
            if ($is_max === true) {
                return $this->errorFromCode('max_signin_attempt');
            }

            return $this->errorFromCode('invalid_credentials');
        } else {
            $attempt->delete();
        }

        DB::beginTransaction();
        try {
            $otp = $this->generate_otp_code($phone_number, 'sign_in');
            $this->sendSMS(
                "Repay OTP \n\n$otp->code is your verification code\n\nUse this code to verify your phone number.",
                $otp->contact,
                'sign_up_otp'
            );
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
     * Validates OTP code.
     * 
     * - generate `verification_id` and `otp` code with `sign_in` function.
     * 
     * @param \App\Http\Requests\Auth\AuthVerifyOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_otp(AuthVerifyOTPRequest $request)
    {
        $validated = $request->validated();
        $verification_id = $validated['verification_id'];
        $code = $validated['code'];

        $otp = OTP::where([
            'verification_id' => $verification_id,
            'code' => $code,
            'type' => 'sign_in',
            'verified_at' => null
        ])->first();

        if (empty($otp)) {
            return $this->error(config('constants.messages.invalid_otp'), 499);
        }

        if (now()->isAfter($otp->expires_at)) {
            $otp->delete();
            return $this->error(config('constants.messages.invalid_otp'), 499);
        }

        try {
            $user = User::where('phone_number', str_replace('+', '', $otp->contact))->first();
            $otp->delete();

            if (empty($u->phone_verified_at)) {
                $user->phone_verified_at = now();
                $user->save();
            }

            // $user->tokens()->update(['revoked' => true]);
            $user->tokens()->delete();
            $token = $user->createToken('auth-pin', ['auth-pin'])->accessToken;
            return $this->success([
                'token' => $token,
                'name' => $user->profile->first_name . ' ' . $user->profile->surname,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Generate a new personal access token for successful PIN sign-in
     * 
     * @param \App\Http\Requests\Auth\AuthVerifyPinRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_pin(AuthVerifyPinRequest $request)
    {
        $validated = $request->validated();
        $pin = $validated['pin'];

        $user = Auth::guard('api')->user();
        $restricted = $this->is_restricted($user);
        if ($restricted) {
            return $this->errorFromCode('auth_restricted');
        }

        $attempt = AuthAttempt::firstOrNew(['user_id' => $user->id]);
        if (Hash::check($pin, $user->pin) == false) {
            $is_max = $this->is_max_auth_attempt($attempt);
            if ($is_max === true) {
                $user->tokens()->update(['revoked' => true]);
                $user->fcm_token = '';
                $user->save();
                return $this->errorFromCode('max_signin_attempt');
            }

            return $this->errorFromCode('invalid_pin');
        } else {
            $attempt->delete();
        }

        $user->tokens()->where('name', 'repay-app')->update(['revoked' => true]);
        $token = $user->createToken('repay-app', ['repay-app'])->accessToken;
        return $this->success($token);
    }

    /**
     * Summary of generate_token
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_token()
    {
        $user = Auth::guard('api')->user();
        $user->tokens()->where('name', 'repay-app')->update(['revoked' => true]);
        $token = $user->createToken('repay-app', ['repay-app'])->accessToken;
        return $this->success($token);
    }

    /**
     * Summary of register_push_token
     * @param \App\Http\Requests\Auth\AuthRegisterPushTokenRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function register_push_token(AuthRegisterPushTokenRequest $request)
    {
        $validated = $request->validated();

        $push_token = $validated['push_token'];
        if (empty($push_token)) {
            return $this->success();
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $user->fcm_token = $push_token;
            $user->save();
            DB::commit();

            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->successWithException($ex);
        }
    }

    /**
     * Sign outs a user by deleting it's access token
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function sign_out()
    {
        $user = auth()->user();

        $user->tokens()->update(['revoked' => true]);
        $user->fcm_token = '';
        $user->save();

        return $this->success('success');
    }
}
