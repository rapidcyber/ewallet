<?php

namespace App\Admin\Disputes\ReturnOrders;

use App\Models\ReturnOrder;
use App\Models\TransactionDispute;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminDisputesReturnOrders extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithValidPhoneNumber;

    public $searchTerm = '';

    #[Computed]
    public function return_order_count()
    {
        return ReturnOrder::whereHas('dispute')->count();
    }

    #[Computed]
    public function disputes_count()
    {
        return TransactionDispute::count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $return_orders = ReturnOrder::whereHas('dispute')
            ->with([
                'reason',
                'status.parent_status',
                'product_order.product.first_image',
                'product_order.product.merchant',
                'product_order.buyer' => function (MorphTo $query) {
                    $query->morphWith([
                        User::class => ['profile']
                    ]);
                }
            ]);

        if ($this->searchTerm) {
            $return_orders = $return_orders->where(function ($query) {
                $query->whereHas('product_order', function ($product_order) {
                    $product_order->whereHas('product', function ($product) {
                        $product->where('name', 'like', '%' . $this->searchTerm . '%');
                    });
                });
                $query->orWhere('return_orders.id', 'like', '%' . $this->searchTerm . '%');
                // $query->orWhereHas('product_order', function ($product_order) {
                //     $product_order->where('order_number', 'like', '%' . $this->searchTerm . '%');
                //     $product_order->orWhere('tracking_number', 'like', '%' . $this->searchTerm . '%');
                // });
            });
        }

        $return_orders = $return_orders->orderBy('created_at', 'desc')->paginate(10);
        $elements = $this->getPaginationElements($return_orders);

        return view('admin.disputes.return-orders.admin-disputes-return-orders')->with([
            'return_orders' => $return_orders,
            'elements' => $elements
        ]);
    }
}
