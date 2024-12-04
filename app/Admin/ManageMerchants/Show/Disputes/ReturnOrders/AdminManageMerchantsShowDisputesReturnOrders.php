<?php

namespace App\Admin\ManageMerchants\Show\Disputes\ReturnOrders;

use App\Models\Merchant;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowDisputesReturnOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;

    public Merchant $merchant;
    public $searchTerm = '';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function return_orders_disputes_count()
    {
        return $this->merchant->return_orders_through_products()->count();
    }

    #[Computed]
    public function transaction_disputes_count()
    {
        return $this->merchant->transaction_disputes()->count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $return_orders = $this->merchant->return_orders_through_products()->with([
            'reason',
            'status.parent_status',
            'product_order.buyer' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile'],
                ]);
            },
            'product_order.product.first_image',
            'product_order.product.merchant',
        ]);

        if ($this->searchTerm) {
            $return_orders = $return_orders->where(function ($query) {
                $query->whereHas('product_order', function ($product_order) {
                    $product_order->whereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
                });
                $query->orWhere('return_orders.id', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $return_orders = $return_orders->orderBy('created_at', 'desc')->paginate(10);
        $elements = $this->getPaginationElements($return_orders);

        return view('admin.manage-merchants.show.disputes.return-orders.admin-manage-merchants-show-disputes-return-orders')->with([
            'return_orders' => $return_orders,
            'elements' => $elements
        ]);
    }
}
