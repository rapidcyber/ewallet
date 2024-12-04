<?php

namespace App\Guest\Auth;

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Traits\WithSMS;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ForgotPassword extends Component
{
    use WithSMS;
    
    #[Locked]
    public $verification_id;

    #[Locked]
    public $validOtp = false;

    #[Locked]
    public $passwordResetted = false;

    public $email_or_phone;

    public $lookup;

    public $otp;

    public $new_password;

    public $confirm_password;

    public $message;

    #[Locked]
    public $form_state = 'forgot_password_email_step';

    #[Locked]
    public $invalidOTP = false;

    public function resend_code()
    {
        $this->verification_id = null;
        $this->otp = null;
        $this->message = null;
        $this->form_state = 'forgot_password_email_step';

        $this->reset_password();
    }

    public function send_otp()
    {
        // check if this is the second time this function was called consecutively
        if ($this->form_state !== 'forgot_password_email_step' || $this->verification_id) {
            return;
        }

        $this->validate([
            'email_or_phone' => 'required',
        ]);

        $user = User::where('email', $this->email_or_phone)
            ->orWhere('phone_number', $this->email_or_phone)
            ->first();

        if (empty($user)) {
            session()->flash('error', 'Error: User not found');
            session()->flash('error_message', 'No user found with the provided details.');
            return;
        }

        $verification_id = bin2hex(date_format(now(), 'md').mt_rand(100000, 999999));
        $code = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);

        DB::beginTransaction();
        try {
            $passwordCode = PasswordResetCode::firstOrNew([
                'contact' => $user->phone_number,
            ]);

            $passwordCode->code = $code;
            $passwordCode->verification_id = $verification_id;
            $passwordCode->expires_at = now()->addMinutes(5);
            $passwordCode->save();

            DB::commit();
            
            $this->sendSMS("Your password reset code is $code", $user->phone_number, 'verification_otp');

            $env = config('app.env');
            if ($env == 'local' || $env == 'alpha' || $env == 'staging') {
                $this->otp = $passwordCode->code;
            }

            $masked = Str::mask($user->phone_number, '*',  0, 9);

            $this->form_state = 'forgot_password_verification_step';
            $this->verification_id = $passwordCode->verification_id;
            session()->flash('success', 'Code Sent');
            session()->flash('success_message', "A code has been sent to your registered phone number " . $masked);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ForgotPassword.send_otp: '.$e->getMessage());

            $this->reset(['form_state', 'verification_id']);
            session()->flash('error', 'Error has occurred');
            session()->flash('error_message', 'Please try again later');
            return;
        }
    }

    public function verify_otp()
    {
        if ($this->form_state !== 'forgot_password_verification_step' || !$this->verification_id || $this->validOtp) {
            return;
        }

        $this->validate([
            'otp' => 'required',
        ]);

        $passwordCode = PasswordResetCode::where('verification_id', $this->verification_id)->first();

        if (empty($passwordCode)) {
            session()->flash('error', 'Error: Invalid Verification ID');
            session()->flash('error_message', 'Please try again.');
            return redirect()->route('forgot-password');
        }

        if ($passwordCode->expires_at < now()) {
            session()->flash('error', 'Error: Code Expired');
            session()->flash('error_message', 'Click "Resend Code" to get a new code.');
            $this->invalidOTP = true;
            return;
        }

        if ($passwordCode->code != $this->otp) {
            $this->validOtp = false;
            session()->flash('error', 'Error: Invalid Code');
            session()->flash('error_message', 'Please try again.');
            $this->invalidOTP = true;
            return;
        }

        session()->flash('success', 'Success: Code Verified');
        session()->flash('success_message', 'Enter your new password for your account.');
        $this->validOtp = true;
        $this->form_state = 'forgot_password_reset_step';
    }

    public function resetPassword()
    {
        if (!$this->validOtp || $this->passwordResetted) {
            return;
        }

        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d!@#$%^&*()_+={}\[\]:;\"\'<>,.?\/\\|`~\-]{8,32}$/";

        $this->validate([
            'new_password' => ['required','confirmed:confirm_password','string','min:8','max:32', function ($attribute, $value, $fail) use ($pattern) {
                if (!preg_match($pattern, $value)) {
                    $fail('Password must contain at least one uppercase letter, one lowercase letter, and one number');
                }
            }],
        ], [
            'new_password.confirmed' => 'Passwords do not match',
        ]);

        $passwordCode = PasswordResetCode::where('verification_id', $this->verification_id)->first();

        if (empty($passwordCode)) {
            session()->flash('error', 'Error: User not found');
            session()->flash('error_message', 'Please try again.');
            return redirect()->route('forgot-password');
        }

        $user = User::where('phone_number', $passwordCode->contact)->first();

        if (empty($user)) {
            session()->flash('error', 'Error: User not found');
            session()->flash('error_message', 'Please try again.');
            return redirect()->route('forgot-password');
        }

        DB::beginTransaction();
        try {
            $passwordCode->delete();

            $user->password = Hash::make($this->new_password);
            $user->save();

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('ForgotPassword.resetPassword: '.$ex->getMessage());
            session()->flash('error', 'Error has occurred');
            session()->flash('error_message', 'Please try again later');
            return;
        }

        $this->passwordResetted = true;

        session()->flash('success', 'Success: Password Reset');
        session()->flash('success_message', 'You can now sign in with your new password.');
        return redirect()->route('sign-in');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.auth.forgot-password');
    }
}
