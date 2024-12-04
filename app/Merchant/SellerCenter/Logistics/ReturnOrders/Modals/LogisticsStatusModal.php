<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use Livewire\Attributes\Locked;
use Livewire\Component;

class LogisticsStatusModal extends Component
{
    public Merchant $merchant;
    public ReturnOrder $return_order;

    #[Locked]
    public $delivery_status = 0;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->with([
                'product_order.logs',
                'product_order.shipping_status',
                'product_order.shipping_option'
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

    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.logistics-status-modal');
    }
}
