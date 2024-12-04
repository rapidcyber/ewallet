<?php

namespace App\Admin\ManageUsers\Show;

use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithLog;
use App\Traits\WithTSEKYCTrait;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminManageUsersShowBasicDetails extends Component
{
    use WithImage, WithValidPhoneNumber, WithLog, WithTSEKYCTrait;

    public User $user;
    public $visible = false;

    protected $listeners = ['changedStatus' => '$refresh'];

    public function mount(User $user)
    {
        $this->user = $user->load(['auth_attempt', 'profile', 'roles', 'latest_balance', 'kyc']);
    }

    public function remove_restriction()
    {
        $auth_attempt = $this->user->auth_attempt;
        if ($auth_attempt && $auth_attempt->restricted_until > now()) {
            DB::beginTransaction();
            try {
                $auth_attempt->count = 0;
                $auth_attempt->restricted_until = null;
                $auth_attempt->save();
    
                $this->admin_action_log('Removed restriction from user ' . $this->user->id);

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                Log::error('AdminManageUsersShowBasicDetails.remove_restriction: ' . $ex->getMessage());
                session()->flash('error', 'Something went wrong, please try again later');
            }


            session()->flash('success', 'Successfully removed restriction');
        } else {
            session()->flash('error', 'Error: User is already unrestricted');
        }

        $this->visible = false;
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
        if (!$this->user->kyc) {
            return null;
        }

        $selfie_id = $this->user->kyc->selfie_image_id;

        return $this->get_image($selfie_id);
    }

    #[Computed(persist: true)]
    public function get_user_front_id()
    {
        if (!$this->user->kyc) {
            return null;
        }

        $front_id = $this->user->kyc->front_card_image_id;

        return $this->get_image($front_id);
    }

    #[Computed(persist: true)]
    public function get_user_back_id()
    {
        if (!$this->user->kyc) {
            return null;
        }

        $back_id = $this->user->kyc->back_card_image_id;

        return $this->get_image($back_id);
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.manage-users.show.admin-manage-users-show-basic-details');
    }
}
