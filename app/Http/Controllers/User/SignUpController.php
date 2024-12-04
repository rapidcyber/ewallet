<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CompleteKYCRequest;
use App\Http\Requests\User\ImageRequest;
use App\Http\Requests\User\SignUpCompareFacesRequest;
use App\Http\Requests\User\SignUpDetectIDTamperingRequest;
use App\Http\Requests\User\SignUpRequest;
use App\Http\Requests\User\SignUpUploadFramesRequest;
use App\Http\Requests\User\SignUpUploadImageRequest;
use App\Http\Requests\User\SignUpVerifyIDSanityRequest;
use App\Http\Requests\User\SignUpVerifyLivenessRequest;
use App\Http\Requests\User\SignUpVerifySelfieSanityRequest;
use App\Http\Requests\User\EmailTakenRequest;
use App\Http\Requests\User\PhoneTakenRequest;
use App\Http\Requests\User\UsernameTakenRequest;
use App\Http\Requests\User\GenerateMailOTPRequest;
use App\Http\Requests\User\GenerateOTPRequest;
use App\Http\Requests\User\VerifyOTPRequest;
use App\Mail\RepayMail;
use App\Models\OTP;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserKyc;
use App\Traits\WithHttpResponses;
use App\Traits\WithMail;
use App\Traits\WithNumberGeneration;
use App\Traits\WithSMS;
use App\Traits\WithTSEKYCTrait;
use App\Traits\WithValidPhoneNumber;
use DB;
use Exception;
use Hash;
use Http;
use Propaganistas\LaravelPhone\PhoneNumber;
use Response;
use Str;

class SignUpController extends Controller
{
    use
        WithNumberGeneration,
        WithValidPhoneNumber,
        WithHttpResponses,
        WithHttpResponses,
        WithTSEKYCTrait,
        WithMail,
        WithSMS;


    ///////////////////////////////// UTILS

