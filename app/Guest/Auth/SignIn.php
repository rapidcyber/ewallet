<?php

namespace App\Guest\Auth;

use App\Models\AuthAttempt;
use App\Models\OTP;
use App\Models\User;
use App\Traits\WithSMS;
use Aws\Exception\AwsException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SignIn extends Component
{
    use WithSMS;

    public $username = '';
    public $password = '';
    public $otp = '';
    public $errorMessage = '';
    public $invalidOTP = false;
    public $pin;

    #[Locked]
    public $verification_id = '';
    #[Locked]
    public $otp_valid = null;
    #[Locked]
    public $otp_sent = false;
    #[Locked]
    public $otp_expires_at = null;

    #[Locked]
    public $signup = false;

    private function credentials($arr)
    {
        if (is_numeric($arr['username'])) {
            return ['phone_number' => $arr['username'], 'password' => $arr['password']];
        } elseif (filter_var($arr['username'], FILTER_VALIDATE_EMAIL)) {
            return ['email' => $arr['username'], 'password' => $arr['password']];
        }

        return ['username' => $arr['username'], 'password' => $arr['password']];
    }

    public function switchView()
    {
        $this->signup = !$this->signup;
    }

    public function updatedUsername()
    {
        $this->errorMessage = '';
    }

    public function updatedPassword()
    {
        $this->errorMessage = '';
    }

    public function updatedOtp()
    {
        $this->invalidOTP = false;
        $this->errorMessage = '';
    }

    public function submit()
    {
        if ($this->verification_id) {
            return;
        }

        $creds = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $validator = Validator::make($creds, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorMessage = 'Invalid Credentials';
        }

        $user = User::where('email', $this->username)
            ->orWhere('phone_number', $this->username)
            ->first();

        /// User not found
        if (empty($user)) {
            return $this->errorMessage = 'Invalid Credentials';
        }

        /// Check attempts
        $attempt = AuthAttempt::where('user_id', $user->id)->first();
        /// User account is temporarity locked due to maximum attempt
        if (!empty($attempt) && !empty($attempt->restricted_until) && now()->isBefore($attempt->restricted_until)) {
            if (!$attempt->message_sent) {
                $this->sendSMS(config('constants.messages')['maximum_signin_attempt'], (string) $user->phone_number, 'sign_in_max_attempt');
                $attempt->message_sent = 1;
                $attempt->save();
            }

            return $this->errorMessage = 'Maximum sign-in attempts reached, please try again later';
        }

        $credentials = $this->credentials($creds);
        if (auth()->validate($credentials)) {
            if ($user->profile->status == 'deactivated') {
                return $this->errorMessage = 'This account is suspended';
            }

            $otp = new OTP;
            $otp->code = str_pad(strval(random_int(000000, 999999)), 6, '0', STR_PAD_LEFT);
            $otp->verification_id = bin2hex(date_format(now(), 'md') . mt_rand(100000, 999999));
            $otp->expires_at = now()->addMinutes(3);
            $otp->contact = $user->phone_number;
            $this->verification_id = $otp->verification_id;

            try {
                DB::transaction(function () use ($otp, $attempt) {
                    if (!empty($attempt)) {
                        $attempt->delete();
                    }

                    OTP::where('contact', $otp->contact)->delete(); // delete previously generated otp for the same contact
                    $otp->save();
                });
                if (config('app.env') == 'alpha' || config('app.env') == 'staging' || config('app.env') == 'local') {
                    $this->otp = $otp->code;
                } else {
                    $sms_result =  $this->sendSMS("Repay OTP \n\n$otp->code is your verification code\n\nUse this code to verify your phone number.", '+' . $otp->contact, 'sign_in_otp');
                }
            } catch (AwsException | Exception $ex) {
                return $this->errorMessage = $ex->getMessage();
            }

            $this->otp_sent = true;
            $this->otp_expires_at = $otp->expires_at;
        } else {
            try {
                /// Record attempt
                DB::transaction(function () use ($user) {
                    $attempt = AuthAttempt::firstOrNew(['user_id' => $user->id]);
                    /// Reset attempt if recent_attempt_at is passed the given treshhold
                    /// 60 * 10 = 10 minutes
                    if (now()->diffInSeconds($attempt->updated_at) > (60 * 10)) {
                        $attempt->count = 0;
                        $attempt->message_sent = 0;
                    }

                    /// Reached max attempt
                    if ($attempt->count >= 2) {
                        $attempt->restricted_until = now()->addMinutes(30);
                    }

                    /// Update attempt record
                    $attempt->updated_at = now();
                    $attempt->count += 1;
                    $attempt->save();
                });

                return $this->errorMessage = 'Invalid Credentials';
            } catch (AwsException | Exception $ex) {
                return $this->errorMessage = $ex->getMessage();
            }
        }
    }

    public function resendOTP()
    {
        if (!$this->verification_id) {
            return;
        }

        if ($this->otp_sent && $this->otp_expires_at > now()) {
            return;
        }

        $this->reset([
            'otp_sent',
            'otp_expires_at',
        ]);

        $creds = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $validator = Validator::make($creds, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorMessage = 'Invalid Credentials';
        }

        $user = User::where('email', $this->username)
            ->orWhere('phone_number', $this->username)
            ->first();

        /// User not found
        if (empty($user)) {
            return $this->errorMessage = 'Invalid Credentials';
        }

        /// Check attempts
        $attempt = AuthAttempt::where('user_id', $user->id)->first();
        /// User account is temporarity locked due to maximum attempt
        if (!empty($attempt) && !empty($attempt->restricted_until) && now()->isBefore($attempt->restricted_until)) {
            if (!$attempt->message_sent) {
                $this->sendSMS(config('constants.messages')['maximum_signin_attempt'], (string) $user->phone_number, 'sign_in_max_attempt');
                $attempt->message_sent = 1;
                $attempt->save();
            }

            return $this->errorMessage = 'Maximum sign-in attempts reached, please try again later';
        }

        $credentials = $this->credentials($creds);
        if (auth()->validate($credentials)) {
            if ($user->profile->status == 'deactivated') {
                return $this->errorMessage = 'This account is suspended';
            }

            $otp = new OTP;
            $otp->code = str_pad(strval(random_int(000000, 999999)), 6, '0', STR_PAD_LEFT);
            $otp->verification_id = bin2hex(date_format(now(), 'md') . mt_rand(100000, 999999));
            $otp->expires_at = now()->addMinutes(3);
            $otp->contact = $user->phone_number;
            $this->verification_id = $otp->verification_id;

            try {
                DB::transaction(function () use ($otp, $attempt) {
                    if (!empty($attempt)) {
                        $attempt->delete();
                    }

                    OTP::where('contact', $otp->contact)->delete(); // delete previously generated otp for the same contact
                    $otp->save();
                });
                if (config('app.env') == 'alpha' || config('app.env') == 'staging' || config('app.env') == 'local') {
                    $this->otp = $otp->code;
                } else {
                    $sms_result =  $this->sendSMS("Repay OTP \n\n$otp->code is your verification code\n\nUse this code to verify your phone number.", '+' . $otp->contact, 'sign_in_otp');
                }

                $this->otp_sent = true;
                $this->otp_expires_at = $otp->expires_at;
            } catch (AwsException | Exception $ex) {
                return $this->errorMessage = $ex->getMessage();
            }
        } else {
            try {
                /// Record attempt
                DB::transaction(function () use ($user) {
                    $attempt = AuthAttempt::firstOrNew(['user_id' => $user->id]);
                    /// Reset attempt if recent_attempt_at is passed the given treshhold
                    /// 60 * 10 = 10 minutes
                    if (now()->diffInSeconds($attempt->updated_at) > (60 * 10)) {
                        $attempt->count = 0;
                        $attempt->message_sent = 0;
                    }

                    /// Reached max attempt
                    if ($attempt->count >= 2) {
                        $attempt->restricted_until = now()->addMinutes(30);
                    }

                    /// Update attempt record
                    $attempt->updated_at = now();
                    $attempt->count += 1;
                    $attempt->save();
                });

                return $this->errorMessage = 'Invalid Credentials';
            } catch (AwsException | Exception $ex) {
                return $this->errorMessage = $ex->getMessage();
            }
        }
    }

    public function submitOTP()
    {
        $otp = OTP::where('verification_id', $this->verification_id)
            ->where('verified_at', null)->first();

        if (empty($otp)) {
            return $this->invalidOTP = true;
        }

        if ($otp->code != $this->otp) {
            return $this->invalidOTP = true;
        }

        try {
            $u = User::where('phone_number', $otp->contact)->first();
            $otp->delete();
            if (now()->isAfter($otp->expires_at)) {
                return response()->json([
                    'message' => 'Expired OTP',
                ], 401);
            }

            $this->otp_valid = $u->id;

            // auth()->loginUsingId($u->id);

            // $session_url = session("url");

            // if (empty($session_url) == false and $session_url["intended"] != null) {
            //     $redirect = session("url")["intended"];
            // } else {
            //     $redirect = route('user.dashboard');
            // }

            // return redirect($redirect);

        } catch (Exception $ex) {
            // dd($ex);
            $this->otp_valid = false;
            return $this->invalidOTP = true;
        }
    }

    public function loginWithPin($pin)
    {
        $this->resetErrorBag('pin');

        $user_pin = [
            'pin' => implode('', $pin),
            'user_id' => $this->otp_valid,
        ];
        $validator = Validator::make($user_pin, [
            'pin' => 'required|numeric|digits:4',
            'user_id' => 'required|exists:users,id',
        ], [
            'pin.required' => 'PIN is required',
            'pin.numeric' => 'PIN must be numeric',
            'pin.digits' => 'PIN must be 4 digits',
            'user_id.required' => 'No user found. Please refresh and try again',
            'user_id.exists' => 'Invalid User. Please refresh and try again',
        ]);

        if ($validator->fails()) {
            return $this->addError('pin', $validator->errors()->first('pin'));
        }

        $user = User::where('id', $this->otp_valid)->firstOrFail();

        if (Hash::check($user_pin['pin'], $user->pin) == false) {
            return $this->addError('pin', 'Invalid PIN');
        }

        if (empty($user->phone_verified_at)) {
            $user->phone_verified_at = now();
            $user->save();
        }

        Auth::login($user);

        $user->last_login_at = now();
        $user->last_login_ip = request()->ip();
        $user->save();

        return redirect()->intended(route('user.dashboard'));
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.auth.sign-in');
    }
}
