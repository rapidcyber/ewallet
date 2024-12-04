<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DetectTamperingRequest;
use App\Http\Requests\Profile\SelfieSanityRequest;
use App\Http\Requests\Profile\UpdateRequest;
use App\Http\Requests\Profile\UploadFramesRequest;
use App\Http\Requests\Profile\UploadImageRequest;
use App\Http\Requests\Profile\VerifyIDSanityRequest;
use App\Http\Requests\Profile\VerifyLivenessRequest;
use App\Models\ProfileUpdateRequest;
use App\Traits\WithHttpResponses;
use App\Traits\WithTSEKYCTrait;
use DB;
use Exception;
use Http;
use Str;

class ProfileController extends Controller
{
    use WithHttpResponses, WithTSEKYCTrait;


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
            $profile_update_request = auth()->user()->profile_update_request;
            if (empty($profile_update_request) == false) {
                $profile_update_request->delete();
            }

            $profile_update_request = new ProfileUpdateRequest;
            $profile_update_request->request_id = $request_id;
            $profile_update_request->user_id = auth()->user()->id;
            $profile_update_request->save();
            DB::commit();

            $res_body = json_decode($res->body());
            return $this->success([
                'request_id' => $profile_update_request->request_id,
                'config' => $res_body->data,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of upload_image
     * @param \App\Http\Requests\Profile\UploadImageRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function upload_image(UploadImageRequest $request)
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
     * Summary of upload_frames
     * @param \App\Http\Requests\Profile\UploadFramesRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function upload_frames(UploadFramesRequest $request)
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
     * Summary of selfie_sanity
     * @param \App\Http\Requests\Profile\SelfieSanityRequest $request
     * @return mixed
     */
    public function selfie_sanity(SelfieSanityRequest $request)
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
            $profile_update_request = auth()->user()->profile_update_request;
            $profile_update_request->selfie_sanity_score = $sanity['score'];
            $profile_update_request->selfie_sanity_req_id = $res_body['data']['request_id'];
            $profile_update_request->save();

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
     * @param \App\Http\Requests\Profile\VerifyLivenessRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verify_liveness(VerifyLivenessRequest $request)
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
            $profile_update_request = auth()->user()->profile_update_request;
            $profile_update_request->liveness_score = $liveness['score'];
            $profile_update_request->liveness_req_id = $liveness['request_id'];
            $profile_update_request->save();

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
     * @param \App\Http\Requests\Profile\VerifyIDSanityRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function card_sanity(VerifyIDSanityRequest $request)
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
            $profile_update_request = auth()->user()->profile_update_request;
            $profile_update_request->card_sanity_score = $sanity['score'];
            $profile_update_request->card_sanity_req_id = $res_body['data']['request_id'];
            $profile_update_request->save();

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
     * Summary of detect_tampering
     * @param \App\Http\Requests\Profile\DetectTamperingRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function detect_tampering(DetectTamperingRequest $request)
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
            $profile_update_request = auth()->user()->profile_update_request;
            $profile_update_request->card_tampering_score = $tampering['score'];
            $profile_update_request->card_tampering_req_id = $res_body['data']['request_id'];
            $profile_update_request->save();

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
     * Summary of update
     * @param \App\Http\Requests\Profile\UpdateRequest $request
     * @return void
     */
    public function update(UpdateRequest $request)
    {
        $validated = $request->validated();

        $profile_update_request = auth()->user()->profile_update_request;
        $profile_update_request->selfie_image_id = $validated['selfie_image_id'];
        $profile_update_request->front_card_image_id = $validated['front_card_image_id'];
        $profile_update_request->back_card_image_id = $validated['back_card_image_id'] ?? '';
        $profile_update_request->first_name = $validated['firstname'] ?? '';
        $profile_update_request->surname = $validated['surname'] ?? '';
        $profile_update_request->middle_name = $validated['middlename'] ?? '';
        $profile_update_request->suffix = $validated['ext'] ?? '';
        $profile_update_request->save();

        return $this->success();
    }
}
