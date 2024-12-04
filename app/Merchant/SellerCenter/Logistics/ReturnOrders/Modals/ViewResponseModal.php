<?php

namespace App\Merchant\SellerCenter\Logistics\ReturnOrders\Modals;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Component;

class ViewResponseModal extends Component
{
    use WithImage;

    public Merchant $merchant;
    public ReturnOrder $return_order;

    public function mount(Merchant $merchant, $return_order_id)
    {
        $this->merchant = $merchant;
        $status_allowed = ReturnOrderStatus::where(function ($query) {
            $query->where('slug', 'pending_resolution');
            $query->whereHas('parent_status', function ($q) {
                $q->where('slug', 'dispute_in_progress');
            });
        })->firstOrFail();

        $this->return_order = $merchant->return_orders_through_products()
            ->where('return_orders.id', $return_order_id)
            ->where('return_order_status_id', $status_allowed->id)
            ->whereHas('dispute', function ($q) {
                $q->whereHas('response');
            })
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
                'dispute.response.media'
            ])
            ->firstOrFail();
    }
    
    public function render()
    {
        return view('merchant.seller-center.logistics.return-orders.modals.view-response-modal');
    }
}
