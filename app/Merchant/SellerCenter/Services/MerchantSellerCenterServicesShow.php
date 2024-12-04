<?php

namespace App\Merchant\SellerCenter\Services;

use App\Models\Merchant;
use App\Models\Service;
use App\Traits\WithImage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MerchantSellerCenterServicesShow extends Component
{
    use WithImage;

    public Merchant $merchant;
    public Service $service;

    public function mount(Merchant $merchant, Service $service)
    {
        $this->merchant = $merchant;
        $this->service = $service->load(['category.parent_category', 'media' => function ($query) {
            $query->where('collection_name', 'service_images');
        }, 'previous_works.media', 'location', 'form_questions.choices']);
    }

    public function delete()
    {
        if ($this->service->merchant_id == $this->merchant->id) {
            $this->service->delete();

            session()->flash('success', 'Service deleted successfully.');
            return $this->redirect(route('merchant.seller-center.services.index', ['merchant' => $this->merchant]));
        }
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $dayOrder = ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"];

        $service_days = $this->service->service_days;

        // Sort the timeslots array by the correct day order
        uksort($service_days, function($a, $b) use ($dayOrder) {
            return array_search($a, $dayOrder) - array_search($b, $dayOrder);
        });

        return view('merchant.seller-center.services.merchant-seller-center-services-show')->with([
            'service_days' => $service_days
        ]);
    }
}
