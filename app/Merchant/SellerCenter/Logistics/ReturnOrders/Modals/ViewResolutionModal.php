<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Component;

class ViewResolutionModal extends Component
{
    use WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::whereHas('parent_status', function ($q) {
            $q->where('slug', 'resolved');
        })->pluck('id')->toArray();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->whereIn('return_order_status_id', $status_allowed)
            ->with([
                'product_order.buyer' => function (MorphTo $q) {
                    $q->morphWith([
                        User::class => ['profile', 'media' => function ($q) {
                            $q->where('collection_name', 'profile_picture');
                        }],
                        Merchant::class => ['media' => function ($q) {
                            $q->where('collection_name', 'merchant_logo');
                        }]
                    ]);
                },
                'product_order.product.first_image',
                'product_order.payment_option',
                'status.parent_status',
                'reason',
                'logs',
                'dispute.media',
                'dispute.response.media',
                'dispute.decision.media',
                'cancellation.media',
                'cancellation.reason',
            ])
            ->firstOrFail();
    }

    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.view-resolution-modal');
    }
}