    /**
     * Summary of phone_taken
     * @param \App\Http\Requests\User\PhoneTakenRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function phone_taken(PhoneTakenRequest $request)
    {
        $validated = $request->validated();
        $phone_number = $validated['phone_number'];

        $phone_info = $this->phonenumber_info($phone_number);
        if ($phone_info == false) {
            return $this->error('Invalid phone number', 499);
        }

        $exists = User::where(
            'phone_number',
            $phone_info->getCountryCode() . $phone_info->getNationalNumber(),
        )->exists();
        return $this->success(['taken' => $exists]);
    }

    /**
     * Summary of email_taken
     * @param \App\Http\Requests\User\EmailTakenRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function email_taken(EmailTakenRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $exists = User::where('email', $email)->exists();
        return $this->success(['taken' => $exists]);
    }

    /**
     * Summary of username_taken
     * @param \App\Http\Requests\User\UsernameTakenRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function username_taken(UsernameTakenRequest $request)
    {
        $validated = $request->validated();
        $username = $validated['username'];
        $exists = User::where('username', $username)->exists();
        return $this->success($exists);
    }

    /**
     * Summary of generate_otp
     * @param \App\Http\Requests\User\GenerateOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_otp(GenerateOTPRequest $request)
    {
        $validated = $request->validated();
        $phone_number = $validated['phone_number'];


        /// for resending
        $otp = OTP::where([
            'contact' => $phone_number,
            'type' => 'sign_up',
        ])->first();

        DB::beginTransaction();
        try {
            if (empty($otp) == false) {
                $otp->delete();
            }

            $otp = $this->generate_otp_code($phone_number, 'sign_up');
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
     * Summary of generate_mail_otp
     * @param \App\Http\Requests\User\GenerateMailOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_mail_otp(GenerateMailOTPRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];

        /// for resending
        $otp = OTP::where([
            'contact' => $email,
            'type' => 'sign_up',
        ])->first();

        DB::beginTransaction();
        try {
            if (empty($otp) == false) {
                $otp->delete();
            }

            $otp = $this->generate_otp_code($email, 'sign_up');
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
     * Summary of verify_otp
     * @param \App\Http\Requests\User\VerifyOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_otp(VerifyOTPRequest $request)
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
            $otp->update(['verified_at' => now()]);
            DB::commit();

            return $this->success(['verified' => true]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of submit
     * @param \App\Http\Requests\User\SignUpRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function submit(SignUpRequest $request)
    {
        $validated = $request->validated();

        $phone = $validated['phone_number'];
        $email = $validated['email'];
        $firstname = $validated['firstname'];
        $middlename = $validated['middlename'] ?? '';
        $surname = $validated['surname'];
        $ext = $validated['ext'] ?? '';

        $password = $validated['password'];
        $pin = $validated['pin'];

        $phoneOTP = OTP::where('contact', $phone)->first();
        $emailOTP = OTP::where('contact', $email)->first();

        $phone_info = new PhoneNumber($phone);

        $user = new User;
        $user->fill([
            'app_id' => Str::uuid(),
            'phone_iso' => $phone_info->getCountry(),
            'phone_number' => str_replace('+', '', $phone),
            'phone_verified_at' => $phoneOTP->verified_at,
            'email' => $email,
            'email_verified_at' => $emailOTP->verified_at,
            'password' => Hash::make($password),
            'pin' => Hash::make($pin),
            'username' => explode('@', $email)[0] . '_' . User::count() + 1,
        ]);

        $profile = new Profile;
        $profile->fill([
            'first_name' => $firstname,
            'middle_name' => $middlename,
            'surname' => $surname,
            'suffix' => $ext,
            'status' => 'pending',
        ]);

        DB::beginTransaction();
        try {
            $user->save();
            $profile->user_id = $user->id;
            $profile->save();

            $phoneOTP->delete();
            $emailOTP->delete();
            DB::commit();

            $pin_token = $user->createToken('auth-pin', ['auth-pin'])->accessToken;
            $app_token = $user->createToken('repay-app', ['repay-app'])->accessToken;
            return $this->success([
                'pin_token' => $pin_token,
                'app_token' => $app_token,
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    //////////////////// KYC 

    /**
     * Handles errors from TS KYC
     * 
     * @param array $errors
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    private function handle_kyc_error(array $errors)
    {
        $error = $errors[0];
        return $this->error($error['message'], 499);
    }

    /**
     * Summary of settings
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function settings()
    {
        $request_id = Str::uuid();
        [$url, $headers] = $this->generate_url_headers('GET', '/v1/client_settings', $request_id);
        $res = Http::withHeaders($headers)->get($url);

        if ($res->failed()) {
            return $this->error('Something went wrong, please try again later. [KYCSETERR]', 499);
        }

        DB::beginTransaction();
        try {
            $user_kyc = auth()->user()->kyc;
            if (empty($user_kyc) == false) {
                $user_kyc->delete();
            }

            $user_kyc = new UserKyc;
            $user_kyc->request_id = $request_id;
            $user_kyc->user_id = auth()->user()->id;
            $user_kyc->save();
            DB::commit();

            $res_body = json_decode($res->body());
            return $this->success([
                'request_id' => $user_kyc->request_id,
                'config' => $res_body->data,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of submit_image
     * @param \App\Http\Requests\User\SignUpUploadImageRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function upload_image(SignUpUploadImageRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers('POST', '/v1/images', $validated['request_id']);

        $response = Http::withHeaders($headers)->post($url, $validated);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }

        return $this->success([
            'image_id' => $res_body['data']['image_id']
        ]);
    }

    /**
     * Summary of submit_frames
     * @param \App\Http\Requests\User\SignUpUploadFramesRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function upload_frames(SignUpUploadFramesRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers('POST', '/v1/files', $validated['request_id']);

        $response = Http::withHeaders($headers)->post($url, $validated);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }

        return $this->success([
            'file_id' => $res_body['data']['file_id']
        ]);
    }

    /**
     * Summary of verify_selfie_sanity
     * @param \App\Http\Requests\User\SignUpVerifySelfieSanityRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function selfie_sanity(SignUpVerifySelfieSanityRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers(
            'POST',
            '/v1/verify_portrait_sanity_sync',
            $validated['request_id'],
        );
        $data = [
            'image' => [
                'id' => $validated['image_id'],
            ],
            'selfie_type' => 'flash',
        ];

        $response = Http::withHeaders($headers)->post($url, $data);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }

        if ($res_body['data']['status'] == 'failure') {
            return $this->handle_kyc_error($res_body['errors']);
        }

        $sanity = $res_body['data']['portrait_sanity'];
        if ($sanity['verdict'] == 'good') {
            $user_kyc = auth()->user()->kyc;
            $user_kyc->selfie_sanity_score = $sanity['score'];
            $user_kyc->selfie_sanity_req_id = $res_body['data']['request_id'];
            $user_kyc->save();

            $result = [
                'pass' => true,
                'message' => 'passed',
            ];
        } else {
            $result = [
                'pass' => false,
                'message' => $sanity['verdict'],
            ];
        }
        return $this->success($result);
    }

    /**
     * Summary of verify_liveness
     * @param \App\Http\Requests\User\SignUpVerifyLivenessRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_liveness(SignUpVerifyLivenessRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers(
            'POST',
            '/v1/verify_face_liveness_sync',
            $validated['request_id'],
        );

        unset($validated['request_id']);
        $response = Http::withHeaders($headers)->post($url, [
            ...$validated,
            'selfie_type' => 'flash',
        ]);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }


        if ($res_body['data']['status'] == 'failure') {
            return $this->handle_kyc_error($res_body['errors']);
        }

        $liveness = $res_body['data'];
        if ($liveness['is_live']) {
            $user_kyc = auth()->user()->kyc;
            $user_kyc->liveness_score = $liveness['score'];
            $user_kyc->liveness_req_id = $liveness['request_id'];
            $user_kyc->save();

            /// TODO save liveness score and request_id
            $result = [
                'pass' => true,
                'score' => $liveness['score'],
            ];
        } else {
            $result = [
                'pass' => false,
                'score' => $liveness['score'],
            ];
        }

        return $this->success($result);
    }

    /**
     * Summary of card_sanity
     * @param \App\Http\Requests\User\SignUpVerifyIDSanityRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function card_sanity(SignUpVerifyIDSanityRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers(
            'POST',
            '/v1/verify_id_card_sanity_sync',
            $validated['request_id']
        );

        $data = [
            "card_type" => $validated["card_type"],
            "image1" => [
                "id" => $validated["front_image_id"],
            ],
        ];

        if (empty($validated['back_image_id']) == false) {
            $data['image2'] = [
                "id" => $validated['back_image_id'],
            ];
        }

        $response = Http::withHeaders($headers)->post($url, $data);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }


        if ($res_body['data']['status'] == 'failure') {
            return $this->handle_kyc_error($res_body['errors']);
        }

        $sanity = $res_body['data']['card_sanity'];
        if ($sanity['verdict'] == 'good') {
            $user_kyc = auth()->user()->kyc;
            $user_kyc->card_sanity_score = $sanity['score'];
            $user_kyc->card_sanity_req_id = $res_body['data']['request_id'];
            $user_kyc->save();

            $result = [
                'pass' => true,
                'message' => 'passed',
            ];
        } else {
            $result = [
                'pass' => false,
                'message' => $sanity['verdict'],
            ];
        }

        return $this->success($result);
    }

    /**
     * Summary of detect_id_tampering
     * @param \App\Http\Requests\User\SignUpDetectIDTamperingRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function detect_tampering(SignUpDetectIDTamperingRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers(
            'POST',
            '/v1/detect_id_card_tampering_sync',
            $validated['request_id'],
        );

        $data = [
            'card_type' => $validated['card_type'],
            "image" => [
                "id" => $validated['front_image_id'],
            ],
        ];

        if (empty($validated['back_image_id']) == false) {
            $data['image2'] = [
                "id" => $validated['back_image_id'],
            ];
        }

        $response = Http::withHeaders($headers)->post($url, $data);
        $res_body = json_decode($response->body(), true);

        if ($response->successful() == false) {
            return $this->error('Error processing images', 499, $res_body['errors']);
        }

        if ($res_body['data']['status'] == 'failure') {
            return $this->handle_kyc_error($res_body['errors']);
        }

        $tampering = $res_body['data']['card_tampering'];
        if ($tampering['score'] > .74) {
            $user_kyc = auth()->user()->kyc;
            $user_kyc->card_tampering_score = $tampering['score'];
            $user_kyc->card_tampering_req_id = $res_body['data']['request_id'];
            $user_kyc->save();

            $result = [
                'pass' => true,
                'message' => 'passed',
                'verdict' => $tampering['verdict'],
            ];
        } else {
            $result = [
                'pass' => false,
                'message' => 'retry',
                'verdict' => $tampering['verdict'],
            ];
        }

        return $this->success($result);
    }

    /**
     * Summary of complete_kyc
     * @param \App\Http\Requests\User\CompleteKYCRequest $request
     * @return void
     */
    public function complete_kyc(CompleteKYCRequest $request)
    {
        $validated = $request->validated();

        $user_kyc = auth()->user()->kyc;
        $user_kyc->selfie_image_id = $validated['selfie_image_id'];
        $user_kyc->front_card_image_id = $validated['front_card_image_id'];
        $user_kyc->back_card_image_id = $validated['back_card_image_id'] ?? '';

        $user_kyc->save();
        return $this->success();
    }

    /**
     * Summary of image
     * @param \App\Http\Requests\User\ImageRequest $request
     * @return bool
     */
    public function image(ImageRequest $request)
    {
        $validated = $request->validated();
        [$url, $headers] = $this->generate_url_headers('GET', "/v1/images/" . $validated['id']);
        $response = Http::withHeaders($headers)->get($url);

        $image = imagecreatefromstring($response->body());
        header('Content-type: image/png');
        return imagejpeg($image);
    }
}
