<?php

namespace App\Components\Layout\Admin;

use App\Models\AdminLog;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Role;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithPushNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class UserDetailsHeader extends Component
{
    use WithImage, WithPushNotification;

    public User $user;
    public $visible = false;
    public $actionType = '';

    #[Locked]
    public $can_edit = false;

    public function mount(User $user)
    {
        $this->user = $user;

        if ($user->hasRole('administrator') == false || auth()->id() == 1) {
            $this->can_edit = true;
        }
    }

    public function change_status()
    {
        if ($this->can_edit == false) {
            return session()->flash('warning', 'You do not have permission to perform this action.');
        }

        DB::beginTransaction();
        try {
            $verified_role = Role::where('slug', 'verified_user')->first();
            $notif = new Notification;
            $notif->recipient_id = $this->user->id;
            $notif->recipient_type = User::class;
            $notif->ref_id = "";
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;

            switch ($this->actionType) {
                case 'approve':
                    if ($this->user->profile->status == 'pending') {
                        $this->user->profile->status = 'verified';
                        $this->user->profile->save();

                        $this->user->roles()->syncWithoutDetaching($verified_role->id);
    
                        session()->flash('success', 'User has been approved.');

                        $notif->message = 'Your profile has been approved.';
                        
                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Approved user ' . $this->user->id;
                        $log->save();
                    } else {
                        session()->flash('warning', 'Only pending users can be approved.');
                    }
                    break;
                case 'reactivate':
                    if (in_array($this->user->profile->status, ['rejected', 'deactivated'])) {
                        $this->user->profile->status = 'verified';
                        $this->user->profile->save();

                        $this->user->roles()->syncWithoutDetaching($verified_role->id);
    
                        session()->flash('success', 'User has been reactivated.');

                        $notif->message = 'Your profile has been reactivated.';

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Reactivated user ' . $this->user->id;
                        $log->save();
                    } else {
                        session()->flash('warning', 'Only rejected and deactivated users can be reactivated.');
                    }
                    break;
                case 'deny':
                    if ($this->user->profile->status == 'pending') {
                        $this->user->profile->status = 'rejected';
                        $this->user->profile->save();
    
                        session()->flash('success', 'User has been denied.');

                        $notif->message = 'Your profile has been denied.';

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Rejected user ' . $this->user->id;
                        $log->save();
                    } else {
                        session()->flash('warning', 'Only pending users can be denied.');
                    }
                    break;
                case 'deactivate':
                    if ($this->user->profile->status == 'verified') {
                        $this->user->profile->status = 'deactivated';
                        $this->user->profile->save();
    
                        session()->flash('success', 'User has been deactivated.');

                        $notif->message = 'Your profile has been deactivated.';

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Deactivated user ' . $this->user->id;
                        $log->save();
                    } else {
                        session()->flash('warning', 'Only verified users can be deactivated.');
                    }
                    break;
                default:
                    return session()->flash('warning', 'Invalid action.');
            }

            $notif->save();
            $this->sendPushNotification($notif);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('UserDetailsHeader: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
        }

        $this->dispatch('changedStatus');
        $this->visible = false;
    }

    public function render()
    {
        return view('components.layout.admin.user-details-header');
    }
}
