<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\User;
use App\Traits\WithImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class MerchantSellerCenterLogisticsReturnOrdersShow extends Component
{
    use WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $delivery_status = 0;

    public $return_order_id = null;

    public $showRefundModal = false;
    public $showReturnRefundModal = false;
    public $showRejectRequestModal = false;
    public $showRejectRequestAfterReturnModal = false;
    public $showRespondModal = false;
    public $showViewResponseModal = false;
    public $showViewResolutionModal = false;
    public $showLogisticsStatusModal = false;
    public $showProcessRefundModal = false;

    public function mount(Merchant $merchant, ReturnOrder $returnOrder)
    {
        $this->merchant = $merchant;
        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $returnOrder->id)
            ->with([
                'logs',
                'dispute.media',
                'reason',
                'media',
                'status.parent_status',
                'product_order.product.first_image',
                'product_order.product.merchant',
                'product_order.logs',
                'product_order.shipping_option',
                'product_order.payment_option',
                'product_order.shipping_status',
                'product_order.warehouse',
                'product_order.location',
                'product_order.buyer' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile'],
                    ]);
                },
            ])
            ->firstOrFail();

        switch ($this->return_order->product_order->shipping_status->name) {
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

    public function calculate_remaining_hours($created_at)
    {
        $date = Carbon::parse($created_at);
        $target_time = $date->copy()->addHours(96.5);

        if ($target_time->lt(Carbon::now())) {
            return 'Expired';
        }

        return $target_time->diffForHumans(null, true) . ' left';
    }

    public function open_modal($return_order_id, $modal_name)
    {
        if (!in_array($modal_name, ['refund', 'return_refund', 'reject_request', 'reject_request_after_return', 'respond', 'view_response', 'view_resolution', 'logistics_status', 'process_refund'])) {
            return session()->flash('error', 'Invalid action');
        }

        switch ($modal_name) {
            case 'refund':
                $this->showRefundModal = true;
                break;
            case 'return_refund':
                $this->showReturnRefundModal = true;
                break;
            case 'reject_request':
                $this->showRejectRequestModal = true;
                break;
            case 'reject_request_after_return':
                $this->showRejectRequestAfterReturnModal = true;
                break;
            case 'respond':
                $this->showRespondModal = true;
                break;
            case 'view_response':
                $this->showViewResponseModal = true;
                break;
            case 'view_resolution':
                $this->showViewResolutionModal = true;
                break;
            case 'logistics_status':
                $this->showLogisticsStatusModal = true;
                break;
            case 'process_refund':
                $this->showProcessRefundModal = true;
                break;
        }

        $return_order = $this->merchant->return_orders_through_products()->where('return_orders.id', $return_order_id)->first();

        if (!$return_order) {
            $this->reset([
                'return_order_id',
                'showRefundModal',
                'showReturnRefundModal',
                'showRejectRequestModal',
                'showRejectRequestAfterReturnModal',
                'showRespondModal',
                'showViewResponseModal',
                'showViewResolutionModal',
                'showLogisticsStatusModal',
                'showProcessRefundModal'
            ]);
            return session()->flash('error', 'Return Order Request not found');
        }

        $this->return_order_id = $return_order_id;
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->reset([
            'return_order_id',
            'showRefundModal',
            'showReturnRefundModal',
            'showRejectRequestModal',
            'showRejectRequestAfterReturnModal',
            'showRespondModal',
            'showViewResponseModal',
            'showViewResolutionModal',
            'showLogisticsStatusModal',
            'showProcessRefundModal'
        ]);
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

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.merchant-seller-center-logistics-return-orders-show');
    }
}
