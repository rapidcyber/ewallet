<?php

namespace App\Admin\ManageUsers;

use App\Models\ProfileUpdateRequest;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageUsersRequests extends Component
{
    use WithValidPhoneNumber, WithPagination, WithCustomPaginationLinks;

    #[Locked]
    public $sortDirection = 'desc';

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

    public function sortTable()
    {
        if ($this->sortDirection === 'desc') {
            $this->sortDirection = 'asc';
        } else {
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        if (!in_array($this->sortDirection, ['asc', 'desc'])) {
            $this->sortDirection = 'desc';
        }

        $profile_update_requests = ProfileUpdateRequest::whereHas('user', function ($q) {
            $q->whereHas('profile', function ($q2) {
                $q2->whereNot('status', 'deactivated');
            });
        })
            ->whereNot('selfie_image_id', '')
            ->with('user.profile')
            ->orderBy('id', $this->sortDirection)
            ->paginate(10);

        $elements = $this->getPaginationElements($profile_update_requests);

        $is_active_page = true;

        return view('admin.manage-users.admin-manage-users-requests')->with([
            'profile_update_requests' => $profile_update_requests,
            'elements' => $elements,
            'is_active_page' => $is_active_page
        ]);
    }
}
