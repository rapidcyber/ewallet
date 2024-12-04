<?php

namespace App\Merchant\SellerCenter\Services;

use App\Models\BookingStatus;
use App\Models\Merchant;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterServices extends Component
{
    use WithCustomPaginationLinks, WithImageUploading, WithPagination, WithImage;
    
    public Merchant $merchant;

    public $searchTerm = '';

    public $main_category = '';

    public $sub_category = '';

    public $searchDay = '';

    public $approval_status = '';

    #[Locked]
    public $sortBy = '';
    #[Locked]
    public $sortDirection = 'desc';

    public $actions_id = ['copy' => '', 'delete' => ''];

    #[Locked]
    public $can_create;

    #[Locked]
    public $can_edit;

    #[Locked]
    public $can_delete;

    protected $allowedSearchDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    protected $allowedApprovalStatus = ['review', 'approved', 'rejected', 'suspended'];
    protected $allowedSort = ['bookings_count', 'inquiries_count'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        if (Gate::allows('merchant-services', [$this->merchant, 'create'])) {
            $this->can_create = true;
        }

        if (Gate::allows('merchant-services', [$this->merchant, 'update'])) {
            $this->can_edit = true;
        }

        if (Gate::allows('merchant-services', [$this->merchant, 'delete'])) {
            $this->can_delete = true;
        }
    }

    #[Computed(persist: true)]
    public function categories()
    {
        return ServiceCategory::with('parent_category')->whereHas('services', function ($query) {
            $query->where('merchant_id', $this->merchant->id);
        })->get();
    }

    #[Computed(persist: true)]
    public function main_categories()
    {
        return ServiceCategory::with('sub_categories')->whereHas('sub_categories', function ($query) {
            $query->whereHas('services', function ($query) {
                $query->where('merchant_id', $this->merchant->id); 
            });
        })->get();
    }

    #[Computed]
    public function sub_categories()
    {
        if (! empty($this->main_category)) {
            return ServiceCategory::whereHas('services', function ($query) {
                    $query->where('merchant_id', $this->merchant->id); 
                })
                ->whereHas('parent_category', function ($query) {
                    $query->where('slug', $this->main_category);
                })
                ->get();
        }
        return null;
    }

    public function sortTable($column) {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            if (! in_array($column, $this->allowedSort)) {
                $column = 'bookings_count';
            }
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
    }

    public function update_active_status(Service $service)
    {
        if ($service->merchant_id != $this->merchant->id) {
            session()->flash('error', __('Service not found!'));
            return;
        }

        if ($service->approval_status === 'approved') {
            $service->is_active = ! $service->is_active;
            $service->save();
        }
    }

    public function set_copy_id(?int $id)
    {
        $this->actions_id['delete'] = null;
        $this->actions_id['copy'] = $id;
    }

    public function set_delete_id(?int $id)
    {
        $this->actions_id['copy'] = null;
        $this->actions_id['delete'] = $id;
    }

    public function commit_copy()
    {
        $copyNumber = 1;

        $service = $this->merchant->services()->where('id', $this->actions_id['copy'])->first();

        $this->actions_id['copy'] = null;

        if (empty($service)) {
            session()->flash('error', __('Service not found!'));
            return;
        }

        $existingCount = Service::where('name', 'like', $service->name.' (%)%')
            ->orWhere('name', $service->name)
            ->count();

        if ($existingCount > 1) {
            $copyNumber++;
        }

        DB::beginTransaction();
        try {
            $newService = $service->replicate();
            $newService->name = $service->name.' ('.$copyNumber.')';
            $newService->is_active = 0;
            $newService->approval_status = 'review';
            $newService->save();
    
            $collection = 'service_images';
            $service_images = $service->getMedia($collection);
    
            foreach ($service_images as $image) {
                $this->copy_file_media($newService, $image, $collection);
            }
            $newLocation = $service->location->replicate();
            $newLocation->entity_id = $newService->id;
            $newLocation->save();
            
            DB::commit();

            return session()->flash('success', 'Service copied successfully.');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterServices.commit_copy: '.$ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
    }

    public function commit_delete()
    {
        $service = $this->merchant->services()->where('id', $this->actions_id['delete'])->first();

        $this->actions_id['delete'] = null;
        if (empty($service)) {
            session()->flash('error', __('Service not found!'));
            return;
        }

        DB::beginTransaction();
        try {
            $service->location->delete();
            $service->delete();

            DB::commit();
            return session()->flash('success', 'Service deleted successfully.');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantSellerCenterServices.commit_delete: '.$ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
    }

    public function arrange_service_days($service_days)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $service_days = array_intersect($days, $service_days);

        return $service_days;
    }

    private function clean_user_input()
    {
        if (! empty($this->searchDay) && ! in_array($this->searchDay, $this->allowedSearchDays)) {
            $this->searchDay = '';
        }

        if (! empty($this->approval_status) && ! in_array($this->approval_status, $this->allowedApprovalStatus)) {
            $this->approval_status = '';
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $this->clean_user_input();

        $service_q = $this->merchant->owned_services()->with(['inquiries', 'location', 'first_image'])
            ->withCount(['inquiries', 'bookings' => function ($query) {
                $query->whereIn('booking_status_id', ['2', '3']);    
            }]);

        if (! empty($this->searchTerm)) {
            $service_q = $service_q->where('name', 'like', "%$this->searchTerm%");
        }

        if (! empty($this->main_category)) {
            $service_q = $service_q->whereHas('category', function ($query) {
                if (! empty($this->sub_category)) {
                    $query->where('slug', $this->sub_category);
                }
                $query->whereHas('parent_category', function ($query) {
                    $query->where('slug', $this->main_category); 
                });
            });
        }

        if (! empty($this->searchDay)) {
            $service_q = $service_q->whereRaw('LOWER(service_days) like ?', ['%'.strtolower($this->searchDay).'%']);
        }

        if (! empty($this->approval_status)) {
            $service_q = $service_q->where('approval_status', $this->approval_status);
        }

        if ($this->sortBy && !in_array($this->sortBy, $this->allowedSort)) {
            $this->sortBy = 'bookings_count';
        }

        if (! empty($this->sortBy)) {
            $service_q = $service_q->orderBy($this->sortBy, $this->sortDirection);
        }

        $services = $service_q
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $elements = $this->getPaginationElements($services);

        return view('merchant.seller-center.services.merchant-seller-center-services-list', ['services' => $services, 'elements' => $elements]);
    }
}
