<?php

namespace App\Admin\ManageServices;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminManageServicesShow extends Component
{
    use WithImage, WithValidPhoneNumber;

    public Service $service;
    public $visible = false;
    public $actionType = '';

    public function mount(Service $service)
    {
        $this->service = $service->load(['merchant.owner']);
    }

    #[Computed]
    public function contact_number()
    {
        $validated_phone = $this->phonenumber_info($this->service->merchant->phone_number, $this->service->merchant->phone_iso);

        if ($validated_phone == false) {
            return $this->service->merchant->phone_number;
        }

        return '(+' . $validated_phone->getCountryCode() . ') ' . $validated_phone->getNationalNumber();
    }

    public function change_status()
    {
        try {
            $this->validate([
                'actionType' => 'required|in:approve,deny,reactivate,suspend',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->visible = false;
            return;
        }
        
        DB::beginTransaction();
        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();

            $service = $this->service->load(['merchant.owner']);
            $notif = new Notification;
            $notif->recipient_id = $service->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $service->id;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;

            switch ($this->actionType) {
                case 'approve':
                    $this->service->approval_status = 'approved';
                    $log->title = 'Approved service ' . $this->service->id;
                    $notif->message = "Your service, {$service->name}, has been approved.";
                    break;
                case 'deny':
                    $this->service->approval_status = 'rejected';
                    $log->title = 'Rejected service ' . $this->service->id;
                    $notif->message = "Your service, {$service->name}, has been denied.";
                    break;
                case 'reactivate':
                    $this->service->approval_status = 'approved';
                    $log->title = 'Reactivated service ' . $this->service->id;
                    $notif->message = "Your service, {$service->name}, has been reactivated.";
                    break;
                case 'suspend':
                    $this->service->approval_status = 'suspended';
                    $log->title = 'Suspended service ' . $this->service->id;
                    $notif->message = "Your service, {$service->name}, has been suspended.";
                    break;
            }
            $this->service->is_active = false;
            $this->service->save();
            $notif->save();

            $log->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageServicesShow.change_status: ' . $th->getMessage());
            $this->visible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->visible = false;
        return session()->flash('success', 'Service has been ' . $this->service->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $this->service = $this->service->load(['merchant.category', 'category.parent_category', 'media' => function ($query) {
            $query->where('collection_name', 'service_images');
        }, 'previous_works.media', 'location', 'form_questions.choices']);

        $dayOrder = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

        $service_days = $this->service->service_days;

        // Sort the timeslots array by the correct day order
        uksort($service_days, function($a, $b) use ($dayOrder) {
            return array_search($a, $dayOrder) - array_search($b, $dayOrder);
        });

        return view('admin.manage-services.admin-manage-services-show')->with([
            'service_days' => $service_days
        ]);
    }
}
