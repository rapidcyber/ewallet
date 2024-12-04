<?php

namespace App\Admin\ManageUsers;

use App\Models\ProfileUpdateRequest;
use App\Traits\WithLog;
use App\Traits\WithNotification;
use App\Traits\WithTSEKYCTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminManageUsersRequestsShow extends Component
{
    use WithLog, WithTSEKYCTrait, WithNotification;

    public ProfileUpdateRequest $request;
    public $showApproveModal = false;
    public $showDenyModal = false;

    #[Locked]
    public $button_disabled = false;

    public function mount(ProfileUpdateRequest $profileUpdateRequest)
    {
        $this->request = $profileUpdateRequest->load('user.profile');

        if ($this->request->user->profile->status == 'deactivated' || empty($this->request->selfie_image_id)) {
            abort(404);
        }
    }

    private function get_image($id)
    {
        [$url, $headers] = $this->generate_url_headers('GET', "/v1/images/" . $id);
        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed()) {
            return null;
        }

        return base64_encode($response->body());
    }

    #[Computed(persist: true)]
    public function get_user_selfie()
    {
        $selfie_id = $this->request->selfie_image_id;

        return $this->get_image($selfie_id);
    }

    #[Computed(persist: true)]
    public function get_user_front_id()
    {
        $front_id = $this->request->front_card_image_id;

        return $this->get_image($front_id);
    }

    #[Computed(persist: true)]
    public function get_user_back_id()
    {
        $back_id = $this->request->back_card_image_id;

        return $this->get_image($back_id);
    }

    public function approve()
    {
        $user = $this->request->user;
        $kyc = $this->request->user->kyc;
        $profile = $this->request->user->profile;
        
        DB::beginTransaction();
        try {
            $kyc->request_id = $this->request->request_id;
            $kyc->liveness_score = $this->request->liveness_score;
            $kyc->card_sanity_score = $this->request->card_sanity_score;
            $kyc->selfie_sanity_score = $this->request->selfie_sanity_score;
            $kyc->card_tampering_score = $this->request->card_tampering_score;
            $kyc->liveness_req_id = $this->request->liveness_req_id;
            $kyc->card_sanity_req_id = $this->request->card_sanity_req_id;
            $kyc->selfie_sanity_req_id = $this->request->selfie_sanity_req_id;
            $kyc->card_tampering_req_id = $this->request->card_tampering_req_id;
            $kyc->selfie_image_id = $this->request->selfie_image_id;
            $kyc->front_card_image_id = $this->request->front_card_image_id;
            $kyc->back_card_image_id = $this->request->back_card_image_id;
            $kyc->save();

            if ($profile->first_name != $this->request->first_name) {
                $profile->first_name = $this->request->first_name;
            }

            if ($profile->middle_name != $this->request->middle_name) {
                $profile->middle_name = $this->request->middle_name;
            }

            if ($profile->surname != $this->request->surname) {
                $profile->surname = $this->request->surname;
            }

            if ($profile->suffix != $this->request->suffix) {
                $profile->suffix = $this->request->suffix;
            }

            $profile->status = 'verified';
            $profile->save();

            $this->admin_action_log("Approved profile update of user " . $this->request->user->id);

            $this->request->delete();

            $this->alert(
                $user,
                'notification',
                '',
                'Your profile update has been approved.'
            );

            DB::commit();

            $this->button_disabled = true;

            session()->flash('success', 'Success!');
            session()->flash('success_message', 'Requested profile update has been approved.');
            return $this->redirect(route('admin.manage-users.requests.index'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminManageUsersRequestsShow - approve - ' . $ex->getMessage());
            session()->flash('error', 'Something went wrong, please try again later');
            return $this->showApproveModal = false;
        }
    }

    public function deny()
    {
        DB::beginTransaction();
        try {
            $this->admin_action_log("Denied profile update of user " . $this->request->user->id);
            
            $this->alert(
                $this->request->user,
                'notification',
                '',
                'Your profile update request has been denied.'
            );
                
            $this->request->delete();
            
            DB::commit();

            $this->button_disabled = true;

            session()->flash('success', 'Success!');
            session()->flash('success_message', 'Requested profile update has been denied.');
            return $this->redirect(route('admin.manage-users.requests.index'));
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminManageUsersRequestsShow - deny - ' . $ex->getMessage());
            session()->flash('error', 'Something went wrong, please try again later');
            return $this->showDenyModal = false;
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.manage-users.admin-manage-users-requests-show');
    }
}
