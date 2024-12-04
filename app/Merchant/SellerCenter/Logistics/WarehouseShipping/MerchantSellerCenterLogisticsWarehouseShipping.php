<?php

namespace App\Merchant\SellerCenter\Logistics\WarehouseShipping;

use App\Models\Merchant;
use App\Models\ShippingOption;
use App\Models\Warehouse;
use App\Traits\WithCustomPaginationLinks;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterLogisticsWarehouseShipping extends Component
{
    use WithCustomPaginationLinks, WithPagination;

    public Merchant $merchant;
    public $isAddWarehouseModalVisible = false;
    public $isPackageQuantityModalOpen = false;
    #[Locked]
    public $warehouse_id = null;

    protected $listeners = [
        'refreshModal' => '$refresh',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::where('merchant_id', $this->merchant->id)
            ->with('location')
            ->withSum(['products as package_quantity' => function ($query) {
                $query->where('merchant_id', $this->merchant->id);
            }], 'product_warehouse.stocks')
            ->get();
    }

    public function openPackageQuantityModal($warehouse_id)
    {
        if (!in_array($warehouse_id, $this->warehouses->pluck('id')->toArray())) {
            session()->flash('error', 'Something went wrong!');
            session()->flash('error_message', 'Invalid warehouse.');
            return;
        }

        $this->warehouse_id = $warehouse_id;
        $this->isPackageQuantityModalOpen = true;
    }

    #[On('addWarehouseSuccess')]
    public function addWarehouseSuccess($message)
    {
        session()->flash('success', $message);
        $this->isAddWarehouseModalVisible = false;
        $this->warehouse_id = null;
    }

    #[On('addWarehouseFailed')]
    public function addWarehouseFailed($message)
    {
        session()->flash('error', $message);
        $this->isAddWarehouseModalVisible = false;
        $this->warehouse_id = null;
    }

    #[On('closeWarehouseModal')]
    public function closeWarehouseModal()
    {
        $this->isAddWarehouseModalVisible = false;
        $this->warehouse_id = null;
    }

    public function updatedIsAddWarehouseModalVisible()
    {
        if (!$this->isAddWarehouseModalVisible) {
            $this->warehouse_id = null;
        }
    }
    
    public function openEditWarehouseModal($warehouse_id)
    {
        $this->warehouse_id = $warehouse_id;
        $this->isAddWarehouseModalVisible = true;
    }

    #[Computed]
    public function shipping_options()
    {
        return ShippingOption::all();
    }

    #[Computed]
    public function merchant_shipping_options()
    {
        return $this->merchant->shipping_options()->pluck('shipping_options.id')->toArray();
    }

    public function update_merchant_shipping($shipping_option_slug, $checked)
    {
        if (!in_array($shipping_option_slug, $this->shipping_options->pluck('slug')->toArray())) {
            session()->flash('error', 'Something went wrong!');
            session()->flash('error_message', 'Invalid shipping option.');
            return;
        }

        if ($checked) {
            $this->merchant->shipping_options()->syncWithoutDetaching($this->shipping_options->where('slug', $shipping_option_slug)->first()->id);
        } else {
            $this->merchant->shipping_options()->detach($this->shipping_options->where('slug', $shipping_option_slug)->first()->id);
        }
    }


    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.logistics.warehouse-shipping.merchant-seller-center-logistics-warehouse-shipping');
    }
}
