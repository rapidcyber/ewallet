<?php

namespace App\Admin\ManageMerchants;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithPushNotification;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsList extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithValidPhoneNumber, WithNotification;

    public $filter = 'all';
    public $sortDirection = 'desc';
    public $searchTerm = '';
    public $confirmationModalVisible = false;
    public $merchant_id;

    public $selectAll = false;
    public $checkedMerchants = [];
    public $groupConfirmationModalVisible = false;

    public function handleSelectAllCheckbox($checked, $users)
    {
        if ($checked) {
            $this->checkedMerchants = array_map(function ($user) {
                return $user['id'];
            }, $users);
        } else {
            $this->checkedMerchants = [];
        }
    }


    public function handleSingleSelectCheckbox($merchants)
    {
        if (count($this->checkedMerchants) === count($merchants)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function multipleActivate()
    {
        $this->validate([
            'checkedMerchants' => 'required|array|min:1|max:10',
            'checkedMerchants.*' => 'required|exists:merchants,id',
        ]);

        if (in_array(1, $this->checkedMerchants)) {
            session()->flash('error', 'Repay Merchant cannot be managed.');
            $this->groupConfirmationModalVisible = false;
            return;
        }

        $merchants = Merchant::whereIn('id', $this->checkedMerchants)
            ->with(['owner:id'])
            ->get();

        DB::beginTransaction();
        try {
            foreach ($merchants as $merchant) {
                $merchant->status = 'verified';
                $merchant->save();

                $this->alert(
                    $merchant->owner,
                    'notification',
                    $merchant->account_number,
                    "Your merchant account, '$merchant->name', has been activated.",
                    
                );
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Activated merchants ' . implode(',', $this->checkedMerchants);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.multipleActivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Merchants have been activated.');
        $this->reset(['checkedMerchants', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function multipleDeactivate()
    {
        $this->validate([
            'checkedMerchants' => 'required|array|min:1|max:10',
            'checkedMerchants.*' => 'required|exists:merchants,id',
        ]);

        if (in_array(1, $this->checkedMerchants)) {
            session()->flash('error', 'Repay Merchant cannot be managed.');
            $this->groupConfirmationModalVisible = false;
            return;
        }

        $merchants = Merchant::whereIn('id', $this->checkedMerchants)
            ->with(['owner:id'])
            ->get();

        DB::beginTransaction();
        try {
            foreach ($merchants as $merchant) {
                $merchant->status = 'deactivated';
                $merchant->save();

                $this->alert(
                    $merchant->owner,
                    'notification',
                    $merchant->account_number,
                    "Your merchant account, '$merchant->name', has been deactivated.",
                    
                );
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Deactivated merchants ' . implode(',', $this->checkedMerchants);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.multipleDeactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Merchants have been deactivated.');
        $this->reset(['checkedMerchants', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function updatedFilter()
    {
        $this->sortDirection = 'desc';
        $this->resetPage();

        if (!in_array($this->filter, ['all', 'verified', 'pending', 'rejected'])) {
            $this->filter = 'all';
        }

        $this->checkedMerchants = [];
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->sortDirection = 'desc';
        $this->checkedMerchants = [];
    }

    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
    }

    public function approve()
    {
        if ($this->merchant_id === null) {
            session()->flash('warning', 'Please select a merchant to approve.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        if ($this->merchant_id === 1) {
            session()->flash('warning', 'No actions can be performed on this merchant.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant = Merchant::with(['owner'])->find($this->merchant_id);
        if ($merchant->status === 'verified') {
            session()->flash('warning', 'Merchant is already verified.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant->status = 'verified';

        $log = new AdminLog;
        $log->user_id = auth()->id();
        $log->title = 'Approved merchant ' . $merchant->id;

        DB::beginTransaction();
        try {
            $merchant->save();

            $log->save();

            $this->alert(
                $merchant->owner,
                'notification',
                $merchant->account_number,
                "Your merchant account, '$merchant->name', has been approved.",
                
            );
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.approve: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        unset($this->all_merchants, $this->verified_merchants, $this->pending_merchants, $this->rejected_merchants);

        session()->flash('success', 'Merchant has been approved.');
        $this->reset(['confirmationModalVisible', 'merchant_id']);
        return;
    }

    public function deny()
    {
        if ($this->merchant_id === null) {
            session()->flash('warning', 'Please select a merchant to deny.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        if ($this->merchant_id === 1) {
            session()->flash('warning', 'No actions can be performed on this merchant.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant = Merchant::with(['owner:id'])->find($this->merchant_id);
        if ($merchant->status === 'rejected') {
            session()->flash('warning', 'Merchant is already denied.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant->status = 'rejected';

        $log = new AdminLog;
        $log->user_id = auth()->id();
        $log->title = 'Rejected merchant ' . $merchant->id;

        DB::beginTransaction();
        try {
            $merchant->save();

            $log->save();

            $this->alert(
                $merchant->owner,
                'notification',
                $merchant->account_number,
                "Your merchant account, '$merchant->name', has been rejected.",
                
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.deny: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        unset($this->all_merchants, $this->verified_merchants, $this->pending_merchants, $this->rejected_merchants);

        session()->flash('success', 'Merchant has been denied.');
        $this->reset(['confirmationModalVisible', 'merchant_id']);
        return;
    }

    public function reactivate()
    {
        if ($this->merchant_id === null) {
            session()->flash('warning', 'Please select a merchant to reactivate.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        if ($this->merchant_id === 1) {
            session()->flash('warning', 'No actions can be performed on this merchant.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant = Merchant::with(['owner:id'])->find($this->merchant_id);
        if ($merchant->status === 'verified' || $merchant->status === 'pending') {
            session()->flash('warning', 'Merchant is already active.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant->status = 'verified';

        $log = new AdminLog;
        $log->user_id = auth()->id();
        $log->title = 'Reactivated merchant ' . $merchant->id;

        DB::beginTransaction();
        try {
            $merchant->save();

            $log->save();

            $this->alert(
                $merchant->owner,
                'notification',
                $merchant->account_number,
                "Your merchant account, '$merchant->name', has been reactivated.",
                
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.reactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        unset($this->all_merchants, $this->verified_merchants, $this->pending_merchants, $this->rejected_merchants);

        session()->flash('success', 'Merchant has been reactivated.');
        $this->reset(['confirmationModalVisible', 'merchant_id']);
        return;
    }

    public function deactivate()
    {
        if ($this->merchant_id === null) {
            session()->flash('warning', 'Please select a merchant to deactivate.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        if ($this->merchant_id === 1) {
            session()->flash('warning', 'No actions can be performed on this merchant.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant = Merchant::with(['owner:id'])->find($this->merchant_id);
        if ($merchant->status === 'deactivated') {
            session()->flash('warning', 'Merchant is already deactivated.');
            $this->reset(['confirmationModalVisible', 'merchant_id']);
            return;
        }

        $merchant->status = 'deactivated';

        $log = new AdminLog;
        $log->user_id = auth()->id();
        $log->title = 'Deactivated merchant ' . $merchant->id;

        DB::beginTransaction();
        try {
            $merchant->save();

            $log->save();

            $this->alert(
                $merchant->owner,
                'notification',
                $merchant->account_number,
                "Your merchant account, '$merchant->name', has been deactivated.",
                
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsList.deactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        unset($this->all_merchants, $this->verified_merchants, $this->pending_merchants, $this->rejected_merchants);

        session()->flash('success', 'Merchant has been deactivated.');
        $this->reset(['confirmationModalVisible', 'merchant_id']);
        return;
    }

    #[Computed]
    public function all_merchants()
    {
        return Merchant::count();
    }

    #[Computed]
    public function pending_merchants()
    {
        return Merchant::where('status', 'pending')->count();
    }

    #[Computed]
    public function active_merchants()
    {
        return Merchant::where('status', 'verified')->count();
    }

    #[Computed]
    public function rejected_merchants()
    {
        return Merchant::whereIn('status', ['rejected', 'deactivated'])->count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $merchants = Merchant::with([
            'media' => function ($query) {
                $query->where('collection_name', 'merchant_logo');
            }
        ]);

        $merchants = match ($this->filter) {
            'all' => $merchants,
            'pending' => $merchants->where('status', 'pending'),
            'verified' => $merchants->where('status', 'verified'),
            'rejected' => $merchants->whereIn('status', ['rejected', 'deactivated']),
        };

        if ($this->searchTerm) {
            $merchants = $merchants->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%');
                $query->orWhere('email', 'like', '%' . $this->searchTerm . '%');
                $query->orWhere('phone_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $merchants = $merchants->orderBy('created_at', $this->sortDirection)
            ->paginate(10);

        $elements = $this->getPaginationElements($merchants);

        return view('admin.manage-merchants.admin-manage-merchants-list')->with([
            'merchants' => $merchants,
            'elements' => $elements
        ]);
    }
}
