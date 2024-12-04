<?php

namespace App\Merchant\SellerCenter\Logistics\Orders;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderDocument;
use App\Models\ProductOrderLog;
use App\Models\ShippingStatus;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class MerchantSellerCenterLogisticsOrdersShow extends Component
{
    use WithImage, WithValidPhoneNumber, WithNotification;

    public Merchant $merchant;
    public ProductOrder $productOrder;
    public $show_modal = false;

    #[Locked]
    public $delivery_status = 0;

    public $show_cancel_modal = false;

    public function mount(Merchant $merchant, ProductOrder $productOrder)
    {
        $this->merchant = $merchant;
        $this->productOrder = $merchant->orders_through_products()
            ->where('product_orders.id', $productOrder->id)
            ->with([
                'product.first_image',
                'shipping_status',
                'documents',
                'location',
                'shipping_option',
                'payment_option',
                'cancellation.reason',
                'cancellation.media',
                'buyer' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }]
                    ]);
                }
            ])
            ->findOrFail($productOrder->id);

        switch ($this->productOrder->shipping_status->name) {
            case 'Unpaid':
            case 'Pending':
            case 'Packed':
            case 'Cancellation':
            case 'Failed Delivery':
                $this->delivery_status = 0;
                break;
            case 'Ready to Ship':
                $this->delivery_status = 1;
                break;
            case 'Shipping':
                $this->delivery_status = 2;
                break;
            case 'Completed':
                $this->delivery_status = 3;
                break;
        }
    }

    #[On('successModal')]
    public function successModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('success', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }
    }

    #[On('failedModal')]
    public function failedModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('error', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('error_message', $message['message']);
        }
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->show_cancel_modal = false;
    }

    #[Computed]
    public function shipping_statuses()
    {
        return ShippingStatus::all();
    }

    public function download_documents($document)
    {
        if (!in_array($document, ['awb', 'pick-list', 'packing-list', 'all'])) {
            return session()->flash('error', 'Invalid document');
        }

        if ($this->productOrder->documents) {
            $documents_list = $this->productOrder->documents;
        } else {
            $documents_list = new ProductOrderDocument;
            $documents_list->product_order_id = $this->productOrder->id;
        }

        switch ($document) {
            case 'awb':
                session()->flash('warning', 'AWB document file not available yet.');
                $documents_list->awb_downloaded = true;
                break;
            case 'pick-list':
                session()->flash('warning', 'Pick List document file not available yet.');
                $documents_list->pick_list_downloaded = true;
                break;
            case 'packing-list':
                session()->flash('warning', 'Packing List document file not available yet.');
                $documents_list->packing_list_downloaded = true;
                break;
            case 'all':
                session()->flash('warning', 'All document files not available yet.');
                $documents_list->awb_downloaded = true;
                $documents_list->pick_list_downloaded = true;
                $documents_list->packing_list_downloaded = true;
                break;
        }

        $documents_list->save();
    }

    public function pack_and_print()
    {
        $status_pending = $this->shipping_statuses->where('slug', 'pending')->first()->id;

        if ($this->productOrder->shipping_status_id != $status_pending) {
            return session()->flash('error', 'Invalid product order');
        }

        DB::beginTransaction();
        try {
            $this->productOrder->shipping_status_id = $this->shipping_statuses->where('slug', 'packed')->first()->id;
            $this->productOrder->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $this->productOrder->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'packed')->first()->id;
            $log->title = 'Packed by Seller';
            $log->description = 'Product order has been marked as packed by seller';
            $log->save();

            $this->alert(
                $this->productOrder->buyer, 
                'notification', 
                $this->productOrder->order_number,
                "#{$this->productOrder->order_number} - Your product order has been marked as packed by the seller.",
                
            );

            
            session()->flash('success', 'Marked as Packed');
            session()->flash('success_message', 'Product order has been marked as packed successfully');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrdersShow.pack_and_print: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }

        $this->reset(['show_modal']);
    }

    public function arrange_shipment()
    {
        $status_packed = $this->shipping_statuses->where('slug', 'packed')->first()->id;

        if ($this->productOrder->shipping_status_id != $status_packed) {
            return session()->flash('error', 'Invalid product order');
        }

        DB::beginTransaction();
        try {
            $this->productOrder->shipping_status_id = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;
            $this->productOrder->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $this->productOrder->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;
            $log->title = 'Shipment to be arranged';
            $log->description = 'Seller has arranged shipment for the product';
            $log->save();

            $this->alert(
                $this->productOrder->buyer, 
                'notification', 
                $this->productOrder->order_number,
                "#{$this->productOrder->order_number} - Your product order has been marked as ready to ship.",
                
            );
            
            session()->flash('success', 'Marked as Ready to Ship');
            session()->flash('success_message', 'Product order has been marked as ready to ship successfully');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrders.arrange_shipment: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }

        $this->reset(['show_modal']);
    }

    public function recreate_package()
    {
        $status_packed = $this->shipping_statuses->where('slug', 'ready_to_ship')->first()->id;

        if ($this->productOrder->shipping_status_id != $status_packed) {
            return session()->flash('error', 'Invalid product order');
        }

        DB::beginTransaction();
        try {
            $this->productOrder->shipping_status_id = $this->shipping_statuses->where('slug', 'pending')->first()->id;
            $this->productOrder->save();

            $log = new ProductOrderLog;
            $log->product_order_id = $this->productOrder->id;
            $log->shipping_status_id = $this->shipping_statuses->where('slug', 'pending')->first()->id;
            $log->title = 'Product order brought back to pending';
            $log->description = 'Seller has recreated package for the product';
            $log->save();
            
            session()->flash('success', 'Package Recreated');
            session()->flash('success_message', 'Product order has been marked as pending successfully');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterLogisticsOrders.recreate_package: ' . $th->getMessage());
            session()->flash('error', 'An error has occurred. Please try again later.');
        }

        $this->reset(['show_modal']);
    }

    public function open_cancel_order_modal()
    {
        $accepted_statuses = $this->shipping_statuses->whereIn('slug', ['unpaid', 'pending', 'packed', 'ready_to_ship'])->pluck('id')->toArray();

        if (!in_array($this->productOrder->shipping_status_id, $accepted_statuses)) {
            return session()->flash('error', 'Product order cannot be cancelled');
        }

        $this->show_cancel_modal = true;
    }

    private function calculate_remaining_hours($created_at)
    {
        $date = Carbon::parse($created_at);
        $target_time = $date->copy()->addHours(96.5);

        if ($target_time->lt(Carbon::now())) {
            return 'Expired';
        }

        return $target_time->diffForHumans(null, true) . ' left';
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $countdown = $this->calculate_remaining_hours($this->productOrder->created_at);

        return view('merchant.seller-center.logistics.orders.merchant-seller-center-logistics-orders-show')->with([
            'countdown' => $countdown
        ]);
    }
}
