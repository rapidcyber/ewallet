<?php

namespace App\Admin\ManageUsers;

use App\Models\AdminLog;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\ProfileUpdateRequest;
use App\Models\Role;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithLog;
use App\Traits\WithNotification;
use App\Traits\WithTempPassword;
use App\Traits\WithValidPhoneNumber;
use App\Traits\WithPushNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageUsers extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithTempPassword, WithImage, WithValidPhoneNumber, WithNotification, WithLog;

    #[Locked]
    public $user;
    public $allUsersCount;
    public $order = 'desc';
    public $pendingUsersCount;
    public $verifiedUsersCount;
    public $deniedUsersCount;
    public $activeBox = 1;
    public $searchTerm = '';
    public $confirmationModalVisible = false;
    public $user_id = 0;
    public $showPopup = false;

    public $selectAll = false;
    public $checkedUsers = [];
    public $groupConfirmationModalVisible = false;


    public function handleSelectAllCheckbox($checked, $users)
    {
        if ($checked) {
            $this->checkedUsers = array_map(function ($user) {
                return $user['id'];
            }, $users);
        } else {
            $this->checkedUsers = [];
        }
    }

    public function handleSingleSelectCheckbox($users)
    {
        // if (in_array($user_id, $this->checkedUsers)) {
        //     $this->checkedUsers = array_diff($this->checkedUsers, [$user_id]);
        // } else {
        //     $this->checkedUsers[] = $user_id;
        // }

        if (count($this->checkedUsers) === count($users)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function multipleActivate()
    {
        $this->validate([
            'checkedUsers' => 'required|array|min:1|max:10',
            'checkedUsers.*' => 'required|exists:users,id',
        ]);

        if (in_array([1, auth()->id()], $this->checkedUsers)) {
            session()->flash('error', 'System Admin or Owned Account cannot be managed.');
            $this->groupConfirmationModalVisible = false;
            return;
        }

        $users = User::with('profile')->whereIn('id', $this->checkedUsers)->get();

        DB::beginTransaction();
        try {
            $verified_role = Role::where('name', 'Verified User')->first();

            foreach ($users as $user) {
                $status = $user->profile->status;

                $user->tokens()->update(['revoked' => false]);

                $user->profile->status = 'verified';
                $user->profile->save();

                $user->roles()->syncWithoutDetaching($verified_role->id);

                if ($status === 'pending') {
                    $this->alert(
                        $user,
                        'notification',
                        '',
                        'Your profile has been approved.'
                    );

                    $this->admin_action_log('Approved profile of user ' . $user->id);
                } else {
                    $this->alert(
                        $user,
                        'notification',
                        '',
                        'Your profile has been reactivated.'  
                    );

                    $this->admin_action_log('Reactivated user ' . $user->id);
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminManageUsersList.multipleActivate: ' . $ex->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Users have been activated.');
        $this->reset(['checkedUsers', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function multipleDeactivate()
    {
        $this->validate([
            'checkedUsers' => 'required|array|min:1|max:10',
            'checkedUsers.*' => 'required|exists:users,id',
        ]);
        
        $admin_users = User::whereHas('roles', function ($q) {
            $q->where('slug', 'administrator');
        })->pluck('id')->toArray();

        if (in_array($admin_users, $this->checkedUsers)) {
            session()->flash('error', 'Administrator accounts cannot be managed.');
            $this->groupConfirmationModalVisible = false;
            return;
        }

        $users = User::with('profile')->whereIn('id', $this->checkedUsers)->get();

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                $this->alert(
                    $user,
                    'notification',
                    '',
                    'Your account has been deactivated.'
                );

                $user->profile->status = 'deactivated';
                $user->profile->save();

                $user->tokens()->update(['revoked' => true]);

                $user->fcm_token = '';
                $user->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Deactivated users ' . implode(',', $this->checkedUsers);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageUsersList.multipleActivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Users have been deactivated.');
        $this->reset(['checkedUsers', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function handleApproveButtonClick()
    {
        DB::beginTransaction();
        try {
            $verified_role = Role::where('name', 'Verified User')->first();
            $user = User::find($this->user_id);
            // If the profile `created_at` and `updated_at` is the same, it means we are approving a registration
            // if the profile `created_at` is not the same as the `updated_at`, it means we are approving a profile update.
            $user->profile->status = 'verified';
            if ($user->profile->created_at->equalTo($user->profile->updated_at)) {
                /// First approval of profile
                $notif = new Notification;
                $notif->recipient_id = $user->id;
                $notif->recipient_type = User::class;
                $notif->ref_id = "";
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = 'Your profile has been approved.';
                $notif->save();

                $this->sendPushNotification($notif);

                // application for realholmes account
                // if (! empty($user->apply_for_realholmes) && in_array($user->apply_for_realholmes, ['owner', 'merchant'])) {
                //     [$plain, $_] = $this->generate_temp_password();
                //     $phone = new PhoneNumber($user->phone_number, $user->phone_iso);
                //     $res = apply_for_realholmes_account($plain, $user->apply_for_realholmes, $user->app_id, $user->current_address, $phone, $user);
                //     if ($res->ok()) {
                //         sendMail(
                //             $user->email,
                //             new RepayMail(
                //                 'Onboarding via Repay Merchant Application',
                //                 [
                //                     '<h2>Welcome to RealHolmes!</h2>',
                //                     'Your account registration via Repay, has been approved!',
                //                     ' ',
                //                     'You can now login to RealHolmes using your email address and a temporary password:',
                //                     "Password: <b>$plain</b>",
                //                     '</n>',
                //                     "Don't share this password to anyone, and make sure that you reset your password.",
                //                 ]
                //             )
                //         );
                //         $responseText = $user->profile->first_name."'s profile has been approved";
                //         $status = $statusEnum[0];
                //     } else {
                //         $responseText = $res->body();
                //         $status = $statusEnum[1];
                //     }
                // } else {
                // $responseText = $user->profile->first_name."'s profile has been approved";
                // $status = $statusEnum[0];
                // }
            } else {
                /// Profile update, create notification.
                $notif = new Notification;
                $notif->recipient_id = $user->id;
                $notif->recipient_type = User::class;
                $notif->ref_id = "";
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = 'Your profile update has been approved.';
                $notif->save();

                $this->sendPushNotification($notif);

                $responseText = $user->profile->first_name . "'s profile has been approved";
            }
            $user->profile->save();

            $user->roles()->syncWithoutDetaching($verified_role->id);

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Approved user ' . $user->id;
            $log->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AdminManageUsersList.handleApproveButtonClick: ' . $e->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'User has been approved.');

        $this->reset('confirmationModalVisible');
        return;
    }

    public function handleDeactivateButtonClick()
    {
        DB::beginTransaction();
        try {
            $user = User::find($this->user_id);

            $user->tokens()->update(['revoked' => true]);
            $user->fcm_token = '';
            $user->save();

            $user->profile->status = 'deactivated';
            $user->profile->save();

            $this->admin_action_log('Deactivated user ' . $user->id);
            DB::commit();
            session()->flash('success', 'User has been deactivated.');
            $this->reset('confirmationModalVisible');
            return;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AdminManageUsersList.handleDeactivateButtonClick: ' . $e->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            $this->reset('confirmationModalVisible');
            return;
        }
    }

    public function handleReactivateButtonClick()
    {
        DB::beginTransaction();
        try {
            $verified_role = Role::where('name', 'Verified User')->first();

            $user = User::find($this->user_id);
            $user->profile->status = 'verified';
            $user->profile->save();

            $user->roles()->syncWithoutDetaching($verified_role->id);

            $user->tokens()->update(['revoked' => false]);

            $this->admin_action_log('Reactivated user ' . $user->id);

            $this->alert(
                $user,
                'notification',
                '',
                'Your account has been reactivated.'
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AdminManageUsersList.handleReactivateButtonClick: ' . $e->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'User has been reactivated.');
        $this->reset('confirmationModalVisible');
        return;
    }

    public function handleDenyButtonClick()
    {
        DB::beginTransaction();
        try {
            $user = User::find($this->user_id);
            // If the profile `created_at` and `updated_at` is the same, it means we are denying a registration
            // if the profile `created_at` is not the same as the `updated_at`, it means we are denying a profile update.
            $user->profile->status = 'rejected';
            if (!($user->profile->created_at->equalTo($user->profile->updated_at))) {
                $this->alert(
                    $user,
                    'notification',
                    '',
                    'Your profile update has been denied.'
                );

                $this->admin_action_log('Denied profile update of user ' . $user->id);
            } else {
                $this->alert(
                    $user,
                    'notification',
                    '',
                    'Your profile has been denied.'
                );

                $this->admin_action_log('Denied profile of user ' . $user->id);
            }

            $user->profile->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AdminManageUsersList.handleDenyButtonClick: ' . $e->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'User has been denied.');
        $this->reset('confirmationModalVisible');
        return;
    }

    public function sortTable()
    {
        if ($this->order === 'desc') {
            $this->order = 'asc';
        } elseif ($this->order === 'asc') {
            $this->order = 'desc';
        }
    }

    public function handleFilterBoxClick($num)
    {
        $this->activeBox = $num;
        $this->order = 'desc';
        $this->checkedUsers = [];
        $this->resetPage();
    }

    public function handleActionButtonClick($data)
    {
        $this->actionConfirmationModal['isVisible'] = $data['visibility'];
        $this->actionConfirmationModal['type'] = $data['type'];
        $this->actionConfirmationModal['selectedUserId'] = $data['userId'];
    }

    public function closeModal()
    {
        $this->actionConfirmationModal['isVisible'] = false;
        $this->actionConfirmationModal['type'] = '';
        $this->actionConfirmationModal['selectedUserId'] = null;
    }

    public function closePopup()
    {
        $this->showPopup = false;
    }

    public function updatedSearchTerm()
    {
        $this->checkedUsers = [];
        $this->resetPage();
    }

    #[Computed]
    public function allUsersCount()
    {
        return User::count();
    }

    #[Computed]
    public function pendingUsersCount()
    {
        return User::whereHas('profile', function ($q) {
            $q->where('status', 'pending');
        })->count();
    }

    #[Computed]
    public function verifiedUsersCount()
    {
        return User::whereHas('profile', function ($q) {
            $q->where('status', 'verified');
        })->count();
    }

    #[Computed]
    public function deniedUsersCount()
    {
        return User::whereHas('profile', function ($q) {
            $q->whereIn('status', ['rejected', 'deactivated']);
        })->count();
    }

    #[Computed]
    public function profileUpdateRequestsCount()
    {
        return ProfileUpdateRequest::whereHas('user.profile', function ($q) { $q->whereNot('status', 'deactivated'); })->whereNot('selfie_image_id', '')->count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $users = User::with(['profile'])->where(function ($q) {
            $q->whereHas('profile', function ($q2) {
                if ($this->activeBox === 2) {
                    $q2->where('status', 'pending');
                } elseif ($this->activeBox === 3) {
                    $q2->where('status', 'verified');
                } elseif ($this->activeBox === 4) {
                    $q2->whereIn('status', ['rejected', 'deactivated']);
                }

                if (!empty($this->searchTerm)) {
                    $q2->where(function ($q3) {
                        $q3->where(DB::raw("CONCAT(first_name, ' ', surname)"), 'LIKE', '%' . $this->searchTerm . '%');
                        $q3->orWhere('phone_number', 'LIKE', '%' . $this->searchTerm . '%');
                        $q3->orWhere('email', 'LIKE', '%' . $this->searchTerm . '%');
                    });
                }
            });
        })->orderBy('created_at', $this->order)->paginate(10);

        $elements = $this->getPaginationElements($users);

        return view('admin.manage-users.admin-manage-users')->with([
            'users' => $users,
            'elements' => $elements,
        ]);
    }
}
