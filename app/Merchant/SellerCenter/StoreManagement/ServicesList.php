<?php

namespace App\Merchant\SellerCenter\StoreManagement;

use App\Models\Merchant;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Livewire\Component;
use Livewire\WithPagination;

class ServicesList extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithImage;

    public Merchant $merchant;

    public Service $service;

    public $categories;

    public $main_categories;

    public $sub_categories;

    public $searchTerm = '';

    public $category = '';

    public $main_category = '';

    public $sub_category = '';

    public $service_day = '';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->categories = ServiceCategory::with('parent_category')->whereHas('services', function ($query) {
            $query->where('merchant_id', $this->merchant->id);
        })->get();

        $this->main_categories = $this->categories->map(function ($category) {
            return $category->parent_category;
        })->unique();
    }

    public function clear_service_filter()
    {
        $this->searchTerm = '';
        $this->clear_main_category();
        $this->clear_sub_category();
        $this->clear_service_day();
    }

    public function clear_sub_category()
    {
        $this->sub_category = '';
    }

    public function clear_main_category()
    {
        $this->main_category = '';
        $this->sub_category = '';
        $this->sub_categories = null;
    }

    public function clear_service_day()
    {
        $this->service_day = '';
    }

    public function getServiceSubCategories()
    {
        if (! empty($this->main_category)) {
            $this->sub_category = '';
            $this->sub_categories = $this->categories->where('parent', $this->main_category);
        } else {
            $this->sub_category = '';
            $this->sub_categories = null;
        }
    }

    public function service_feature_change(Service $service)
    {
        $service->is_featured = empty($service->is_featured);
        $service->save();

        $this->dispatch('featuredServicesUpdated');

    }

    public function arrange_service_days($service_days)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $service_days = array_intersect($days, $service_days);

        return $service_days;
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'searchTerm') {
            $this->resetPage();
        } elseif ($propertyName === 'service_day') {
            $this->resetPage();
        } elseif ($propertyName === 'main_category') {
            $this->resetPage();
        } elseif ($propertyName === 'sub_category') {
            $this->resetPage();
        }
    }

    public function render()
    {
        $services = $this->merchant->owned_services()->with('first_image', 'location');
        if (! empty($this->searchTerm)) {
            $services = $services->where('name', 'like', '%'.$this->searchTerm.'%');
        }

        if (! empty($this->service_day)) {
            $services = $services->whereRaw('LOWER(service_days) like ?', ['%'.strtolower($this->service_day).'%']);
        }

        if (! empty($this->main_category)) {
            $sub_categories = ServiceCategory::where('parent', $this->main_category)->pluck('id')->toArray();
            $services = $services->whereIn('service_category_id', $sub_categories);
        }

        if (! empty($this->sub_category)) {
            $services = $services->where('service_category_id', $this->sub_category);
        }
        if (! empty($this->service_day)) {
            $services = $services->where('service_days', 'like', '%'.$this->service_day.'%');
        }

        $services = $services->orderBy('created_at')->paginate(5);

        $services_elements = $this->getPaginationElements($services);
        $this->resetPage();

        return view('merchant.seller-center.store-management.services-list', ['services_elements' => $services_elements, 'services' => $services]);
    }
}
