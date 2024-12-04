<?php

namespace App\Admin\ManageMerchants\Show\Services;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowServices extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public Merchant $merchant;
    public $searchTerm = '';
    #[Locked]
    public $sortDirection = 'desc';

    public $confirmationModalVisible = false;
    public $actionType = '';

    public $groupConfirmationModalVisible = false;
    public $groupActionType = '';
    public $selectAll = false;
    public $checkedServices = [];
    public $service_id;

    public function handleSelectAllCheckbox($checked, $services)
    {
        if ($checked) {
            $this->checkedServices = $services;
        } else {
            $this->checkedServices = [];
        }
    }

    public function handleSingleSelectCheckbox($services)
    {
        if (count($this->checkedServices) === count($services)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
    }

    public function multipleActivate()
    {
        $this->validate([
            'checkedServices' => 'required|array|min:1|max:10',
            'checkedServices.*' => 'required|exists:services,id',
        ]);

        $services = Service::where('merchant_id', $this->merchant->id)->whereIn('id', $this->checkedServices)->get();

        DB::beginTransaction();
        try {
            foreach($services as $service) {
                $service->approval_status = 'approved';
                $service->save();

                $service = $service->load(['merchant.owner']);
                $notif = new Notification;
                $notif->recipient_id = $service->merchant->id;
                $notif->recipient_type = Merchant::class;
                $notif->ref_id = $service->id;
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = "Your service, {$service->name}, has been approved.";
                $notif->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Activated services ' . implode(',', $this->checkedServices);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowServices.multipleActivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }
        
        session()->flash('success', 'Services have been activated.');
        $this->reset(['checkedServices', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function multipleDeactivate()
    {
        $this->validate([
            'checkedServices' => 'required|array|min:1|max:10',
            'checkedServices.*' => 'required|exists:services,id',
        ]);

        $services = Service::where('merchant_id', $this->merchant->id)->whereIn('id', $this->checkedServices)->get();

        DB::beginTransaction();
        try {
            foreach($services as $service) {
                $service->approval_status = 'suspended';
                $service->is_active = false;
                $service->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Suspended services ' . implode(',', $this->checkedServices);
            $log->save();

            $service = $service->load(['merchant.owner']);
            $notif = new Notification;
            $notif->recipient_id = $service->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $service->id;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
            $notif->message = "Your service, {$service->name}, has been suspended.";
            $notif->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowServices.multipleDeactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }
        
        session()->flash('success', 'Services have been deactivated.');
        $this->reset(['checkedServices', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function change_status()
    {
        try {
            $this->validate([
                'service_id' => 'required|exists:services,id',
                'actionType' => 'required|in:approve,deny,reactivate,deactivate',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->confirmationModalVisible = false;
            return;
        }
        
        DB::beginTransaction();
        try {
            $service = Service::where('merchant_id', $this->merchant->id)->find($this->service_id);

            $service = $service->load(['merchant.owner']);
            $notif = new Notification;
            $notif->recipient_id = $service->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $service->id;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
            
            $log = new AdminLog;
            $log->user_id = auth()->id();

            switch ($this->actionType) {
                case 'approve':
                    $service->approval_status = 'approved';
                    $log->title = 'Approved service ' . $service->id;
                    $notif->message = "Your service, {$service->name}, has been approved.";
                    break;
                case 'deny':
                    $service->approval_status = 'rejected';
                    $log->title = 'Rejected service ' . $service->id;
                    $notif->message = "Your service, {$service->name}, has been rejected.";
                    break;
                case 'reactivate':
                    $service->approval_status = 'approved';
                    $log->title = 'Reactivated service ' . $service->id;
                    $notif->message = "Your service, {$service->name}, has been reactivated.";
                    break;
                case 'deactivate':
                    $service->approval_status = 'suspended';
                    $log->title = 'Deactivated service ' . $service->id;
                    $notif->message = "Your service, {$service->name}, has been suspended.";
                    break;
            }
            $service->is_active = false;
            $service->save();
            $notif->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageMerchantsShowServices.change_status: ' . $th->getMessage());
            $this->confirmationModalVisible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->confirmationModalVisible = false;
        return session()->flash('success', 'Service has been ' . $service->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $services = Service::where('merchant_id', $this->merchant->id);

        if ($this->searchTerm) {
            $services = $services->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        $services = $services->orderBy('created_at', $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($services);

        return view('admin.manage-merchants.show.services.admin-manage-merchants-show-services')->with([
            'services' => $services,
            'elements' => $elements
        ]);
    }
}
