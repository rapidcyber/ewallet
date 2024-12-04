<?php

namespace App\Admin\ManageServices;

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

class AdminManageServices extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public $searchTerm = '';
    #[Locked]
    public $sortDirection = 'desc';
    public $selectedBox = 'all';

    public $confirmationModalVisible = false;
    public $actionType = '';
    public $service_id;

    public $groupConfirmationModalVisible = false;
    public $groupActionType = '';
    public $selectAll = false;
    public $checkedServices = [];

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


    public function multipleActivate()
    {
        $this->validate([
            'checkedServices' => 'required|array|min:1|max:10',
            'checkedServices.*' => 'required|exists:services,id',
        ]);

        $services = Service::whereIn('id', $this->checkedServices)->get();

        DB::beginTransaction();
        try {
            foreach ($services as $service) {
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
            Log::error('AdminManageServices.multipleActivate: ' . $th->getMessage());
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

        $services = Service::whereIn('id', $this->checkedServices)->get();

        DB::beginTransaction();
        try {
            foreach ($services as $service) {
                $service->approval_status = 'suspended';
                $service->is_active = false;
                $service->save();

                $service = $service->load(['merchant.owner']);
                $notif = new Notification;
                $notif->recipient_id = $service->merchant->id;
                $notif->recipient_type = Merchant::class;
                $notif->ref_id = $service->id;
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = "Your service, {$service->name}, has been suspended.";
                $notif->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Deactivated services ' . implode(',', $this->checkedServices);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageServices.multipleDeactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Services have been deactivated.');
        $this->reset(['checkedServices', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
    }

    public function updatedSelectedBox()
    {
        $this->resetPage();

        if (!in_array($this->selectedBox, ['all', 'review', 'active', 'rejected', 'suspended', 'unpublished'])) {
            $this->selectedBox = 'all';
        }

        $this->reset(['sortDirection']);
        $this->checkedServices = [];
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->sortDirection = 'desc';
        $this->checkedServices = [];
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
            $service = Service::find($this->service_id);

            $log = new AdminLog;
            $log->user_id = auth()->id();

            $service = $service->load(['merchant.owner']);
            $notif = new Notification;
            $notif->recipient_id = $service->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $service->id;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;

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
                    $notif->message = "Your service, {$service->name}, has been deactivated.";
                    break;
            }
            $service->is_active = false;
            $service->save();
            $notif->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageServices.change_status: ' . $th->getMessage());
            $this->confirmationModalVisible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->confirmationModalVisible = false;
        return session()->flash('success', 'Service has been ' . $service->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $services = Service::query();

        $totalProductCount = $services->clone()->count();
        $reviewCount = $services->clone()->where('approval_status', 'review')->count();
        $activeCount = $services->clone()->where('approval_status', 'approved')->where('is_active', true)->count();
        $rejectedCount = $services->clone()->where('approval_status', 'rejected')->count();
        $suspendedCount = $services->clone()->where('approval_status', 'suspended')->count();
        $unpublishedCount = $services->clone()->where('approval_status', 'approved')->where('is_active', false)->count();

        $services = match ($this->selectedBox) {
            'all' => $services,
            'review' => $services->where('approval_status', 'review'),
            'active' => $services->where('approval_status', 'approved')->where('is_active', true),
            'rejected' => $services->where('approval_status', 'rejected'),
            'suspended' => $services->where('approval_status', 'suspended'),
            'unpublished' => $services->where('approval_status', 'approved')->where('is_active', false),
        };

        if ($this->searchTerm) {
            $services = $services->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhereHas('merchant', function ($query) {
                        $query->where('name', 'like', '%' . $this->searchTerm . '%');
                        $query->orWhere('email', 'like', '%' . $this->searchTerm . '%');
                    });
            });
        }

        $services = $services->with('merchant:id,name')->orderBy('created_at', $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($services);

        return view('admin.manage-services.admin-manage-services')->with([
            'services' => $services,
            'elements' => $elements,
            'totalProductCount' => $totalProductCount,
            'reviewCount' => $reviewCount,
            'activeCount' => $activeCount,
            'rejectedCount' => $rejectedCount,
            'suspendedCount' => $suspendedCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }
}
